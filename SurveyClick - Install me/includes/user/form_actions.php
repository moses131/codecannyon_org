<?php

namespace user;

class form_actions extends \util\db {

    private $user;
    private $user_obj;

    function __construct( $user ) {
        parent::__construct();

        if( gettype( $user ) == 'object' ) {
            $this->user_obj     = $user;
            $this->user         = $this->user_obj->getId();
        } else if( $user == 0 ) {
            $this->user_obj     = me();
            $this->user         = $this->user_obj->getId();
        } else {
            $this->setUser( $user );
        }
    }

    public function setUser( int $user ) {
        $users = users( $user );
        if( $users->getObject() ) {
            $this->user         = $users->getId();
            $this->user_obj     = $users;
        }
        return $this;
    }

    public function deposit( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data       = filters()->do_filter( 'deposit-form-sanitize-data', $data );
        $methods    = filters()->do_filter( 'deposit-methods', [] );

        if( !filters()->do_filter( 'custom-error-deposit', true, $this->user, $data ) ) 
            return ;
        else if( !isset( $data['amount' ] ) || !isset( $data['option'] ) || !in_array( $data['option'], array_keys( $methods ) ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( (double) $data['amount'] < ( $min = (double) get_option( 'deposit_min' ) ) )
            throw new \Exception( sprintf( t( 'The minimum amount you can deposit is: %s' ), cms_money_format( $min ) ) );

        $class  = call_user_func( $methods[$data['option']]['class'], [
            'returnURL' => filters()->do_filter( 'payments_deposit_returnURL', admin_url( '?executePayment&success=true' ) ),
            'cancelURL' => filters()->do_filter( 'payments_deposit_cancelURL', admin_url( '?executePayment&success=false' ) )
        ] );
        $class  ->setAmount( (double) $data['amount'] );
        $class  ->setParam( 'userId', $this->user );

        if( isset( $data['voucher'] ) )
        $class  ->setParam( 'voucher', (int) $data['voucher'] );

        try {
            return $class->getRedirectURL();
        }

        catch( Exception $e ) {
            throw new \Exception( 'Error' );
        }
    }

    public function upgrade( object $plan, array $data, array $attributes = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !$this->user_obj->limits()->isFree() )
        throw new \Exception( t( 'You are already subscribed to a plan' ) );

        $data       = filters()->do_filter( 'subscribe-form-sanitize-data', $data, $plan );
        $methods    = filters()->do_filter( 'deposit-methods', [] );

        if( !filters()->do_filter( 'custom-error-upgrade', true, $this->user, $plan, $data ) ) 
            return ;
        else if( !isset( $data['months'] ) || !isset( $data['method'] ) || (int) $data['months'] > 24 )
            throw new \Exception( t( 'Something went wrong' ) );

        $months     = (int) $data['months'];
        $totalPrice = $plan->priceMonths( $months ) * $months;
        $planId     = $plan->getId();

        // Subscribe using the wallet
        if( $data['method'] == 'wallet' ) {

            if( $this->user_obj->getBalance() < $totalPrice )
            throw new \Exception( t( "Balance is too low" ) );

            $query  = 'INSERT INTO ';
            $query .= $this->table( 'subscriptions' );
            $query .= ' (user, plan, expiration, rcount, paid) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MONTH), ?, 1) ';
            $query .= ' ON DUPLICATE KEY UPDATE user = VALUES(user), plan = VALUES(plan), expiration = VALUES(expiration), rcount = VALUES(rcount), paid = VALUES(paid)';
        
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iiii', $this->user, $planId, $months, $months );
            $e = $stmt->execute();
    
            if( $e ) {
                actions()->do_action( 'after:upgrade', 'wallet', 'upgrade', $this->user_obj, $months, $plan );

                $query = 'INSERT INTO ';
                $query .= $this->table( 'transactions' );
                $query .= ' (user, type, amount, details, status) VALUES (?, 5, ?, ?, 2)';
    
                $details = cms_json_encode( [ 'Method' => 'wallet' ] );
    
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ids', $this->user, $totalPrice, $details );
                $e  = $stmt->execute();
                $stmt->close();

                // Save credit
                $this->user_obj->manage()->add_credit( -$totalPrice );

                // Add invoice & receipt
                $invoicing  = $this->user_obj->invoicing();
                $invoicing  ->newInvoice( [
                    [ $plan->getName(), $months, $totalPrice ],
                ] )         
                ->setType( 'plan' )
                ->createInvoice( true );

                return [ 'type' => 'reload' ];
            }

            $stmt->close();
    
        // Subscribe using a payment method
        } else if( isset( $data['method'] ) && in_array( $data['method'], array_keys( $methods ) ) ) {

            $query  = 'INSERT INTO ';
            $query .= $this->table( 'subscriptions' );
            $query .= ' (user, plan, addm, rcount, paid, token, info) VALUES (?, ?, ?, ?, 0, ?, ?) ';
            $query .= ' ON DUPLICATE KEY UPDATE user = VALUES(user), plan = VALUES(plan), addm = VALUES(addm), rcount = VALUES(rcount), paid = VALUES(paid), token = VALUES(token), info = VALUES(info), date = NOW()';
        
            $token  = md5( uniqid() );
            $info   = cms_json_encode( [
                'plan'      => $plan->getName(),
                'months'    => $months,
                'total'     => $totalPrice
            ] );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iiiiss', $this->user, $planId, $months, $months, $token, $info );
            $e  = $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();

            $class  = call_user_func( $methods[$data['method']]['class'], [
                'returnURL' => filters()->do_filter( 'payments_deposit_returnURL', admin_url( '?executePayment&success=true' ) ),
                'cancelURL' => filters()->do_filter( 'payments_deposit_cancelURL', admin_url( '?executePayment&success=false' ) )
            ] );
            $class  ->setAmount( $totalPrice );
            $class  ->setParam( 'userId', $this->user );
            $class  ->setParam( 'subscriptionId', $id );
            $class  ->setParam( 'subscriptionToken', $token );

            try {
                return [ 'type' => 'redirect', 'URL' => $class->getRedirectURL() ];
            }
    
            catch( Exception $e ) {
                throw new \Exception( 'Error' );
            }

        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function extend_subscription( object $plan, array $data, array $attributes = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data       = filters()->do_filter( 'extend-subscription-form-sanitize-data', $data, $plan );
        $methods    = filters()->do_filter( 'deposit-methods', [] );

        if( !filters()->do_filter( 'custom-error-extend-subscription', true, $this->user, $plan, $data ) )
            return ;
        else if( !isset( $data['months'] ) || !isset( $data['method'] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $date_time  = new \DateTime;
        $diff_time  = $date_time->diff( new \DateTime( me()->limits()->expiration() ) );
        $max_months = 23 - ( $diff_time->y * 12 + $diff_time->m );
        $months     = (int) $data['months'];

        if( $months > $max_months ) {
            throw new \Exception( t( 'The expiration date of your subscription cannot exceed 24 months' ) );
        }

        $totalPrice = $plan->priceMonths( $months ) * $months;
        $planId     = $plan->getId();

        // Subscribe using the wallet
        if( $data['method'] == 'wallet' ) {

            if( $this->user_obj->getBalance() < $totalPrice )
            throw new \Exception( t( "Your balance is too low" ) );

            $query  = 'UPDATE ';
            $query .= $this->table( 'subscriptions' );
            $query .= ' SET expiration = DATE_ADD(expiration, INTERVAL ? MONTH), rcount = rcount + ?';
            $query .= ' WHERE plan = ? AND user = ?';
        
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iiii', $months, $months, $planId, $this->user );
            $e = $stmt->execute();

            if( $e ) {
                actions()->do_action( 'after:upgrade', 'wallet', 'extend', $this->user_obj, $months, $plan );

                $query = 'INSERT INTO ';
                $query .= $this->table( 'transactions' );
                $query .= ' (user, type, amount, details, status) VALUES (?, 5, ?, ?, 2)';
    
                $details = cms_json_encode( [ 'Method' => 'wallet' ] );
    
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ids', $this->user, $totalPrice, $details );
                $e  = $stmt->execute();
                $stmt->close();

                // Save credit
                $this->user_obj->manage()->add_credit( -$totalPrice );

                // Add invoice & receipt
                $invoicing  = $this->user_obj->invoicing();
                $invoicing  ->newInvoice( [
                    [ $plan->getName(), $months, $totalPrice ],
                ] )         
                            ->setType( 'plan' )
                            ->createInvoice( true );
                            
                return [ 'type' => 'reload' ];
            }

            $stmt->close();
    
        // Subscribe using a payment method
        } else if( isset( $data['method'] ) && in_array( $data['method'], array_keys( $methods ) ) ) {

            $query  = 'UPDATE ';
            $query  .= $this->table( 'subscriptions' );
            $query  .= ' SET addm = ?, token = ?, info = ?';
            $query  .= ' WHERE user = ? AND plan = ?';
        
            $token  = md5( uniqid() );
            $info   = cms_json_encode( [
                'plan'      => $plan->getName(),
                'months'    => $months,
                'total'     => $totalPrice
            ] );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'sssii', $months, $token, $info, $this->user, $planId );
            $e  = $stmt->execute();
            $stmt->close();

            $class  = call_user_func( $methods[$data['method']]['class'], [
                'returnURL' => filters()->do_filter( 'payments_deposit_returnURL', admin_url( '?executePayment&success=true' ) ),
                'cancelURL' => filters()->do_filter( 'payments_deposit_cancelURL', admin_url( '?executePayment&success=false' ) )
            ] );
            $class  ->setAmount( $totalPrice );
            $class  ->setParam( 'userId', $this->user );
            $class  ->setParam( 'extendSubId', $this->user_obj->limits()->getSubscriptionId() );
            $class  ->setParam( 'subscriptionToken', $token );

            try {
                return [ 'type' => 'redirect', 'URL' => $class->getRedirectURL() ];
            }
    
            catch( Exception $e ) {
                throw new \Exception( 'Error' );
            }

        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function withdraw( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'withdraw-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-withdraw', true, $this->user, $data ) )
            return ;
        else if( !isset( $data['option'] ) || !isset( $data['amount'] ) || !in_array( $data['option'], array_keys( filters()->do_filter( 'withdraw-methods', [ 'paypal' => '', 'stripe' => '' ] ) ) ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( $data['option'] == 'paypal' && ( !isset( $data['pp_address'] ) || !filter_var( $data['pp_address'], FILTER_VALIDATE_EMAIL ) ) )
            throw new \Exception( t( 'Your PayPal address is invalid' ) );
        else if( $data['option'] == 'stripe' && ( !isset( $data['stripe_address'] ) || !filter_var( $data['stripe_address'], FILTER_VALIDATE_EMAIL ) ) )
            throw new \Exception( t( 'Your Stripe address is invalid' ) );
        else if( (double) $data['amount'] > me()->getRealBalance() )
            throw new \Exception( t( 'The amount exceeds your balance available for withdraw' ) );
        else if( (double) $data['amount'] < ( $min = (double) get_option( 'withdraw_min', 50 ) ) )
            throw new \Exception( sprintf( t( 'The minimum amount is: %s' ), cms_money_format( $min ) ) );

        if( !isset( $data['details'] ) ) {
            if( $data['option'] == 'paypal' )
            $data['details'] = [ 'Method' => $data['option'], 'PayPal Address' => $data['pp_address'] ];

            if( $data['option'] == 'stripe' )
            $data['details'] = [ 'Method' => $data['option'], 'Stripe Address' => $data['stripe_address'] ];
        }

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'transactions' );
        $query .= ' (user, type, amount, details, status) VALUES (?, 4, ?, ?, 1)';

        $details = cms_json_encode( $data['details'] );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ids', $this->user, $data['amount'], $details );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:withdraw', $this->user, $data['amount'], $details );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function create_order( array $data ) {
        if( shop()->count() == 0 ) {
            throw new \Exception( t( 'Your cart is empty' ) );
        }

        $data       = filters()->do_filter( 'deposit-form-sanitize-data', $data );
        $methods    = filters()->do_filter( 'deposit-methods', [] );

        if( !isset( $data['method' ] ) || !in_array( $data['method'], array_keys( $methods ) ) ) {
            throw new \Exception( t( 'Something went wrong' ) );
        } else if( !filters()->do_filter( 'custom-error-create-order', true, $this->user, $data ) )
            return ;

        $id     = shop()->getCartId();
        $amount = shop()->getTotal();

        try {
            $this->user_obj->actions()->addOrder();
    
            $class  = call_user_func( $methods[$data['method']]['class'], [
                'returnURL' => filters()->do_filter( 'payments_create_order_returnURL', site_url( 'thank_you?executePayment&success=true' ) ),
                'cancelURL' => filters()->do_filter( 'payments_create_order_cancelURL', site_url( 'order_canceled?executePayment&success=false' ) )
            ] );
            $class  ->setAmount( $amount );
            $class  ->setParam( 'orderId', $id );

            try {
                $URL = $class->getRedirectURL();
            }

            catch( Exception $e ) {
                throw new \Exception( 'Error' );
            }

            return $URL;
        }

        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }
    }

    public function add_user_voucher( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'add-voucher-form-sanitize-data', $data );

        if( !isset( $data['code'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-apply-voucher', true, $this->user, $data ) )
            return ;

        $query = 'SELECT add_voucher(?, ?)';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'is', $this->user, $data['code'] );
        $stmt->execute();
        $stmt->bind_result( $added );
        $stmt->fetch();
        $stmt->close();

        if( $added ) {
            actions()->do_action( 'after:apply-voucher', $this->user_obj, $data, $added );
            return true;
        }

        throw new \Exception( t( 'Invalid voucher code' ) );
    }

    public function edit_profile( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-profile-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-profile', true, $this->user, $data ) )
            return ;
        else if( !isset( $data['username' ] ) || !isset( $data['full_name'] ) || !isset( $data['address'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9]{3,50}$/iu', $data['username'] ) )
            throw new \Exception( t( 'Invalid username' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9 ]{3,50}$/iu', $data['full_name'] ) )
            throw new \Exception( t( 'Invalid name' ) );

        $country_id = $this->user_obj->getCountryId();
        $old_country_id = $country_id;
        $timezone   = $this->user_obj->getTz();
        $lcc        = $this->user_obj->getLastCountryChanged();

        if( isset( $data['country'] ) ) {
            $limit  = filters()->do_filter( 'change-country-limit', 30 );
            if( !$lcc || strtotime( '+' . $limit . ' days', strtotime( $lcc ) ) < time() ) {

                $countries  = new \query\countries( $data['country'] );

                if( $countries->getObject() && $country_id != $countries->getIso3166() ) {
                    $country_id = $countries->getIso3166();
                    $timezones  = $countries->getTimezones();
                    $timezone   = key( $timezones );
                    $lcc        = date( 'Y-m-d' );
                }
            }
        }

        // Only inputs that can deal with media
        $avatar = $this->user_obj->getAvatar() ? [ $this->user_obj->getAvatar() => $this->user_obj->getAvatarURL() ] : NULL;
        $fields = filters()->do_filter( 'form:fields:edit-profile', [
            'avatar'    => [ 'type' => 'image', 'category' => 'user-avatar' ]
        ], $this->user_obj );

        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->setValues( filters()->do_filter( 'form:values:edit-profile', [
            'avatar'  => $avatar
        ], $this->user_obj, $data ) );
        $form   ->build();

        $media  = $form->uploadFiles( $data );

        if( count( $media['data[avatar]'] ) )
        $media_avatar = key( $media['data[avatar]'] );

        $birthday   = isset( $data['year'] ) && isset( $data['month'] ) && isset( $data['day'] ) ? implode( '-', [ $data['year'], $data['month'], $data['day'] ] ) : '';
        $gender     = isset( $data['gender'] ) && in_array( $data['gender'], [ 'M', 'F' ] ) ? $data['gender'] : '';

        if( $birthday == '' || !strtotime( $birthday ) || $this->user_obj->getBirthday() )
        $birthday = $this->user_obj->getBirthday();

        if( $gender == '' || $this->user_obj->getGender() )
        $gender = $this->user_obj->getGender();

        $query  = 'UPDATE ' . $this->table( 'users' );
        $query .= ' SET name = ?, full_name = ?, birthday = ?, gender = ?, avatar = ?, address = ?, country = ?, lcc = ?, tz = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sssssssssi', $data['username'], $data['full_name'], $birthday, $gender, $media_avatar, $data['address'], $country_id, $lcc, $timezone, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-profile', $this->user_obj, $data, $media );

            if( $country_id !== $old_country_id )
            actions()->do_action( 'after:country-switch', $this->user_obj, $country_id, $old_country_id );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function become_surveyor( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'become-surveyor-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-become-surveyor', true, $this->user, $data ) )
            return ;

        $surveyor = isset( $data['surveyor'] );

        $query  = 'UPDATE ' . $this->table( 'users' );
        $query .= ' SET surveyor = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $surveyor, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:become-surveyor', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function change_password( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'change-password-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-change-password', true, $this->user, $data ) )
            return ; 
        else if( !isset( $data['cpassword' ] ) || !isset( $data['password' ] ) || !isset( $data['password2'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( md5( $data['cpassword'] ) != me()->getPassword() )
            throw new \Exception( t( 'Your current password is invalid' ) );
        else if( $data['password'] != $data['password2'] )
            throw new \Exception( t( 'Passwords do not match' ) );
        else if( !\util\etc::check_password( $data['password'] ) )
            throw new \Exception( t( 'The new password must contain at least 6 characters (letters and numbers)' ) );

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET password = MD5(?) WHERE id = ?';

        $details = serialize( $data );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['password'], $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:change-password', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function preferences( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-preferences-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-preferences', true, $this->user, $data ) )
            return ; 
        else if( !isset( $data['language'] ) || !isset( $data['timezone'] ) || !isset( $data['hour_format'] ) || !isset( $data['date_format'] ) || !isset( getLanguages()[$data['language']] ) || !isset( $data['firstday'] ) ||  !in_array( (int) $data['firstday'], [ 0, 1 ] ) || !in_array( $data['hour_format'], [ 12, 24 ] ) || !in_array( $data['date_format'], filters()->do_filter( 'date-formats', [ 'm/d/y' => 'm/d/y', 'd/m/y' => 'd/m/y', 'y/m/d' => 'y/m/d' ] ) ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET lang = ?, f_hour = ?, f_date = ?, tz = ?, fdweek = ? WHERE id = ?';

        $country    = $this->user_obj->getCountry();

        if( !$country->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        $old_language = $this->user_obj->getLanguageId(); 
        $languages  = getLanguages();
        $language   = $languages[$data['language']] ?? NULL;

        if( !$language )
        throw new \Exception( t( 'Unexpected' ) );

        $timezones  = $country->getTimezones();

        if( !$timezones[$data['timezone']] )
        throw new \Exception( t( 'Unexpected' ) );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ssssii', $data['language'], $data['hour_format'], $data['date_format'], $timezones[$data['timezone']], $data['firstday'], $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-preferences', $this->user_obj, $data );

            if( $language !== $old_language )
            actions()->do_action( 'after:language-switch', $this->user_obj, $language, $old_language );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function payout_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-payout-options-form-options-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-payout-options', true, $this->user, $data ) )
            return ;
        else if( !isset( $data['pp_address' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filter_var( $data['pp_address'], FILTER_VALIDATE_EMAIL ) )
            throw new \Exception( t( 'Wrong email address' ) );
        else if( !isset( $data['stripe_address' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filter_var( $data['stripe_address'], FILTER_VALIDATE_EMAIL ) )
            throw new \Exception( t( 'Wrong email address' ) );

        $errors         = 0;
        $pp_address     = me()->actions()->saveOption( 'pp_address', $data['pp_address'] );
        $stripe_address = me()->actions()->saveOption( 'stripe_address', $data['stripe_address'] );

        if( $pp_address )
        $errors++;

        if( $stripe_address )
        $errors++;

        if( $errors )
        return true;

        throw new \Exception( t( "Some options couldn't be saved" ) );
    }

    public function privacy_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-privacy-options-form-options-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-privacy-options', true, $this->user, $data ) )
            return ;
        else if( !isset( $data['dname' ] ) || !in_array( $data['dname'], [ 'username', 'fullname' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET dfname = ?, trans = ? WHERE id = ?';

        $dname  = $data['dname'] == 'fullname';
        $trans  = isset( $data['transfer'] );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $dname, $trans, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-privacy-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function security_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-security-options-form-options-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-security-options', true, $this->user, $data ) )
        return ;

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET twosv = ? WHERE id = ?';

        $twosv = isset( $data['twosv'] ) ?: 0;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $twosv, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-security-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_subscription( int $subscription, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'edit-subscription-form-sanitize-data', $data, $subscription );

        if( !filters()->do_filter( 'custom-error-edit-subscription', true, $this->user, $subscription, $data ) )
        return ;

        $query  = 'UPDATE ';
        $query .= $this->table( 'subscriptions' );
        $query .= ' SET autorenew = ? WHERE id = ? AND user = ?';

        $autorenew = isset( $data['auto-renew'] );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $autorenew, $subscription, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-subscription', $this->user_obj, $subscription, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function cancel_subscription( int $subscription, array $data = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'cancel-subscription-form-sanitize-data', $data, $subscription );

        if( !isset( $data['agree'] ) )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-cancel-subscription', true, $this->user, $subscription, $data ) )
        return ;
        
        $query  = 'DELETE FROM ';
        $query .= $this->table( 'subscriptions' );
        $query .= ' WHERE id = ? AND user = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $subscription, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:cancel-subscription', $this->user_obj, $subscription, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function website_general_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-general-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-general-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = isset( $data['website_name'] ) && $site_options->saveOption( 'website_name', $data['website_name'] );
        $opt2           = isset( $data['website_desc'] ) && $site_options->saveOption( 'website_desc', $data['website_desc'] );
        $opt3           = isset( $data['website_url'] ) && $site_options->saveOption( 'site_url', $data['website_url'] );
        $opt4           = isset( $data['withdraw_min'] ) && $site_options->saveOption( 'withdraw_min', $data['withdraw_min'] );
        $opt5           = isset( $data['deposit_min'] ) && $site_options->saveOption( 'deposit_min', $data['deposit_min'] );
        $opt6           = isset( $data['def_country'] ) && $site_options->saveOption( 'def_country', $data['def_country'] );
        $opt7           = isset( $data['def_language'] ) && $site_options->saveOption( 'def_language', $data['def_language'] );

        if( $opt1 || $opt2 || $opt3 || $opt4 || $opt5 || $opt6 || $opt7 ) {
            actions()->do_action( 'after:edit-general-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_paypal_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-paypal-options-form-options-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-paypal-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = isset( $data['paypal_client_id' ] ) && $site_options->saveOption( 'paypal_client_id', $data['paypal_client_id' ] );
        $opt2           = isset( $data['paypal_secret'] ) && $site_options->saveOption( 'paypal_secret', $data['paypal_secret' ] );

        if( $opt1 || $opt2 ) {
            actions()->do_action( 'after:edit-paypal-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_stripe_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-stripe-options-form-options-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-stripe-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = isset( $data['stripe_secret_key' ] ) && $site_options->saveOption( 'stripe_secret_key', $data['stripe_secret_key' ] );

        if( $opt1 || $opt2 ) {
            actions()->do_action( 'after:edit-stripe-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_email_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-email-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-email-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = isset( $data['email_type'] ) && $site_options->saveOption( 'email_type',  $data['email_type' ] );
        $opt2           = isset( $data['mail_smtp'] ) && is_array( $data['mail_smtp'] ) && $site_options->saveOption( 'mail_smtp',   serialize( $data['mail_smtp' ] ) );

        if( $opt1 || $opt2 ) {
            actions()->do_action( 'after:edit-email-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function edit_email_template( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'website-email-options-form-sanitize-data', $data );
        $tpl    = $data['tpl'] ?? [];

        if( !$tpl || !isset( $tpl['template'] ) )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-website-email-options', true, $this->user, $tpl ) )
        return ;

        $templates  = new \site\se_template;
        $templates  ->getTemplate( $tpl['template'], true );
        if( !empty( $tpl['from_name'] ) )
        $templates  ->setFromName( $tpl['from_name'] );
        if( !empty( $tpl['from_email'] ) )
        $templates  ->setFromEmailAddress( $tpl['from_email'] );
        if( !empty( $tpl['subject'] ) )
        $templates  ->setSubject( $tpl['subject'] );
        if( !empty( $tpl['body'] ) )
        $templates  ->setBody( $tpl['body'] );

        if( $templates->save() ) {
            actions()->do_action( 'after:edit-email-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_prices_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-prices-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-prices-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = isset( $data['min_cpa_self'] ) && $site_options->saveOption( 'min_cpa_self', $data['min_cpa_self' ] );
        $opt2           = isset( $data['min_cpa'] ) && $site_options->saveOption( 'min_cpa', $data['min_cpa' ] );
        $opt3           = isset( $data['comm_cpa'] ) && $site_options->saveOption( 'comm_cpa', $data['comm_cpa' ] );

        if( $opt1 || $opt2 || $opt3 ) {
            actions()->do_action( 'after:edit-prices-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_invoicing_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-invoicing-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-invoicing-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'invoicing_settings', cms_json_encode( $data ) );

        if( $opt1 ) {
            actions()->do_action( 'after:edit-invoicing-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_kyc_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-kyc-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-kyc-options', true, $this->user, $data ) )
        return ;

        if( isset( $data['langs'] ) )
        foreach( $data['langs'] as $langId => $items ) {
            array_pop( $items );
            $data['langs'][$langId] = array_values( $items );
        }

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'kyc_settings', cms_json_encode( $data ) );

        if( $opt1 ) {
            actions()->do_action( 'after:edit-kyc-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_tos_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-tos-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-tos-options', true, $this->user, $data ) )
        return ;

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'terms_of_use', cms_json_encode( $data ) );

        if( $opt1 ) {
            actions()->do_action( 'after:edit-tos-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_security_options( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-security-options-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-website-security-options', true, $this->user, $data ) )
            return ;

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'recaptcha_key', ( $data['recaptcha_key'] ?? '' ) );
        $opt2           = $site_options->saveOption( 'recaptcha_secret', ( $data['recaptcha_secret'] ?? '' ) );
        $opt3           = $site_options->saveOption( 'femail_verify', ( $data['femail_verify'] ?? 0 ) );
        $opt4           = $site_options->saveOption( 'auto_approve_surveys', ( $data['auto_approve_surveys'] ?? 1 ) );

        if( $opt1 || $opt2 || $opt3 || $opt4 ) {
            actions()->do_action( 'after:edit-website-security-options', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function website_seo_settings( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'website-seo-settings-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-seo-settings', true, $this->user, $data ) )
            return ;

        $form   = new \markup\front_end\form_fields( [
            'favicon' => [ 'type' => 'image', 'category' => 'settings' ]
        ] );
        $form   ->setValues( filters()->do_filter( 'form:values:seo-settings', [
            'favicon'  => get_option_json( 'front_end_favicon', [] )
        ], $data ) );
        $form   ->build();

        $media  = $form->uploadFiles( $data );

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'meta_tag_title', ( $data['title'] ?? '' ) );
        $opt2           = $site_options->saveOption( 'meta_tag_desc', ( $data['description'] ?? '' ) );
        $opt3           = $site_options->saveOption( 'meta_tag_keywords', ( $data['keywords'] ?? '' ) );
        if( isset( $media['data[favicon]'] ) )
        $opt4           = $site_options->saveOption( 'front_end_favicon', cms_json_encode( $media['data[favicon]'] ) );

        if( $opt1 || $opt2 || $opt3 ) {
            actions()->do_action( 'after:edit-website-seo-settings', $this->user_obj, $data, $media );
            return true;
        }

        throw new \Exception( t( 'Some of the options could not be saved' ) );
    }

    public function clean_website( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data = filters()->do_filter( 'clean-website-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-clean-website', true, $this->user, $data ) )
            return ;

        if( !empty( $data['surveys'] ) ) {
            // Delete surveys
            $query  = 'DELETE FROM ' . $this->table( 'surveys' ) . ' WHERE id IN (SELECT survey FROM ';
            $query  .= $this->table( 'deleted_surveys' );
            $query  .= ' WHERE date < DATE_ADD(NOW(), INTERVAL -? DAY));';

            $days   = (int) ( $data['surveys_old'] ?? 0 );

            if( !isset( $stmt ) )
            $stmt   = $this->db->stmt_init();
            $stmt   ->prepare( $query );
            $stmt   ->bind_param( 'i', $days );

            if( $stmt->execute() ) {
                $query  = 'DELETE FROM ';
                $query  .= $this->table( 'deleted_surveys' ) . ' WHERE date < DATE_ADD(NOW(), INTERVAL -? DAY)';

                if( !isset( $stmt ) )
                $stmt   = $this->db->stmt_init();
                $stmt   ->prepare( $query );
                $stmt   ->bind_param( 'i', $days );
                $stmt   ->execute();
            }
        }

        if( !empty( $data['media'] ) ) {
            // Delete media attachments
            $media = new \query\media;
            foreach( $media->deleted()->fetch( -1 ) as $media ) {
                mediaLinks()->deleteMedia( $media );
            }

            mediaLinks()->clearDeletedFiles();
        }

        if( !empty( $data['chat'] ) && !empty( $data['chat_old'] ) ) {
            // Delete chat messages
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'teams_chat' );
            $query  .= ' WHERE date < DATE_ADD(NOW(), INTERVAL -? MONTH)';

            if( !isset( $stmt ) )
            $stmt   = $this->db->stmt_init();
            $stmt   ->prepare( $query );
            $stmt   ->bind_param( 'i', $data['chat_old'] );
            $stmt   ->execute();
        }

        if( !empty( $data['code'] ) ) {
            // Verification codes
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'verif_codes' );
            $query  .= ' WHERE expiration < NOW()';

            if( !isset( $stmt ) )
            $stmt   = $this->db->stmt_init();
            $stmt   ->prepare( $query );
            $stmt   ->execute();
        }

        if( !empty( $data['carts'] ) ) {
            // Abandoned loyalty points carts
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'shop_cart' );
            $query  .= ' WHERE date < DATE_ADD(NOW(), INTERVAL -30 DAY)';

            if( !isset( $stmt ) )
            $stmt   = $this->db->stmt_init();
            $stmt   ->prepare( $query );
            $stmt   ->execute();
        }

        if( isset( $stmt ) )
        $stmt->close();
    
        actions()->do_action( 'after:clean-website', $this->user_obj, $data );

        return true;
    }

    public function add_survey( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-survey-form-sanitize-data', $data );

        $owner      = $this->user_obj;
        $owner_id   = $this->user;
        $team       = NULL;

        if( isset( $data['share'] ) && ( $data['share'] == 's' || $data['share'] == 'f' ) ) {
            $uteam = $this->user_obj->myTeam();
            if( $uteam ) {
                if( !me()->manageTeam( 'add-survey' ) )
                throw new \Exception( t( "Sorry! You can't add new surveys" ) );

                $owner      = $uteam->getUser();
                if( !$owner->getObject() ) {
                    throw new \Exception( t( 'Unexpected' ) );
                }
                $owner_id   = $uteam->getUserId();
                $team       = $uteam;
                if( $data['share'] == 's' )
                $team_id    = $uteam->getId();
            }
        }

        $slimit = $owner->limits()->surveys();

        if( isset( $team_id ) && ( $slimit > -1 && $owner->ownSurveys() >= $slimit ) )
            throw new \Exception( t( 'Your team has reached the surveys limit' ) );
        else if( $slimit > -1 && $owner->ownSurveys() >= $slimit )
            throw new \Exception( t( 'You have reached the surveys limit' ) );
        else if( !isset( $data['responses'] ) || ( ( $rlimit = $owner->limits()->responses() ) > 0 && (int) $data['responses'] > $owner->limits()->responses() ) )
            throw new \Exception( sprintf( t( 'You can request a maximum of %s responses and at least one' ), $owner->limits()->responses() ) );
        else if( !filters()->do_filter( 'custom-error-add-survey', true, $this->user, $data ) )
            return ;

        $approved = get_option( 'auto_approve_surveys', 1 );

        $query  = 'INSERT INTO ' . $this->table( 'surveys' );
        $query .= ' (user, team, lu_user, name, category, ent_target, status, approved) VALUES (?, ?, ?, ?, ?, ?, 1, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiisiii', $owner_id, $team_id, $this->user, $data['name'], $data['category'], $data['responses'], $approved );
        $e  = $stmt->execute();
        $s  = $stmt->insert_id;

        if( $e ) {
            if( isset( $data['share'] ) ) {
                switch( $data['share'] ) {
                    case 's':
                        if( $team ) {
                            $members    = $team->members()
                                        ->excludeUserId( $this->user )
                                        ->excludeUserId( $owner_id )
                                        ->setApproved();

                            $query  = 'INSERT INTO ' . $this->table( 'usr_surveys' );
                            $query .= ' (user, survey, team) VALUES (?, ?, ?)';
                    
                            $stmt = $this->db->stmt_init();
                            $stmt->prepare( $query );

                            // Insert the survey in the creator's account
                            if( $owner_id != $this->user ) {
                                $stmt->bind_param( 'iii', $this->user, $s, $team_id );
                                $stmt->execute();
                            }

                            // Insert the survey in every single account of this team
                            foreach( $members->fetch( -1 ) as $member ) {
                                $user   = $members->getUserObject( $member );
                                $userId = $user->getId();

                                $stmt->bind_param( 'iii', $userId, $s, $team_id );
                                $stmt->execute();
                            }
                        }
                    break;

                    case 'f':
                        if( $team ) {
                            $query  = 'INSERT INTO ' . $this->table( 'usr_surveys' );
                            $query .= ' (user, survey, team) VALUES (?, ?, ?)';
                    
                            $stmt = $this->db->stmt_init();
                            $stmt->prepare( $query );

                            // Insert the survey in the creator's account
                            if( $owner_id != $this->user ) {
                                $stmt->bind_param( 'iii', $this->user, $s, $team_id );
                                $stmt->execute();
                            }

                            if( !empty( $data['member'] ) ) {
                                $members    = $team->members()
                                ->excludeUserId( $this->user )
                                ->excludeUserId( $owner_id )
                                ->setApproved();
                                $smembers   = array_intersect_key( $members->fetch( -1 ), $data['member'] );

                                // Insert the survey in selected accounts
                                foreach( $smembers as $member ) {
                                    $members->setObject( $member );
                                    $user   = $members->getUserObject( $member );
                                    $userId = $user->getId();

                                    $stmt->bind_param( 'iii', $userId, $s, $team_id );
                                    $stmt->execute();
                                }
                            }
                        }
                    break;
                }
            }

            $stmt->close();

            actions()->do_action( 'after:add-survey', $this->user_obj, $s, $data );

            return $s;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_survey( object $survey, array $data, array $files = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data           = filters()->do_filter( 'edit-survey-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || empty( $data['responses'] ) || empty( $data['theme'] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        $themes = filters()->do_filter( 'survey-themes', [] );

        if( !isset( $themes[$data['theme']] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        if( $survey->getTeamId() && ( $uteam = $this->user_obj->myTeam() ) && ( $owner = $uteam->getUser() ) ) {
            if( !$owner->getObject() )
            throw new \Exception( t( 'Unexpected' ) );
        } else $owner = $this->user_obj;

        if( !isset( $data['responses'] ) || ( ( $rlimit = $owner->limits()->responses() ) > 0 && (int) $data['responses'] > $owner->limits()->responses() ) )
        throw new \Exception( sprintf( t( 'You can request a maxim of %s responses and at least one' ), $owner->limits()->responses() ) );

        if( isset( $data['status'] ) ) {
            switch( (int) $data['status'] ) {
                case 3:
                case 5:
                    $status = (int) $data['status'];
                break;

                case 4:
                    if( $survey->getQuestionsCount() == 0 )
                        throw new \Exception( t( 'You need to add questions before you can publish the survey' ) );
                    else if( $survey->getResponses() >= (int) $data['responses'] )
                        throw new \Exception( t( 'Responses exceded your target, before you can publish this survey increse your responses target' ) );
                    else
                $status = 4;
                break;
            }
        }

        if( !filters()->do_filter( 'custom-error-edit-survey', true, $this->user, $survey, $data ) )
        return ;

        // Only inputs that can deal with media
        $avatar = $survey->getAvatar() ? [ $survey->getAvatar() => $survey->getAvatarURL() ] : NULL;
        $fields = filters()->do_filter( 'form:fields:edit-survey', [
            'avatar'    => [ 'type' => 'image', 'category' => 'survey-avatar', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId() ]
        ], $survey );

        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->setValues( filters()->do_filter( 'form:values:edit-survey', [
            'avatar'  => $avatar
        ], $survey, $data ) );
        $form   ->build();

        $media  = $form->uploadFiles( $data );

        if( count( $media['data[avatar]'] ) )
        $media_avatar = key( $media['data[avatar]'] );

        if( !isset( $status ) )
        $status = $survey->getStatus();

        $autoa  = isset( $data['autoa'] ) ?: 0;
        $s_id   = $survey->getId();

        $query  = 'UPDATE ' . $this->table( 'surveys' );
        $query .= ' SET lu_user = ?, name = ?, avatar = ?, category = ?, ent_target = ?, status = ?, template = ?, autovalid = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'isiiiisii', $this->user, $data['name'], $media_avatar, $data['category'], $data['responses'], $status, $data['theme'], $autoa, $s_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-survey', $this->user_obj, $survey, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_step( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'add-step-survey-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-step', true, $this->user, $survey, $data ) )
            return ;

        $s_id   = $survey->getId();

        $query  = 'INSERT INTO ' . $this->table( 'step_questions' );
        $query .= ' (name, survey) VALUES (?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['name'], $s_id );
        $e  = $stmt->execute();
        $s  = $stmt->insert_id;
        $stmt->close();

        if( $e ) {            
            actions()->do_action( 'after:add-step', $this->user_obj, $survey, $s, $data );
            return $s;
        }


        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_step( object $step, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-step-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || !isset( $data['after'] ) || !isset( $data['actions'] ) || !isset( $data['step'] ) || !isset( $data['c_fallback'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-step', true, $this->user, $step, $data ) )
            return ;

        $action     = 0;
        $action_id  = 0;
        $emsg       = NULL;
    
        if( $data['after'] == 'conditions' ) {
            $action = 2;
            $conds  = $data['conditions'];
            array_pop( $conds );

            if( isset( $data['c_fallback'] ) ) {
                switch( $data['c_fallback'] ) {
                    case 'finish': break;

                    case 'disqualify': 
                        $action_id  = 1; 
                    break;

                    case 'message':
                        $action_id  = 2; 
                        $emsg       = $data['c_fallback_msg'] ?? NULL; 
                    break;

                    
                    default:
                    $steps  = steps();
                    $steps  ->setId( (int) $data['c_fallback'] );
                    if( $steps->getObject() && $steps->getSurveyId() == $step->getSurveyId() ) {
                        $action     = 3;
                        $action_id  = $steps->getId();
                    }
                }
            }

            $conditions = $step->getConditions()->fetch( -1 );
            $deleted    = array_diff_key( $conditions, $conds );

            foreach( $conds as $opt_id => $cond ) {
                $condition = $conditions[$opt_id] ?? NULL;

                if( !$condition ) {
                    // new condition
                    if( !isset( $cond['action'] ) || !isset( $cond['points'] ) ) continue;
                    switch( $cond['action'] ) {
                        case 'finish':
                            me()->form_actions()->add_step_condition( $step->getSurveyId(), $step->getId(), [ 'action' => 0, 'action_id' => 0, 'points' => $cond['points'] ] );
                        break;

                        case 'disqualify':                             
                            me()->form_actions()->add_step_condition( $step->getSurveyId(), $step->getId(), [ 'action' => 1, 'action_id' => 0, 'points' => $cond['points'] ] );
                        break;

                        case 'message':                             
                            me()->form_actions()->add_step_condition( $step->getSurveyId(), $step->getId(), [ 'action' => 3, 'action_id' => 0, 'points' => $cond['points'], 'message' => ( $cond['message'] ?? '' ) ] );
                        break;

                        default:
                        $steps  = steps();
                        $steps  ->setId( $cond['action'] );
                        if( $steps->getObject() && $steps->getSurveyId() == $step->getSurveyId() )
                        me()->form_actions()->add_step_condition( $step->getSurveyId(), $step->getId(), [ 'action' => 2, 'action_id' => $steps->getId(), 'points' => $cond['points'] ] );
                    }
                } else {
                    // old condition
                    if( !isset( $cond['action'] ) || !isset( $cond['points'] ) ) continue;

                    switch( $cond['action'] ) {
                        case 'finish':
                            if( $condition->action != 0 || $cond['points'] != $condition->points )
                            me()->form_actions()->edit_step_condition( $condition->id, $step->getSurveyId(), $step->getId(), [ 'action' => 0, 'action_id' => 0, 'points' => $cond['points'] ] );
                        break;

                        case 'disqualify':
                            if( $condition->action != 1 || $cond['points'] != $condition->points )
                            me()->form_actions()->edit_step_condition( $condition->id, $step->getSurveyId(), $step->getId(), [ 'action' => 1, 'action_id' => 0, 'points' => $cond['points'] ] );
                        break;

                        case 'message':
                            if( $condition->action != 3 || $cond['points'] != $condition->points || ( !isset( $cond['message'] ) || $cond['message'] != $condition->emsg ) )
                            me()->form_actions()->edit_step_condition( $condition->id, $step->getSurveyId(), $step->getId(), [ 'action' => 3, 'action_id' => 0, 'points' => $cond['points'], 'message' => ( $cond['message'] ?? '' ) ] );
                        break;

                        default:
                        if( $condition->action != 2 || $cond['points'] != $condition->points ) {
                            $steps  = steps();
                            $steps  ->setId( $cond['action'] );
                            if( $steps->getObject() && $steps->getSurveyId() == $step->getSurveyId() )
                            me()->form_actions()->edit_step_condition( $condition->id, $step->getSurveyId(), $step->getId(), [ 'action' => 2, 'action_id' => $steps->getId(), 'points' => $cond['points'] ] );
                        }
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $cond ) {
                    me()->form_actions()->delete_step_condition( $cond->id, $step->getSurveyId(), $step->getId() );
                }
            }

        } else if( $data['actions'] == 'goto' ) {
            $steps      = steps();
            $steps      ->setId( (int) $data['step'] );

            if( $steps->getObject() && $steps->getSurveyId() == $step->getSurveyId() ) {
                $action     = 1;
                $action_id  = $steps->getId();
            }
        }

        $id     = $step->getId();

        if( !empty( $data['info'] ) )
        $settings = $data['info'];

        if( !$step->isMain() ) {
            if( empty( $data['hnav'] ) ) {
                $settings['hnav'] = true;
            }
        }

        if( !empty( $data['mtime'] ) && !empty( $data['ntime'] ) && (int) $data['ntime'] >= 1 && (int) $data['ntime'] <= RESPONSE_TIME_LIMIT )
        $settings['time'] = (int) $data['ntime'];

        if( !empty( $settings ) )
        $setting = cms_json_encode( $settings );

        $query  = 'UPDATE ' . $this->table( 'step_questions' );
        $query .= ' SET name = ?, action = ?, action_id = ?, emsg = ?, setting = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'siissi', $data['name'], $action, $action_id, $emsg, $setting, $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-step', $this->user_obj, $step, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_step( object $step, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'delete-step-form-sanitize-data', $data );

        if( empty( $data['action'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-delete-step', true, $this->user, $step, $data ) )
            return ;

        $query  = 'DELETE FROM ' . $this->table( 'step_questions' );
        $query  .= ' WHERE id = ?';

        $id     = $step->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e  = $stmt->execute();

        if( $e ) {
            actions()->do_action( 'after:delete-step', $this->user_obj, $step, $data );

            if( $data['action'] == 'move' && isset( $data['step'] ) && ( $newStep = steps( (int) $data['step'] ) ) && $newStep->getObject() && $step->getSurveyId() == $newStep->getSurveyId() ) {
                $query  = 'UPDATE ' . $this->table( 'questions' );
                $query  .= ' SET step = ? WHERE survey = ? AND step = ?';
        
                $newId  = $newStep->getId();
                $surveyId = $newStep->getSurveyId();

                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'iii', $newId, $surveyId, $id );
                $stmt->execute();
            } else {
                $query  = 'DELETE FROM ' . $this->table( 'questions' );
                $query  .= ' WHERE survey = ? AND step = ?';
        
                $newId      = $step->getId();
                $surveyId   = $step->getSurveyId();

                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ii', $surveyId, $id );
                $stmt->execute();
            }

            $stmt->close();

            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_survey_page( object $survey, string $page, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-survey-page-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-survey-page', true, $this->user, $survey, $page, $data ) )
        return ;

        array_pop( $data['content'] );

        $meta   = $survey->meta();
        $sh     = new \site\shortcodes;
        $value  = $sh->toShortcodeFromArray( $data['content'] );

        if( $meta->save( 'p:' . $page, $value ) ) {
            actions()->do_action( 'after-survey-edit-page-content', $survey, $page, $data );
        }

        $aa = $data['after_action'] ?? NULL;
        $rt = $data['redirect_to'] ?? NULL;
        if( empty( $aa ) || !$rt )
            $af = '';
        else {
            if( !filter_var( $rt, FILTER_VALIDATE_URL ) )
            throw new \Exception( t( 'The web address is invalid' ) );

            $af = cms_json_encode( [ $aa => $rt ] );
        }

        if( $meta->save( 'af:' . $page, $af, '', true ) )
        actions()->do_action( 'after:survey-edit-page', $this->user_obj, $survey, $af );

        return true;
    }

    public function edit_logo_survey( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data   = filters()->do_filter( 'edit-survey-logo-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-survey-logo', true, $this->user, $data ) )
        return ;

        $meta   = $survey->meta();
        $logoId = $meta->get( 'logo', false );

        $form   = new \markup\front_end\form_fields( [
            'logo' => [ 'type' => 'image', 'category' => 'survey-logo', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId()  ],
        ] );
        $form   ->build();

        if( $logoId && ( $logoURL = mediaLinks( $logoId )->getItemURL() ) ) {
            $form->setValues( [
                'logo' => [ $logoId => $logoURL ]
            ] );
        }

        $media  = $form->uploadFiles( $data );

        if( empty( $data['logo'] ) ) {
            if( $logoId ) {
                mediaLinks( $logoId )->deleteItem();
                // delete old logo
                $meta->delete( 'logo' );
            }
        }

        if( !empty( $media['data[logo]'] ) )
        $meta->save( 'logo', key( $media['data[logo]'] ) );

        return true;
    }

    public function edit_meta_tags_survey( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data   = filters()->do_filter( 'edit-survey-meta-tags-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-survey-meta-tags', true, $this->user, $survey, $data ) )
        return ;

        $meta   = $survey->meta();
        $imgId  = $meta->get( 'meta_image', false );
        $title  = $meta->save( 'meta_title', ( $data['title'] ?? '' ), '', true );
        $desc   = $meta->save( 'meta_desc', ( $data['desc'] ?? '' ), '', true );

        $form   = new \markup\front_end\form_fields( [
            'image' => [ 'type' => 'image', 'category' => 'survey-logo', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId()  ],
        ] );
        $form   ->build();

        if( $imgId && ( $imageURL = mediaLinks( $imgId )->getItemURL() ) ) {
            $form->setValues( [
                'image' => [ $imgId => $imageURL ]
            ] );
        }

        $media  = $form->uploadFiles( $data );

        if( empty( $data['image'] ) ) {
            if( $imgId ) {
                mediaLinks( $imgId )->deleteItem();
                // delete old image
                $meta->delete( 'image' );
            }
        }

        if( !empty( $media['data[image]'] ) )
        $meta->save( 'meta_image', key( $media['data[image]'] ) );

        return true;
    }

    public function texts_survey( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data   = filters()->do_filter( 'edit-survey-texts-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-survey-texts', true, $this->user, $data ) )
        return ;

        if( !empty( $data['texts'] ) && is_array( $data['texts'] ) )
        $survey->meta()->save( 'texts', cms_json_encode( $data['texts'] ) );
        else
        return false;
    }

    public function add_label_item( object $result, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( empty( $data ) )
        throw new \Exception( t( 'Something went wrong' ) );

        $labels = $result->getSurvey()
                ->getLabels()
                ->select( [ 'id' ] )
                ->fetch( -1 );
        $r_id   = $result->getId();

        foreach( $data as $label => $value ) {
            if( !isset( $labels[$label] ) )
            continue;
            
            // Add
            if( $value ) {
                if( !isset( $stmt_i ) ) {
                    $query  = 'INSERT INTO ' . $this->table( 'label_items' );
                    $query .= ' (label, result) VALUES (?, ?)';

                    $stmt_i = $this->db->stmt_init();
                    $stmt_i ->prepare( $query );
                }

                $stmt_i ->bind_param( 'ii', $label, $r_id );

                if( $stmt_i->execute() )
                actions()->do_action( 'after:add-label-item', $this->user_obj, $result, $label );

            // Delete
            } else {
                if( !isset( $stmt_i ) ) {
                    $query  = 'DELETE FROM ' . $this->table( 'label_items' );
                    $query .= ' WHERE label = ? AND result = ?';

                    $stmt_d = $this->db->stmt_init();
                    $stmt_d ->prepare( $query );
                }
                
                $stmt_d ->bind_param( 'ii', $label, $r_id );

                if( $stmt_d->execute() )
                actions()->do_action( 'after:remove-label-item', $this->user_obj, $result, $label );
            }
        }

        if( isset( $stmt_i ) )
        $stmt_i->close();
        if( isset( $stmt_d ) )
        $stmt_d->close();

        return true;
    }

    public function add_label_checked_item( object $result, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( empty( $data ) )
        throw new \Exception( t( 'Something went wrong' ) );

        $r_id   = $result->getId();

        $query  = 'UPDATE ' . $this->table( 'label_items' );
        $query .= ' SET checked = ? WHERE id = ? AND result = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );

        foreach( $data as $item => $value ) {
            if( $value ) $value = 1;
            else unset( $value );
            $stmt->bind_param( 'iii', $value, $item, $r_id );
            if( $stmt->execute() )
            actions()->do_action( 'after:label-checked-item', $this->user_obj, $result, $item, $value );
        }

        $stmt->close();

        return true;
    }

    public function add_label( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data           = filters()->do_filter( 'add-label-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-label', true, $this->user, $survey, $data ) )
            return ;

        $s_id   = $survey->getId();
        $color  = $data['color'] ?? 'A';

        $query  = 'INSERT INTO ' . $this->table( 'labels' );
        $query .= ' (survey, name, color) VALUES (?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sss', $s_id, $data['name'], $color );
        $e  = $stmt->execute();
        $l  = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-label', $this->user_obj, $survey, $l, $data );

            return $l;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_survey_label( object $label, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-label-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-label', true, $this->user, $data ) )
            return ;

        $query  = 'UPDATE ' . $this->table( 'labels' );
        $query .= ' SET name = ?, color = ? WHERE id = ?';

        $id = $label->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ssi', $data['name'], $data['color'], $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-label', $this->user_obj, $label, $data );     
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_survey_label( object $label ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-label', true, $this->user, $label ) )
            return ;

        $id     = $label->getId();

        $query  = 'DELETE FROM ' . $this->table( 'labels' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {            
            actions()->do_action( 'after:delete-survey-label', $this->user_obj, $label );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_response_comment( object $survey, object $response, array $data = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-response-comment-form-sanitize-data', $data );
        $data['msg']    = isset( $data['msg'] ) ? trim( $data['msg'] ) : false;

        if( !empty( $data['msg'] ) )
        $comment = $data['msg'];
 
        if( !filters()->do_filter( 'custom-error-edit-response-comment', true, $this->user, $survey, $response, $data ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'results' );
        $query .= ' SET comment = ? WHERE id = ?';

        $id = $response->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $comment, $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-response-comment', $this->user_obj, $survey, $response, $data );       
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_report( object $report, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-report-form-sanitize-data', $data );
        $data['title']   = isset( $data['title'] ) ? trim( $data['title'] ) : false;

        if( empty( $data['title'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-report', true, $this->user, $report, $data ) )
            return ;

        $query  = 'UPDATE ' . $this->table( 'saved_reports' );
        $query .= ' SET title = ? WHERE id = ?';

        $id = $report->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['title'], $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-report', $this->user_obj, $report, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function share_report( object $report, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'share-report-form-sanitize-data', $data );
        $myTeam = $this->user_obj->myTeam();

        if( !$myTeam || !isset( $data['share'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-share-report', true, $this->user, $report, $data ) )
            return ;

        $reportId   = $report->getId();
        $members    = $myTeam->members()
                    ->excludeUserId( $this->user )
                    ->setApproved();

        switch( $data['share'] ) {
            case 's':
                $query  = 'INSERT INTO ' . $this->table( 'shared_reports' );
                $query .= ' (user, report) VALUES (?, ?)';
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );

                // Send the survey to every member of this team
                foreach( $members->fetch( -1 ) as $member ) {
                    $user   = $members->getUserObject( $member );
                    $userId = $user->getId();

                    $stmt->bind_param( 'ii', $userId, $reportId );
                    if( $stmt->execute() )
                    actions()->do_action( 'after:share-report', $this->user_obj, $report, $userId, $data );
                }

                $stmt->close();

                return true;
            break;

            case 'f':
                $smembers   = array_intersect_key( $members->fetch( -1 ), $data['member'] );

                $query  = 'INSERT INTO ' . $this->table( 'shared_reports' );
                $query .= ' (user, report) VALUES (?, ?)';
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );

                // Send the survey to every member of this team
                foreach( $smembers as $member ) {
                    $members->setObject( $member );
                    $user   = $members->getUserObject( $member );
                    $userId = $user->getId();

                    $stmt->bind_param( 'ii', $userId, $reportId );
                    if( $stmt->execute() )
                    actions()->do_action( 'after:share-report', $this->user_obj, $report, $userId, $data );
                }

                $stmt->close();

                return true;
            break;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_report( object $report, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'delete-report-form-sanitize-data', $data );

        if( !isset( $data['agree'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-delete-report', true, $this->user, $report, $data ) )
            return ;

        $query  = 'DELETE FROM ' . $this->table( 'saved_reports' );
        $query .= ' WHERE id = ?';

        $id = $report->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-report', $this->user_obj, $report, $data ); 
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function customize_survey_dashboard( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data   = filters()->do_filter( 'customize-survey-dashboard-form-sanitize-data', $data );

        if( !$this->user_obj->manageSurvey( 'view-result', $survey ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-customize-survey-dashboard', true, $this->user, $survey, $data ) )
            return ;

        $myDashboard= $data2 = [];
        $dashboard  = $survey
                    ->dashboard( $this->user )
                    ->fetch( -1 );

        array_map( function( $v ) use ( &$myDashboard ) {
            $myDashboard[$v->type . '_' . $v->type_id] = [ $v->type, $v->type_id ];
        }, $dashboard );

        foreach( $data as $type => $type_ids ) {
            foreach( $type_ids as $type_id ) {
                $data2[$type . '_' . $type_id] = [ $type, $type_id ];
            }
        }

        $dasDelete  = array_diff_key( $myDashboard, $data2 );
        $dasAdd     = array_diff_key( $data2, $myDashboard );
        $s_id       = $survey->getId();

        if( !empty( $dasAdd ) ) {
            $query      = 'INSERT INTO ' . $this->table( 'survey_dashboard' );
            $query      .= ' (survey, user, type, type_id) VALUES (?, ?, ?, ?)';
            $stmt       = $this->db->stmt_init();
            $stmt       ->prepare( $query );

            foreach( $dasAdd as $type ) {
                $stmt->bind_param( 'iiii', $s_id, $this->user, $type[0], $type[1] );
                $stmt->execute();
            }

            $stmt->close();
        }

        if( !empty( $dasDelete ) ) {
            $query      = 'DELETE FROM ' . $this->table( 'survey_dashboard' );
            $query      .= ' WHERE survey = ? AND user = ? AND type = ? AND type_id = ?';
            $stmt       = $this->db->stmt_init();
            $stmt       ->prepare( $query );

            foreach( $dasDelete as $type ) {
                $stmt->bind_param( 'iiii', $s_id, $this->user, $type[0], $type[1] );
                $stmt->execute();
            }

            $stmt->close();
        }

        return true;
    }

    public function clear_collectors( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'clear-collectors-form-sanitize-data', $data );

        if( !empty( $data['confirm'] ) && !empty( $data['collectors'] ) ) {

            if( !filters()->do_filter( 'custom-error-clear-collectors', true, $this->user, $survey, $data ) )
                return ;

            $s_id   = $survey->getId();

            $query  = 'DELETE FROM ' . $this->table( 'results' );
            $query .= ' WHERE survey = ? AND collector IN(' . implode( ',', array_map( 'intval', $data['collectors'] ) ). ')';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $s_id );
            $e  = $stmt->execute();
            $stmt->close();

            if( $e ) {
                actions()->do_action( 'after:clear-collectors', $this->user_obj, $survey, $data );

                return true;
            }

            $stmt->close();

        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_survey( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'delete-survey-form-sanitize-data', $data );

        if( !empty( $data['dagreed'] ) ) {

            if( !filters()->do_filter( 'custom-error-delete-survey', true, $this->user, $survey, $data ) )
                return ;

            $s_id   = $survey->getId();

            $query  = 'UPDATE ' . $this->table( 'surveys' );
            $query .= ' SET user = 0, team = NULL, status = -1 WHERE id = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $s_id );
            $e  = $stmt->execute();
            $stmt->close();

            if( $e ) {
                actions()->do_action( 'after:delete-survey', $this->user_obj, $survey, $data );

                return true;
            }

            $stmt->close();

        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function transfer_survey( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'transfer-survey-form-sanitize-data', $data );

        if( !isset( $data['email'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-transfer-survey', true, $this->user, $survey, $data ) )
            return ;

        $users  = users();
        if( !$users->setIdByEmail( $data['email'] ) || !$users->getObject() || $users->getId() == $this->user || !$users->isSurveyor() || !$users->getAllowTransfer() )
            throw new \Exception( t( 'Transfer cannot be made. Make sure that the email address is correct' ) );

        $query  = 'UPDATE ' . $this->table( 'results' );
        $query .= ' SET status = ? WHERE id = ?';
    
        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );

        $results = $survey->getResults();

        foreach( $results->setStatusIN( [ 1, 2 ] )->fetch( -1 ) as $result ) {
            $results->setObject( $result );
            $r_id   = $results->getId();

            // If the answer is: in pending
            if( $results->getStatus() == 1 ) {
                $status = 0;
                $stmt   ->bind_param( 'ii', $status, $r_id );
                $stmt   ->execute();

            // If the answer is: waiting approval
            } else if( $results->getStatus() == 2 ) {
                $status = 3;
                $stmt   ->bind_param( 'ii', $status, $r_id );
                if( $stmt->execute() ) {               
                    $response = new \survey\response( $results->getObject() );
                    try {
                        $response->validateResponse();
                    }
                    catch( \Exception $e ) { }
                }
            }
        }

        $s_id   = $survey->getId();
        $u_id   = $users->getId();

        $query  = 'UPDATE ' . $this->table( 'surveys' );
        $query .= ' SET user = ?, team = NULL, status = 5 WHERE id = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'ii', $u_id, $s_id );
        $e      = $stmt->execute();
        $stmt   ->close();

        if( $e ) {
            actions()->do_action( 'after:delete-response', $this->user_obj, $survey, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_response( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data   = filters()->do_filter( 'add-response-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-response', true, $this->user, $survey, $data ) )
        return ;

        $added  = [];
        $s_id   = $survey->getId();
        $limit  = (int) $data['duplicates'] ?? 1;

        if( !empty( $data['country'] ) ) $country = $data['country'];
        if( !empty( $data['comment'] ) ) $comment = $data['comment'];
        if( $limit > 1 && ( !isset( $data['action'] ) || $data['action'] != 'duplicate' ) ) $limit = 1;

        $query  = 'INSERT INTO ' . $this->table( 'results' );
        $query .= ' (survey, status, country, comment) VALUES (?, 1, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );

        for( $i = 1; $i <= $limit; $i++ ) {
            $stmt->bind_param( 'iss', $s_id, $country, $comment );
            if( $stmt->execute() ) {
                $insertId   = $stmt->insert_id;
                $added[]    = $insertId;

                if( !empty( $data['label'] ) && is_array( $data['label'] ) ) {
                    $labels = $survey->getLabels()
                            ->select( [ 'id' ] )
                            ->fetch( -1 );

                    $query  = 'INSERT INTO ' . $this->table( 'label_items' );
                    $query .= ' (label, result) VALUES (?, ?)';
            
                    $stmt2 = $this->db->stmt_init();
                    $stmt2->prepare( $query );

                    foreach( $data['label'] as $label ) {
                        if( isset( $labels[$label] ) ) {
                            $stmt2->bind_param( 'ii', $label, $insertId );
                            if( $stmt2->execute() )
                            actions()->do_action( 'after-add-label-item', $insertId, $label );
                        }
                    }

                    $stmt2->close();
                }

                actions()->do_action( 'after:self-reseponse', $this->user_obj, $survey, $insertId );
            }
        }

        $stmt->close();

        if( !empty( $added ) )
        return $added;

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_response( object $result, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'delete-response-form-sanitize-data', $data );

        if( !isset( $data['agree'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-delete-response', true, $this->user, $result, $data ) )
            return ;

        $query  = 'DELETE FROM ' . $this->table( 'results' );
        $query .= ' WHERE id = ?';

        $r_id   = $result->getId();
        $s_id   = $result->getSurveyId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $r_id );
        $e  = $stmt->execute();

        if( $e ) {
            // If the answer is: in pending
            if( $result->getStatus() == 1 ) {
                $query  = 'UPDATE ' . $this->table( 'surveys' );
                $query .= ' SET budget = budget + ?, budget_bonus = budget_bonus + ? WHERE id = ?';
        
                $cwb    = $result->getCommissionWithoutBonus();
                $cb     = $result->getCommissionBonus();
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ddi', $cwb, $cb, $s_id );
                $stmt->execute();

            // If the answer is: waiting approval
            } else if( $result->getStatus() == 2 ) {
                $query  = 'UPDATE ' . $this->table( 'surveys' );
                $query .= ' SET ent_done = ent_done - 1, budget = budget + ?, budget_bonus = budget_bonus + ? WHERE id = ?';
        
                $cwb    = $result->getCommissionWithoutBonus();
                $cb     = $result->getCommissionBonus();
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ddi', $cwb, $cb, $s_id );
                $stmt->execute();
    
            // If the answer is: finished
            } else if( $result->getStatus() == 3 ) {
                $query  = 'UPDATE ' . $this->table( 'surveys' );
                $query .= ' SET ent_done = ent_done - 1 WHERE id = ?';
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'i', $s_id );
                $stmt->execute();
            }

            $stmt->close();

            actions()->do_action( 'after:delete-response', $this->user_obj, $result, $data );
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_question( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data           = filters()->do_filter( 'add-question-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || !isset( $data['info'] ) || !isset( $data['type'] ) || !( ( $survey_types = survey_types() )->setType( $data['type'] ) ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-question', true, $this->user, $survey, $data ) )
            return ;

        $survey_types->checkData( $data, 'add' );

        if( isset( $data['sposition'] ) && ( preg_match( '/(\d+)\,(\d+)/', $data['sposition'], $pos ) ) && count( $pos ) == 3 && ( $pos[1] == 0 || ( $step = steps( $pos[1] ) )->getObject() && $step->getSurveyId() == $survey->getId() ) ) {
            $step_id    = $pos[1];
            $position   = $pos[2];
        } else {
            $step_id    = $question->getStepId();
            $position   = $question->getPosition();
        }

        $required   = isset( $data['required'] ) ?: 0;
        $setting    = $survey_types->setting();
        $s_id       = $survey->getId();

        $query  = 'INSERT INTO ' . $this->table( 'questions' );
        $query .= ' (survey, user, type, title, info, setting, position, step, required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iissssiii', $s_id, $this->user, $data['type'], $data['name'], $data['info'], $setting, $position, $step_id, $required );
        $e  = $stmt->execute();
        $q  = $stmt->insert_id;

        if( $e ) {
            if( $position != 0 ) {
                $stmt = $this->db->stmt_init();
                $stmt->prepare( 'CALL reorder_questions(?, ?, ?, ?)' );
                $stmt->bind_param( 'iiii', $s_id, $step_id, $position, $q );
                $stmt->execute();
            }

            $stmt->close();

            $survey_types->afterUpdate( $q, $survey, $data, 'add' );
            
            actions()->do_action( 'after:add-question', $this->user_obj, $survey, $q, $data );
            return $q;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_question( object $question, object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-question-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || !isset( $data['info'] ) || !( ( $survey_types = survey_types() )->setType( $question->getType() ) ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-question', true, $this->user, $question, $data ) )
            return ;

        $survey_types->checkData( $data, 'edit' );

        if( isset( $data['sposition'] ) && ( preg_match( '/(\d+)\,(\d+)/', $data['sposition'], $pos ) ) && count( $pos ) == 3 && ( $pos[1] == 0 || ( $step = steps( $pos[1] ) )->getObject() && $step->getSurveyId() == $question->getSurveyId() ) ) {
            $step_id    = $pos[1];
            $position   = $pos[2];
        } else {
            $step_id    = $question->getStepId();
            $position   = $question->getPosition();
        }

        $required   = isset( $data['required'] ) ?: 0;
        $setting    = $survey_types->setting();
        $q_id       = $question->getId();
        $s_id       = $question->getSurveyId();

        $query  = 'UPDATE ' . $this->table( 'questions' );
        $query .= ' SET title = ?, info = ?, setting = ?, position = ?, step = ?, required = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sssiiii', $data['name'], $data['info'], $setting, $position, $step_id, $required, $q_id );
        $e  = $stmt->execute();

        if( $e ) {
            if( $position != $question->getPosition() ) {
                $stmt = $this->db->stmt_init();
                $stmt->prepare( 'CALL reorder_questions(?, ?, ?, ?)' );
                $stmt->bind_param( 'iiii', $s_id, $step_id, $position, $q_id );
                $stmt->execute();
            }

            $stmt->close();
            
            $survey_types->afterUpdate( $question, $survey, $data, 'edit' );

            actions()->do_action( 'after:edit-question', $this->user_obj, $question, $data );
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function trash_question( object $question ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-trash-question', true, $this->user, $question ) )
        return ;
        
        $q_id   = $question->getId();

        $query  = 'UPDATE ' . $this->table( 'questions' );
        $query .= ' SET visible = 0 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $q_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {            
            actions()->do_action( 'after:trash-question', $this->user_obj, $question );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_question( object $question ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-question', true, $this->user, $question ) )
        return ;

        $q_id   = $question->getId();
        $s_id   = $question->getSurveyId();

        $query  = 'DELETE FROM ' . $this->table( 'questions' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $q_id );
        $e  = $stmt->execute();

        if( $e ) {
            if( $question->isVisible() ) {
                $query  = 'UPDATE ' . $this->table( 'surveys' );
                $query .= ' SET questions = questions - 1 WHERE id = ?';
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'i', $s_id );
                $stmt->execute();
            }

            $stmt->close();

            actions()->do_action( 'after:deleted-question', $this->user_obj, $question );
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function restore_question( object $question, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( empty( $data['sposition'] ) || !( ( preg_match( '/(\d+)\,(\d+)/', $data['sposition'], $pos ) ) && count( $pos ) == 3 && ( $pos[1] == 0 || ( $step = steps( $pos[1] ) )->getObject() && $step->getSurveyId() == $question->getSurveyId() ) ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-restore-question', true, $this->user, $question, $data ) )
            return ;

        $s_id       = $question->getSurveyId();
        $q_id       = $question->getId();
        $step_id    = $pos[1];
        $position   = $pos[2];

        $query  = 'UPDATE ' . $this->table( 'questions' );
        $query .= ' SET position = ?, step = ?, visible = 2 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $position, $step_id, $q_id );
        $e  = $stmt->execute();

        if( $e ) {
            if( $position != 0 ) {
                $stmt = $this->db->stmt_init();
                $stmt->prepare( 'CALL reorder_questions(?, ?, ?, ?)' );
                $stmt->bind_param( 'iiii', $s_id, $step_id, $position, $q_id );
                $stmt->execute();
            }

            $stmt->close();
            
            actions()->do_action( 'after:restored-question', $this->user_obj, $question );
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_option( int $type, int $type_id, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-option-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-option', true, $this->user, $type, $type_id, $data ) )
        return ;

        $query  = 'INSERT INTO ' . $this->table( 'q_options' );
        $query .= ' (type, type_id, points, title, setting, position) VALUES (?, ?, ?, ?, ?, ?)';

        $position = $data['position'] ?? 1;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiissi', $type, $type_id, $data['points'], $data['title'], $data['setting'], $position );
        $e  = $stmt->execute();
        $qo = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-question-option', $this->user_obj, $qo, $type, $type_id, $data );
            return $qo;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_option( int $option, int $type, int $type_id, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-option-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-option', true, $this->user, $type, $type_id, $data ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'q_options' );
        $query .= ' SET points = ?, title = ?, setting = ?, position = IF(? IS NULL, position, ?) WHERE id = ? AND type = ? AND type_id = ?';

        $position = $data['position'] ?? NULL;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'issiiiii', $data['points'], $data['title'], $data['setting'], $position, $position, $option, $type, $type_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-question-option', $this->user_obj, $option, $type, $type_id, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_option( int $option, int $type, int $type_id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-option', true, $this->user, $option, $type, $type_id ) )
        return ;

        $query  = 'DELETE FROM ' . $this->table( 'q_options' );
        $query .= ' WHERE id = ? AND type = ? AND type_id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $option, $type, $type_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-option', $option, $type, $type_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_media( int $type, int $type_id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-media', true, $this->user, $type, $type_id ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'media' );
        $query .= ' SET deleted = 1 WHERE type = ? AND type_id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $type, $type_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-media', $this->user_obj, $type, $type_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_media_id( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-media-id', true, $this->user, $id ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'media' );
        $query .= ' SET deleted = 1 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-media-id', $this->user_obj, $id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_condition( int $question, string $value, int $points ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-add-condition', true, $this->user, $question, $value, $points ) )
        return ;

        $query  = 'INSERT INTO ' . $this->table( 'q_cond' );
        $query .= ' (question, value, points) VALUES (?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'isi', $question, $value, $points );
        $e  = $stmt->execute();
        $qc = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-condition', $this->user_obj, $qc, $question, $value, $points );
            return $qc;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_condition( int $id, int $question, string $value, int $points ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-edit-condition', true, $this->user, $id, $question, $value, $points ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'q_cond' );
        $query .= ' SET value = ?, points = ? WHERE id = ? AND question = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'siii', $value, $points, $id, $question );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-condition', $this->user_obj, $id, $question, $value, $points );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_condition( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-condition', true, $this->user, $id ) )
        return ;

        $query  = 'DELETE FROM ' . $this->table( 'q_cond' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-condition', $this->user_obj, $id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_step_condition( int $survey, int $step, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-step-condition-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-step-condition', true, $this->user, $survey, $step, $data ) )
        return ;

        $query  = 'INSERT INTO ' . $this->table( 'step_cond' );
        $query .= ' (survey, step, action, action_id, points, emsg) VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiiiis', $survey, $step, $data['action'], $data['action_id'], $data['points'], $data['message'] );
        $e  = $stmt->execute();
        $qo = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-step-condition', $this->user_obj, $qo, $survey, $step, $data );
            return $qo;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_step_condition( int $condition, int $survey, int $step, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-step-condition-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-step-condition', true, $this->user, $condition, $survey, $step, $data ) )
        return ;

        $query  = 'UPDATE ' . $this->table( 'step_cond' );
        $query .= ' SET action = ?, action_id = ?, points = ?, emsg = ? WHERE id = ? AND survey = ? AND step = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiisiii', $data['action'], $data['action_id'], $data['points'], $data['message'], $condition, $survey, $step );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-step-condition', $this->user_obj, $condition, $survey, $step, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_step_condition( int $condition, int $survey, int $step ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-delete-step-condition', true, $this->user, $condition, $survey, $step ) )
        return ;

        $query  = 'DELETE FROM ' . $this->table( 'step_cond' );
        $query .= ' WHERE id = ? AND survey = ? AND step = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $condition, $survey, $step );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:delete-step-condition', $this->user_obj, $condition, $survey, $step );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_collector( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );

        $data           = filters()->do_filter( 'add-collector-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || !isset( $data['type'] ) || !isset( $data['price'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-collector', true, $this->user, $survey, $data ) )
            return ;

        if( $data['type'] == 0 ) {
            $cpa    = 0;
        } else if( $data['type'] == 1 ) {
            $cpa    = (double) $data['price'];
            if( !me()->isAdmin() && ( $min = (double) get_option( 'min_cpa' ) ) > $cpa ) {
                throw new \Exception( sprintf( t( 'The minimum price per completed survey is: %s' ), $min ) );
            }
        } else throw new \Exception( t( 'Unexpected' ) );

        $s_id       = $survey->getId();
        $lpoints    = me()->isAdmin() && !empty( $data['lpoints'] ) ? (int) $data['lpoints'] : 0;

        $query  = 'INSERT INTO ' . $this->table( 'collectors' );
        $query .= ' (survey, user, name, slug, type, cpa, lpoints, visible) VALUES (?, ?, ?, generate_slug(), ?, ?, ?, 1)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iisidd', $s_id, $this->user, $data['name'], $data['type'], $cpa, $lpoints );
        $e  = $stmt->execute();
        $c  = $stmt->insert_id;
        $stmt->close();

        if( $e ) {

            if( $data['type'] == 1 ) {
                $actions        = new \user\collector_actions( $c );
                $audience       = $data['audience'] ?? [];
                $u_countries    = $audience['countries'] ?? NULL;
                $u_gender       = $audience['gender'] ?? NULL;
                $u_ages         = $audience['age'] ?? NULL;
    
                /** COUNTRIES */
                if( !empty( $audience['country'] ) || !$u_countries ) {
                    $actions        ->addOptions( 1, [ 0 ] );
                } else {
                    // get all countries
                    $countries      = new \query\countries;
                    $Allcountries   = $countries
                                    ->select( [ 'id' ] )
                                    ->selectKey( 'id' )
                                    ->fetch( -1 );
                    $new_countries  = array_intersect_key( $Allcountries, $audience['countries'] );
                    if( empty( $new_countries ) )
                    $new_countries  = [ 0 ];
                    else 
                    $new_countries  = array_keys( $new_countries );
                    $actions        ->addOptions( 1, $new_countries );
                }
    
                /** GENDER */
                $new_gender = $u_gender && in_array( $u_gender, [ 1, 2 ] ) ? [ $u_gender ] : [ 0 ];
                $actions    ->addOptions( 2, $new_gender );
    
                /** AGE RANGES */
                if( !empty( $audience['any_age'] ) || !$u_ages ) {
                    $actions        ->addOptions( 3, [ 0 ] );
                } else {
                    $new_ranges     = array_intersect( $u_ages, [ 1, 2, 3, 4, 5, 6 ] );
                    if( empty( $new_ranges ) )
                    $new_ranges     = [ 0 ];
                    $actions    ->addOptions( 3, $new_ranges );
                }
            }

            actions()->do_action( 'after:add-collector', $this->user_obj, $survey, $c, $data );

            return $c;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_collector( object $collector, int $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'edit-collector-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || !isset( $data['type'] ) || !isset( $data['price'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-collector', true, $this->user, $collector, $survey, $data ) )
            return ;


        if( $data['type'] == 0 )
            $cpa    = 0;
        else if( $data['type'] == 1 ) {

            $cpa    = (double) $data['price'];
            if( !me()->isAdmin() && ( $min = (double) get_option( 'min_cpa' ) ) > $cpa )
            throw new \Exception( sprintf( t( 'The minimum price per completed survey is: %s' ), $min ) );
            
            $actions        = $collector->actions();
            $actions        ->removeAllOptions();
            $audience       = $data['audience'] ?? [];
            $u_countries    = $audience['countries'] ?? NULL;
            $u_gender       = $audience['gender'] ?? NULL;
            $u_ages         = $audience['age'] ?? NULL;

            /** COUNTRIES */
            if( !empty( $audience['country'] ) || !$u_countries ) {
                $actions        ->addOptions( 1, [ 0 ] );
            } else {
                // get all countries
                $countries      = new \query\countries;
                $Allcountries   = $countries
                                ->select( [ 'id' ] )
                                ->selectKey( 'id' )
                                ->fetch( -1 );
                $new_countries  = array_intersect_key( $Allcountries, $audience['countries'] );
                if( empty( $new_countries ) )
                $new_countries  = [ 0 ];
                else 
                $new_countries  = array_keys( $new_countries );
                $actions        ->addOptions( 1, $new_countries );
            }

            /** GENDER */
            $new_gender = $u_gender && in_array( $u_gender, [ 1, 2 ] ) ? [ $u_gender ] : [ 0 ];
            $actions    ->addOptions( 2, $new_gender );

            /** AGE RANGES */
            if( !empty( $audience['any_age'] ) || !$u_ages ) {
                $actions        ->addOptions( 3, [ 0 ] );
            } else {
                $new_ranges     = array_intersect( $u_ages, [ 1, 2, 3, 4, 5, 6 ] );
                if( empty( $new_ranges ) )
                $new_ranges     = [ 0 ];
                $actions    ->addOptions( 3, $new_ranges );
            }

        } else throw new \Exception( t( 'Unexpected' ) );

        $c_id       = $collector->getId();
        $lpoints    = me()->isAdmin() && !empty( $data['lpoints'] ) ? (int) $data['lpoints'] : 0;
        $setting    = [];

        if( !empty( $data['encrypt'] ) && !empty( $data['enckey'] ) ) {
            $setting['enckey'] = $data['enckey'];

            if( !empty( $data['allow_empty'] ) )
            $setting['allowe'] = true;
        }
        
        if( !empty( $data['use_password'] ) && !empty( $data['password'] ) )
        $setting['password'] = $data['password'];

        if( !empty( $setting ) )
            $setting = cms_json_encode( $setting );
        else
            unset( $setting );

        $query  = 'UPDATE ' . $this->table( 'collectors' );
        $query .= ' SET name = ?, type = ?, setting = ?, cpa = ?, lpoints = ? WHERE id = ? AND survey = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sisdiii', $data['name'], $data['type'], $setting, $cpa, $lpoints, $c_id, $survey );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-collector', $this->user_obj, $c_id, $survey, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_collector( object $collector ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( $collector->getSurvey()->getUserId() != $this->user )
            throw new \Exception( t( 'Unexpected' ) );
        else if( !filters()->do_filter( 'custom-error-delete-collector', true, $this->user, $collector ) )
            return ;
 
        $query = 'DELETE FROM ';
        $query .= $this->table( 'collectors' );
        $query .= ' WHERE id = ?';

        $collector_id = $collector->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $collector_id );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            actions()->do_action( 'after:delete-collector', $this->user_obj, $collector );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function update_survey_budget( object $survey, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        else if( $survey->getStatus() < 0 )
        throw new \Exception( t( 'This survey is pending deletion' ) );
        
        $data   = filters()->do_filter( 'update-survey-budget-form-sanitize-data', $data );

        if( !isset( $data['amount'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-update-survey-budget', true, $this->user, $survey, $data ) )
            return ;

        $survey = $survey->getId();
        $user   = $this->user;

        $query  = 'SELECT set_survey_budget(?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iid', $survey, $user, $data['amount'] );
        $e = $stmt->execute();
        $stmt->bind_result( $r );
        $stmt->fetch();
        $stmt->close();

        if( $e && $r ) {
            actions()->do_action( 'after:update-survey-budget', $this->user_obj, $survey, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_category( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'add-category-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) || empty( $data['type'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-category', true, $this->user, $data ) )
            return ;

        $builder    = new \dev\builder\categories;
        $builder    ->setType( $data['type'] );

        try {
            $builder->checkType();
            $builder->checkData( $data );
        }
    
        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        if( !empty( $data['parent'] ) )
        $parent = $data['parent'];

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'categories' );
        $query .= ' (type, parent, user, name, description, slug, meta_title, meta_keywords, meta_desc, lang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'siisssssss', $data['type'], $parent, $this->user, $data['name'], $data['desc'], $data['slug'], $data['meta']['title'], $data['meta']['keys'], $data['meta']['mdesc'], $data['lang'] );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            $builder    ->setObject( $id )
                        ->saveData();
            actions()   ->do_action( 'after:add-category', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_category( object $category, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'edit-category-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-category', true, $this->user, $category, $data ) )
            return ;

        $builder    = new \dev\builder\categories;
        $builder    ->setType( $category->getType() )
                    ->setObject( $category );

        try {
            $builder->checkType();
            $builder->checkData( $data );
        }
    
        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        $query  = 'UPDATE ' . $this->table( 'categories' );
        $query .= ' SET parent = ?, user = ?, name = ?, description = ?, slug = ?, meta_title = ?, meta_keywords = ?, meta_desc = ?, lang = ? WHERE id = ?';

        $id = $category->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iisssssssi', $data['parent'], $this->user, $data['name'], $data['desc'], $data['slug'], $data['meta']['title'], $data['meta']['keys'], $data['meta']['mdesc'], $data['lang'], $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            $builder    ->saveData();
            actions()   ->do_action( 'after:edit-category', $this->user_obj, $category, $data );    
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_page( array $blocks, string $postType, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isAdmin() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'add-page-form-sanitize-data', $data );
        $data['title']  = isset( $data['title'] ) ? trim( $data['title'] ) : NULL;
        $data['slug']   = isset( $data['slug'] ) ? trim( $data['slug'] ) : NULL;

        if( empty( $data['title'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-page', true, $this->user, $blocks, $data ) )
            return ;
            
        $build  = new \dev\builder\blocks;

        try {
            $build  ->setType( $postType );
        }

        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        // Fields & values
        $fields = filters()->do_filter( 'form:fields:add-page:' . $build->getType(), [
            'thumb' => [ 'type' => 'image', 'category' => 'post-thumb' ]
        ] );

        // Modify fields & values
        $build  ->dev()
                ->manageFields( $fields )
                ->manageValues();

        // Build the form
        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->changeInputName( 'page' );
        $form   ->build();
        $media  = $form->uploadFiles( $data );

        // Save blocks
        $content = $build->buildBlocks( $blocks );

        try {
            // Check the data
            $build->dev()->checkData( $data, $media );
        }
    
        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        if( isset( $media['page[thumb]'] ) )
        $page_thumbs = implode( ',', array_keys( $media['page[thumb]' ] ) );

        if( isset( $data['template'] ) ) {
            $templates  = $build->dev()->getTemplates();
            if( isset( $templates[$data['template']] ) )
            $template   = $data['template'];
        }

        if( !isset( $template ) )
            $template   = $build->dev()
                        ->getDefaultTemplate();

        $type = $build->getType();

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'pages' );
        $query .= ' (type, title, thumb, user, template, text, slug, meta_title, meta_keywords, meta_desc, lu_user, lang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sssissssssis', $type, $data['title'], $page_thumbs, $this->user, $template, $content, $data['slug'], $data['meta']['title'], $data['meta']['keys'], $data['meta']['mdesc'], $this->user, $data['lang'] );
        $e = $stmt->execute();
        $p = $stmt->insert_id;

        if( $e ) {
            // save categories
            $categories     = categories()
                            ->setType( $build->getType() )
                            ->select( [ 'id' ] );
            $all_categories = array_map( function( $v ) {
                return $v->id;
            }, $categories->fetch( -1 ) );

            $new_categories = $data['categories'] ?? [];
            $added          = array_intersect( $all_categories, $new_categories );

            if( !empty( $added ) ) {
                $query  = 'INSERT INTO ' . $this->table( 'category_pages' );
                $query .= ' (category, page) VALUES (?, ?)';
        
                $stmt   ->prepare( $query );

                foreach( $added as $cat ) {
                    $stmt   ->bind_param( 'ii', $cat, $p );
                    $e      = $stmt->execute();
                }
            }

            $stmt       ->close();
            $build      ->dev()
                        ->setObject( $p )
                        ->saveData();
            actions()   ->do_action( 'after:add-page', $this->user_obj, $p, $data ); 
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_page( object $page, array $blocks, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'edit-page-form-sanitize-data', $data );
        $data['title']  = isset( $data['title'] ) ? trim( $data['title'] ) : false;

        if( empty( $data['title'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-page', true, $this->user, $page, $blocks, $data ) )
            return ;

        $build  = new \dev\builder\blocks;
        if( !$build->setPage( $page ) )
        throw new \Exception( t( 'Unexpected' ) );

        $build->dev()->checkType();

        // Fields & values
        $fields = filters()->do_filter( 'form:fields:edit-page:' . $build->getType(), [
            'thumb' => [ 'type' => 'image', 'category' => 'post-thumb' ]
        ], $page );

        $values = filters()->do_filter( 'form:values:edit-page:' . $build->getType(), [
            'thumb'  => $page->getThumbnails(),
        ], $page, $data );

        // Modify fields & values
        $build  ->dev()
                ->manageFields( $fields );

        // Build the form
        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->changeInputName( 'page' );
        $form   ->setValues( $values );
        $form   ->build();
        $media  = $form->uploadFiles( $data );

        // Save blocks
        $content = $build->buildBlocks( $blocks );

        try {
            // Check the data
            $build->dev()->checkData( $data, $media );
        }
    
        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        if( isset( $media['page[thumb]'] ) )
        $page_thumbs = implode( ',', array_keys( $media['page[thumb]' ] ) );

        if( isset( $data['template'] ) ) {
            $templates  = $build->dev()->getTemplates();
            if( isset( $templates[$data['template']] ) )
            $template   = $data['template'];
        }

        if( !isset( $template ) )
            $template   = $build->dev()
                        ->getDefaultTemplate();

        $query  = 'UPDATE ' . $this->table( 'pages' );
        $query .= ' SET title = ?, thumb = ?, user = ?, template = ?, text = ?, slug = ?, meta_title = ?, meta_keywords = ?, meta_desc = ?, lu_user = ?, lang = ? WHERE id = ?';

        $id     = $page->getId();

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'ssissssssisi', $data['title'], $page_thumbs, $this->user, $template, $content, $data['slug'], $data['meta']['title'], $data['meta']['keys'], $data['meta']['mdesc'], $this->user, $data['lang'], $id );
        $e      = $stmt->execute();

        if( $e ) {
            // save/remove categories
            $categories     = $page->getCategories();
            $old_categories = array_map( function( $v ) {
                return $v->category;
            }, $categories->fetch( -1 ) );
            $new_categories = $data['categories'] ?? [];
            $added          = array_diff( $new_categories, $old_categories );
            $deleted        = array_diff( $old_categories, $new_categories );
            $page_id        = $page->getId();

            if( !empty( $added ) ) {
                $query  = 'INSERT INTO ' . $this->table( 'category_pages' );
                $query .= ' (category, page) VALUES (?, ?)';
        
                $stmt   ->prepare( $query );

                foreach( $added as $cat ) {
                    $stmt   ->bind_param( 'ii', $cat, $page_id );
                    $e      = $stmt->execute();
                }
            }

            if( !empty( $deleted ) ) {
                $query  = 'DELETE FROM ' . $this->table( 'category_pages' );
                $query .= ' WHERE category = ? AND page = ?';
        
                $stmt   ->prepare( $query );

                foreach( $deleted as $cat ) {
                    $stmt   ->bind_param( 'ii', $cat, $page_id );
                    $e      = $stmt->execute();
                }
            }

            $stmt       ->close();
            $build      ->dev()->saveData();
            actions()   ->do_action( 'after:edit-page', $page, $blocks, $data, $media );    
            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_voucher( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'add-voucher-form-sanitize-data', $data );
        $data['code']   = isset( $data['code'] ) ? trim( $data['code'] ) : false;

        if( empty( $data['code'] ) || !isset( $data['applying'] ) || !isset( $data['amount'] ) || !isset( $data['atype'] ) || !isset( $data['used_by'] ) || !isset( $data['user'] ) || !isset( $data['limit'] ) || !isset( $data['expiration'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-voucher', true, $this->user, $data ) )
            return ;

        $type   = $data['applying'] == 0 || $data['applying'] == 1 ? $data['applying'] : 0;
        $atype  = $data['atype'] == 0 || $data['atype'] == 1 ? $data['atype'] : 0;

        if( $type == 0 && $atype == 1 ) {
            throw new \Exception( t( 'Free vouchers can be saved as a fixed amount only' ) );  
        }

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'vouchers' );
        $query .= ' (code, type, amount, a_type, user, `limit`, status, expiration) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?))';

        $usedby = $data['used_by'] == 0 || $data['used_by'] == 1 ? $data['used_by'] : 0;
        $status = isset( $data['available'] ) ?: 0;
        $exp    = $data['expiration'];

        if( (int) $data['limit'] > 0 ) {
            $limit = (int) $data['limit'];
        }

        if( $usedby == 0 )
        unset( $data['user'] );

        if( isset( $data['never_exp'] ) ) {

        } else if ( !empty( $data['expiration'] ) )
            $exp = custom_time( $data['expiration'], -5 );
        else
            $exp = time();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sidiiiis', $data['code'], $type, $data['amount'], $atype, $data['user'], $limit, $status, $exp );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-voucher', $this->user_obj, $data );          
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_voucher( object $voucher, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'edit-voucher-form-sanitize-data', $data );
        $data['code']   = isset( $data['code'] ) ? trim( $data['code'] ) : false;

        if( empty( $data['code'] ) || !isset( $data['applying'] ) || !isset( $data['amount'] ) || !isset( $data['atype'] ) || !isset( $data['used_by'] ) || !isset( $data['user'] ) || !isset( $data['limit'] ) || !isset( $data['expiration'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-voucher', true, $this->user, $voucher, $data ) )
            return ;

        $type   = $data['applying'] == 0 || $data['applying'] == 1 ? $data['applying'] : 0;
        $atype  = $data['atype'] == 0 || $data['atype'] == 1 ? $data['atype'] : 0;

        if( $type == 0 && $atype == 1 )
        throw new \Exception( t( 'Free vouchers can be saved as a fixed amount only' ) );  

        $query  = 'UPDATE ' . $this->table( 'vouchers' );
        $query .= ' SET code = ?, type = ?, amount = ?, a_type = ?, user = ?, `limit` = ?, status = ?, expiration = FROM_UNIXTIME(?) WHERE id = ?';

        $id     = $voucher->getId();
        $usedby = $data['used_by'] == 0 || $data['used_by'] == 1 ? $data['used_by'] : 0;
        $limit  = (int) $data['limit'] > 0 ? $data['limit'] : NULL;
        $status = isset( $data['available'] ) ?: 0;

        if( $usedby == 0 )
        $data['user'] = NULL;

        if( !isset( $data['never_exp'] ) && isset( $data['expiration'] ) )
        $exp = custom_time( $data['expiration'], -5 );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sidiiiisi', $data['code'], $type, $data['amount'], $atype, $data['user'], $limit, $status, $exp, $id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-voucher', $this->user_obj, $voucher, $data ); 
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_plan( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'add-plan-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;
        $data['rbrand'] = isset( $data['rbrand'] );
        
        if( empty( $data['name'] ) || !isset( $data['surveys'] ) || !isset( $data['responses'] ) || !isset( $data['questions'] ) || !isset( $data['collectors'] ) || !isset( $data['team'] ) || !isset( $data['avb_space'] ) || !isset( $data['price'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-plan', true, $this->user, $data ) )
            return ;

        $visible = isset( $data['visible'] ) ? 2 : 1;

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'plans' );
        $query .= ' (name, sur, res_p_sur, que_p_sur, col, tm, r_brand, space, price, visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'siiiiiiidi', $data['name'], $data['surveys'], $data['responses'], $data['questions'], $data['collectors'], $data['team'], $data['rbrand'], $data['avb_space'], $data['price'], $visible );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-plan', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_plan( object $plan, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data           = filters()->do_filter( 'edit-plan-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;
        $data['rbrand'] = isset( $data['rbrand'] );

        if( empty( $data['name'] ) || !isset( $data['surveys'] ) || !isset( $data['responses'] ) || !isset( $data['questions'] ) || !isset( $data['collectors'] ) || !isset( $data['team'] ) || !isset( $data['avb_space'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-plan', true, $this->user, $plan, $data ) )
            return ;

        if( !$plan->getObject() ) {

            // Update the default plan
            return me()->website_options()->saveOption( 'default_plan', cms_json_encode( [
                'name'          => $data['name'],
                'surveys'       => (int) $data['surveys'],
                'responses'     => (int) $data['responses'],
                'questions'     => (int) $data['questions'],
                'collectors'    => (int) $data['collectors'],
                'tmembers'      => (int) $data['team'],
                'rbrand'        => $data['rbrand'],
                'space'         => (int) $data['avb_space']
            ] ) );

        } else {

            // Update a custom plan
            $planId     = $plan->getId();
            $visible    = isset( $data['visible'] ) ? 2 : 1;

            $query  = 'UPDATE ' . $this->table( 'plans' );
            $query .= ' SET name = ?, sur = ?, res_p_sur = ?, que_p_sur = ?, col = ?, tm = ?, r_brand = ?, space = ?, price = ?, visible = ? WHERE id = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'siiiiiiidii', $data['name'], $data['surveys'], $data['responses'], $data['questions'], $data['collectors'], $data['team'], $data['rbrand'], $data['avb_space'], $data['price'], $visible, $planId );
            $e = $stmt->execute();
            $stmt->close();
            
            if( $e ) {
                actions()->do_action( 'after:edit-plan', $this->user_obj, $plan, $data );     
                return true;
            }

        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_plan_offer( object $plan, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data   = filters()->do_filter( 'add-plan-offer-form-sanitize-data', $data );

        if( empty( $data['min_months'] ) || empty( $data['price'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-add-plan-offer', true, $this->user, $plan, $data ) )
            return ;

        $planId = $plan->getId();

        if( empty( $data['active'] ) ) {
            if( empty( $data['start'] ) ) {
                throw new \Exception( t( 'Please set the start date' ) );
            }

            $starts = $data['start'];
        } else $starts = date( 'Y-m-d H:i:s' );

        if( empty( $data['nexp'] ) ) {
            if( empty( $data['expire'] ) ) {
                throw new \Exception( t( 'Please set the expiration date' ) );
            }

            $expires = $data['expire'];
        }

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'plan_offers' );
        $query .= ' (plan, min_months, price, starts, expires) VALUES (?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iidss', $planId, $data['min_months'], $data['price'], $starts, $expires );
        $e = $stmt->execute();
        $o = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-plan-offer', $this->user_obj, $o, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_plan_offer( object $offer, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data   = filters()->do_filter( 'edit-plan-offer-form-sanitize-data', $data );

        if( empty( $data['min_months'] ) || empty( $data['price'] ) || empty( $data['plan'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-edit-plan-offer', true, $this->user, $offer, $data ) )
            return ;

        // check if plan exists
        $plan   = new \query\plans\plans( (int) $data['plan'] );
        if( !$plan->getObject() ) {
            throw new \Exception( t( 'Unexpected' ) );
        }

        if( empty( $data['nexp'] ) ) {
            if( empty( $data['expire'] ) ) {
                throw new \Exception( t( 'Please set the expiration date' ) );
            }

            $expires = $data['expire'];
        }

        $query  = 'UPDATE ' . $this->table( 'plan_offers' );
        $query .= ' SET plan = ?, min_months = ?, price = ?, starts = ?, expires = ? WHERE id = ?';

        $offerId= $offer->getId();
        $planId = $plan->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iidssi', $planId, $data['min_months'], $data['price'], $data['start'], $expires, $offerId );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:edit-plan-offer', $this->user_obj, $offer, $data ); 
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function website_referral_levels( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !me()->isOwner() )
        throw new \Exception( t( 'Something went wrong' ) );

        $data   = filters()->do_filter( 'edit-referral-levels-form-sanitize-data', $data );
        $bLevels= [];

        foreach( array_intersect_key( $data, [ 'reg' => 1, 'eachupgrade' => 1 ] ) as $k => $opts ) {
            $level  = 1;
            $prev   = NULL;
            array_pop( $opts );

            foreach( $opts as $l ) {
                if( !isset( $l['stars'] ) ) continue;
                if( $prev && $prev < $l['stars'] ) throw new \Exception( t( 'Nope, check the points' ) );
                $prev                   = $l['stars'];
                $bLevels[$k][$level]    = $prev;
                $level                  ++;
            }
        }

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( 'ref_system', cms_json_encode( $bLevels ) );

        if( $opt1 ) {
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function create_team( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data           = filters()->do_filter( 'create-team-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-create-team', true, $this->user, $data ) )
            return ;

        $query  = 'INSERT INTO ' . $this->table( 'teams' );
        $query .= ' (user, name) VALUES (?, ?)';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'is', $this->user, $data['name'] );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            me()        ->actions()->changeTeam( $id );
            actions()   ->do_action( 'after:create-team', $this->user_obj, $id, $data );
            return true;
        }

        throw new \Exception( t( 'Your already have a team' ) );
    }

    public function add_owner_subscription( array $data ) {
        $data   = filters()->do_filter( 'add-owner-subscription-form-sanitize-data', $data );

        if( empty( $data['plan'] ) || empty( $data['user'] ) ) {
            throw new \Exception( t( 'Something went wrong' ) );
        }

        if( ( $errors = filters()->do_filter( 'custom-error-add-owner-subscription', false, $data ) ) ) {
            throw new \Exception( $errors );
        }

        // check if plan exists
        $plan   = new \query\plans\plans( (int) $data['plan'] );
        if( !$plan->getObject() ) {
            throw new \Exception( t( 'Unexpected' ) );
        }

        // check if user exists
        $user   = new \query\users( (int) $data['user'] );
        if( !$user->getObject() ) {
            throw new \Exception( t( 'Unexpected' ) );
        }

        $userId     = $user->getId();
        $planId     = $plan->getId();
        if( isset( $data['custom_exp'] ) && !empty( $data['expiration_c'] ) )
            $expiration = custom_time( $data['expiration_c'], -5 );
        else
            $expiration = isset( $data['expiration'] ) && $data['expiration'] >= 1 && $data['expiration'] <= 24 ? strtotime( $data['expiration'] . ' months' ) : strtotime( '+1 month' );

        
        $query  = 'INSERT INTO ';
        $query .= $this->table( 'subscriptions' );
        $query .= ' (user, plan, expiration) VALUES (?, ?, FROM_UNIXTIME(?))';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iis', $userId, $planId, $expiration );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-owner-subscription', $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_owner_subscription( object $subscription, array $data ) {
        $data   = filters()->do_filter( 'edit-owner-subscription-form-sanitize-data', $data );

        if( empty( $data['plan'] ) ) {
            throw new \Exception( t( 'Something went wrong' ) );
        }

        if( filters()->do_filter( 'custom-error-edit-owner-subscription', false, $subscription, $data ) ) {
            throw new \Exception( $errors );
        }

        // check if plan exists
        $plan   = new \query\plans\plans( (int) $data['plan'] );
        if( !$plan->getObject() ) {
            throw new \Exception( t( 'Unexpected' ) );
        }

        $query  = 'UPDATE ' . $this->table( 'subscriptions' );
        $query .= ' SET plan = ?, expiration = FROM_UNIXTIME(?) WHERE id = ?';

        $subId      = $subscription->getId();
        $planId     = $plan->getId();
        $expiration = isset( $data['expiration'] ) ? custom_time( $data['expiration'], -5 ) : strtotime( '+1 month' );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'isi', $planId, $expiration, $subId );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {          
            actions()->do_action( 'after:edit-owner-subscription', $subscription, $data );  
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_country( array $data ) {
        $data   = filters()->do_filter( 'add-country-form-sanitize-data', $data );

        if( empty( $data['name'] ) || empty( $data['iso3166'] ) || empty( $data['language'] ) || empty( $data['hourf'] ) || empty( $data['datef'] ) || empty( $data['timezones'] ) || !isset( $data['fday'] ) || empty( $data['mformat'] ) || empty( $data['mseparator'] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        if( ( $errors = filters()->do_filter( 'custom-error-add-country', false, $data ) ) )
        throw new \Exception( $errors );

        $languages = getLanguages();

        if( !isset( $languages[$data['language']] ) || !in_array( $data['hourf'], [ '12', '24' ] ) || !in_array( $data['datef'], [ 'm/d/y', 'd/m/y', 'y/m/d' ] ) || !in_array( $data['fday'], [ '0', '1' ] ) || !in_array( $data['mformat'], [ '%s %a', '%a %s' ] ) || !in_array( $data['mseparator'], [ ' |,', ' |.', '.|,', ',|.' ] ) )
        throw new \Exception( t( 'Unexpected' ) );

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'countries' );
        $query .= ' (iso_3166, name, hour_format, date_format, timezone, firstday, language, mformat, mseparator) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ssississs', $data['iso3166'], $data['name'], $data['hourf'], $data['datef'], $data['timezones'], $data['fday'], $data['language'], $data['mformat'], $data['mseparator'] );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:add-country', $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_country( object $country, array $data ) {
        $data   = filters()->do_filter( 'edit-country-form-sanitize-data', $data );

        if( empty( $data['name'] ) || empty( $data['iso3166'] ) || empty( $data['language'] ) || empty( $data['hourf'] ) || empty( $data['datef'] ) || empty( $data['timezones'] ) || !isset( $data['fday'] ) || empty( $data['mformat'] ) || empty( $data['mseparator'] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        if( ( $errors = filters()->do_filter( 'custom-error-edit-country', false, $data ) ) )
        throw new \Exception( $errors );

        $languages = getLanguages();

        if( !isset( $languages[$data['language']] ) || !in_array( $data['hourf'], [ '12', '24' ] ) || !in_array( $data['datef'], [ 'm/d/y', 'd/m/y', 'y/m/d' ] ) || !in_array( $data['fday'], [ '0', '1' ] ) || !in_array( $data['mformat'], [ '%s %a', '%a %s' ] ) || !in_array( $data['mseparator'], [ ' |,', ' |.', '.|,', ',|.' ] ) )
        throw new \Exception( t( 'Unexpected' ) );

        $query  = 'UPDATE ' . $this->table( 'countries' );
        $query .= ' SET iso_3166 = ?, name = ?, hour_format = ?, date_format = ?, timezone = ?, firstday = ?, language = ?, mformat = ?, mseparator = ? WHERE id = ?';

        $countryId  = $country->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ssississsi', $data['iso3166'], $data['name'], $data['hourf'], $data['datef'], $data['timezones'], $data['fday'], $data['language'], $data['mformat'], $data['mseparator'], $countryId );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {          
            actions()->do_action( 'after:edit-country', $country, $data );  
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function identity_verification( array $data ) {
        $data   = filters()->do_filter( 'identity-verification-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-identity-verification', true, $this->user, $data ) )
            return ; 
            
        $docs   = get_option( 'kyc_settings' );
        $docs   = json_decode( $docs, true );
        $twos   = $docs['handhelp'] ?? false;

        // Only inputs that can deal with media
        $fields = [ 
            'doc_img' => [ 'type' => 'image', 'category' => 'kyc' ] 
        ];
        if( $twos )
        $fields['selfie']  = [ 'type' => 'image', 'label' => t( 'Selfie picture holding the document'), 'category' => 'kyc' ];

        $form   = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:identity-verification', $fields ) );
        $form   ->build();

        $rfiles = $form->getFileRequests();

        if( empty( $rfiles['data[doc_img]'] ) || ( empty( $rfiles['data[selfie]'] ) && $twos ) ) {
            throw new \Exception( t( 'Please upload the required photos' ) );
        }

        $media  = $form->uploadFiles( $data );

        $files          = [];
        $files['doc']   = key( $media['data[doc_img]'] );
        if( $twos )
        $files['self']  = key( $media['data[selfie]'] );

        $query  = 'INSERT INTO ' . $this->table( 'user_intents' );
        $query .= ' (user, type) VALUES (?, 1)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->user );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            $json_files = cms_json_encode( $files );

            if( isset( $media['data[doc_img]'] ) ) {
                $query  = 'UPDATE ' . $this->table( 'user_intents' );
                $query .= ' SET text = ? WHERE id = ?';
        
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'si', $json_files, $id );
                $e  = $stmt->execute();
                $stmt->close();
            }

            actions()->do_action( 'after:identity-verification', $id, $data, $media );

            return true;
        }

        throw new \Exception( t( 'The request is already sent' ) );
    }

    public function save_menu( array $data ) {
        $data   = filters()->do_filter( 'edit-owner-menu-form-sanitize-data', $data );

        if( empty( $data['menu'] ) || empty( $data['lang'] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        if( filters()->do_filter( 'custom-error-edit-owner-menu', false, $data ) )
        throw new \Exception( $errors );

        $menu   = $data['menu'];
        $lang   = $data['lang'];

        $site_options   = me()->website_options();
        $opt1           = $site_options->saveOption( $menu . ':' . $lang, cms_json_encode( [ 'links' => ( $data['links'] ?? [] ) ] ) );

        if( $opt1 ) {
            actions()->do_action( 'after:menu-saved', $this->user_obj, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function notif_subscribers( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'notify-subscribers-form-sanitize-data', $data );

        if( empty( $data['type'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-notify-subscribers', true, $this->user, $data ) )
            return ;

        $typeStr    = 'will_expire';
        if( $data['type'] == 'expired' )
        $typeStr    = 'expired';

        if( $typeStr == 'will_expire' ) {

            options()   ->save_option( 'subnotif_will_expire', time() );
            $interval   = (int) ( $data['interval'] ?? 1 );
            $interval   = $interval <= 7 && $interval >= 1 ? $interval : 1;
            $subs       = subscriptions()
                        ->setExpiration( $interval )
                        ->setNotExpired();
            $sent = $err= 0;

            foreach( $subs->fetch( -1 ) as $sub ) {
                $subs   ->setObject( $sub );
                $user   = $subs->getUser();
                if( !$user->getObject() ) 
                continue;

                // Do not notify auto-renew subscriptions
                if( !( $subs->getAutorenew() && $user->getBalance() >= $subs->getPlanPrice() ) ) {
                    $mail   = $user->mail( 'subscription_expires_soon' )
                            ->useDefaultShortcodes()
                            ->setShortcodes( [
                                '%NAME%'        => $user->getDisplayName(),
                                '%PLAN_NAME%'   => $subs->getPlanName(),
                                '%DATE%'        => $user->custom_time( $subs->getExpiration(), 2 )
                            ] );

                    try {
                        $mail->send();
                        $sent++;
                    }
                    
                    catch( \Exception $e ) { 
                        $err++;
                    }
                }
            }

            if( $err )
            throw new \Exception( sprintf( t( '%s errors. %s emails sent' ), $err, $sent ) );

        } else {

            options()   ->save_option( 'subnotif_expired', time() );
            $interval   = (int) ( $data['interval'] ?? 1 );
            $interval   = $interval <= 7 && $interval >= 1 ? $interval : 1;
            $subs       = subscriptions()
                        ->setExpiration( $interval );
            $sent = $err= 0;

            foreach( $subs->fetch( -1 ) as $sub ) {
                $subs   ->setObject( $sub );
                $user   = $subs->getUser();
                if( !$user->getObject() ) 
                continue;

                $mail   = $user->mail( 'subscription_expired' )
                        ->useDefaultShortcodes()
                        ->setShortcodes( [
                            '%NAME%'        => $user->getDisplayName(),
                            '%PLAN_NAME%'   => $subs->getPlanName(),
                            '%DATE%'        => $user->custom_time( $subs->getExpiration(), 2 )
                        ] );

                try {
                    $mail->send();
                    $sent++;
                }
                
                catch( \Exception $e ) { 
                    $err++;
                }
            }

            if( $err )
            throw new \Exception( sprintf( t( '%s errors. %s emails sent' ), $err, $sent ) );

        }

        return true;
    }

    public function remove_expired_subscriptions( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'remove-expired-subscriptions-form-sanitize-data', $data );

        if( !isset( $data['days'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filters()->do_filter( 'custom-error-remove-expired-subscriptions', true, $this->user, $data ) )
            return ;

        $subs   = subscriptions()
                ->setExpiration( -abs( (int) $data['days'] ) );
        $query  = 'DELETE FROM ' . $this->table( 'subscriptions' ) . ' WHERE id = ?';
        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );

        foreach( $subs->fetch( -1 ) as $sub ) {
            $subs   ->setObject( $sub );
            $user   = $subs->getUser();

            // Don't delete auto-renew subscriptions if there is an available balance
            if( $user->getObject() )
            if( $subs->getAutorenew() && $user ) {
                try {
                    $user   ->manage()
                            ->extend_subscription( $subs );
                }
                catch( \Exception $e ) {}
                continue;
            }

            $stmt       ->bind_param( 'i', $sub->id );
            $stmt       ->execute();
            actions()   ->do_action( 'after:subscription-deleted', $this->user_obj, $sub );
        }

        $stmt   ->close();

        return true;
    }

    public function autorenew_subscriptions( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'renew-subscriptions-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-renew-subscriptions', true, $this->user, $data ) )
            return ;

        $subs   = subscriptions()
                ->setExpiration( 24, 'HOUR' )
                ->setAutorenew();

        foreach( $subs->fetch( -1 ) as $sub ) {
            $subs   ->setObject( $sub );
            $user   = $subs->getUser();

            // Don't delete auto-renew subscriptions if there is an available balance
            if( $user->getObject() )
            if( $subs->getAutorenew() && $user ) {
                try {
                    $user   ->manage()
                            ->extend_subscription( $subs );
                }
                catch( \Exception $e ) {}
            }
        }

        return true;
    }

}
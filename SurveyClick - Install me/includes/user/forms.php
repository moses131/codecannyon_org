<?php

namespace user;

class forms {

    private $user;
    private $user_obj;
    private $last_form;

    function __construct( $user ) {
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

    public function getForm() {
        return $this->last_form;
    }

    public function modifyForm( callable $fct ) {
        $this->last_form = $fct;
        return $this;
    }

    private function modifyLastForm( $form ) {
        if( is_callable( $this->last_form ) )
        call_user_func( $this->last_form, $form );
    }

    public function edit_profile( array $attributes = [] ) {
        if( ( $gender = $this->user_obj->getGender() ) )
            $gender_field   = [ 'type' => 'select', 'label' => t( 'Gender' ), 'placeholder' => t( 'Gender' ), 'options' => [ 'M' => t( 'Male' ), 'F' => t( 'Female' ) ], 'value' => $gender, 'disabled' => 'disabled' ];
        else
            $gender_field   = [ 'type' => 'select', 'label' => t( 'Gender' ), 'placeholder' => t( 'Gender' ), 'description' => t( "You won't be able to modify this information" ), 'options' => [ 'M' => t( 'Male' ), 'F' => t( 'Female' ) ] ];

        if( ( $birthday = $this->user_obj->getBirthday() ) && ( $birthday = explode( '-', $birthday ) ) ) {
            $years              = range( ( date( 'Y' ) - 5 ), ( date( 'Y' ) - 90 ) );
            $birthday_fields    = [ 'type' => 'inline-group', 'label' => t( 'Birthday' ), 'grouped' => false, 'fields' => [
                'year'  => [ 'type' => 'select', 'options' => array_combine( $years, $years ), 'value' => (int) $birthday[0], 'disabled' => 'disabled' ],
                'month' => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 12 ), range( 01, 12 ) ) ), 'value' => (int) $birthday[1], 'disabled' => 'disabled' ],
                'day'   => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 31 ), range( 01, 31 ) ) ), 'value' => (int) $birthday[2], 'disabled' => 'disabled' ]
            ] ];
        } else {
            $birthday_fields    = [ 'type' => 'inline-group', 'label' => t( 'Birthday' ), 'description' => t( "You won't be able to modify this information" ), 'grouped' => false, 'fields' => [
                'year'  => [ 'type' => 'select', 'placeholder' => t( 'Year' ), 'options' => range( ( date( 'Y' ) - 5 ), ( date( 'Y' ) - 90 ) ) ],
                'month' => [ 'type' => 'select', 'placeholder' => t( 'Month' ), 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 12 ), range( 01, 12 ) ) ) ],
                'day'   => [ 'type' => 'select', 'placeholder' => t( 'Day' ), 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 31 ), range( 01, 31 ) ) ) ]
            ] ];
        }

        $limit  = filters()->do_filter( 'change-country-limit', 30 );
        $lcc    = $this->user_obj->getLastCountryChanged();

        $cofld  = [ 'type' => 'select', 'label' => t( 'Country' ), 'description' => t( "You can change your country every 30 days" ),  'options' => array_map( function( $item ) {
            return esc_html( t( $item->name ) );
        }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ), 'value' => $this->user_obj->getCountryId(), 'required' => 'required' ];

        if( $lcc && strtotime( '+' . $limit . ' days', strtotime( $lcc ) ) > time() )
        $cofld['disabled'] = 'disabled';

        $form = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:edit-profile', [
            'username'  => [ 'type' => 'text', 'label' => t( 'Username' ), 'required' => 'required' ],
            'full_name' => [ 'type' => 'text', 'label' => t( 'Full name' ), 'required' => 'required' ],
            'birthday'  => [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [ $birthday_fields, 'gender' => $gender_field ] ],
            'avatar'    => [ 'type' => 'image', 'label' => t( 'Avatar' ), 'category' => 'user-avatar', 'identifierId' => $this->user ],
            'email'     => [ 'type' => 'text', 'label' => t( 'Email address' ), 'disabled' => 'disabled' ],
            'country'   => $cofld,
            'address'   => [ 'type' => 'textarea', 'label' => t( 'Address' ) ] ], $this->user_obj ) +
            [ [ 'type' => 'button', 'label' => t( 'Save' ) ] ] );
        
        $form->setValues( filters()->do_filter( 'form:values:edit-profile', [
            'username'  => $this->user_obj->getName(),
            'full_name' => $this->user_obj->getFullName(),
            'email'     => $this->user_obj->getEmail(),
            'address'   => $this->user_obj->getAddress()
        ], $this->user_obj ) );

        if( $this->user_obj->getAvatar() )
        $form->setValue( 'avatar', [ $this->user_obj->getAvatar() => $this->user_obj->getAvatarURL() ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'edit-profile' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_profile" class="form edit_profile_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_profile_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function become_surveyor( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:become-surveyor', [
            'surveyor'  => [ 'type' => 'checkbox', 'title' => t( 'Become a surveyor and start creating surveys' ), 'required' => 'required' ],
            [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] ) );
        
        $form->setValues( filters()->do_filter( 'form:values:become-surveyor', [], $this->user_obj ) );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'become-surveyor' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="become_surveyor" class="form become_surveyor_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'become_surveyor_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_preferences( array $attributes = [] ) {
        $tmz    = $this->user_obj->getCountry();
        if( !$tmz->getObject() )
        return '<div class="msg info mb0">' . t( 'Please update your profile first' ) . ' </div>';

        $form   = new \markup\front_end\form_fields( [
            'language'      => [ 'type' => 'select', 'label' => t( 'Language' ), 'options' => array_map( function( $lng ) {
                return esc_html( $lng['name'] );
            }, getLanguages() ), 'value' => $this->user_obj->getLanguageId() ],
            'timezone'      => [ 'type' => 'select', 'label' => t( 'Timezone' ), 'options' => $tmz->getTimezones(), 'value' => $this->user_obj->getTz() ],
            'firstday'      => [ 'type' => 'select', 'label' => t( 'First day of the week' ), 'options' => [ 0 => t( 'Monday' ), 1 => t( 'Sunday' ) ], 'value' => $this->user_obj->getFirstDayW(), ],
            'hour_format'   => [ 'type' => 'select', 'label' => t( 'Hour format' ), 'options' => [ 12 => t( '12-hour clock' ), 24 => t( '24-hour clock' ) ], 'value' => $this->user_obj->getHFormat(), ],
            'date_format'   => [ 'type' => 'select', 'label' => t( 'Date format' ), 'options' => filters()->do_filter( 'date-formats', [ 'm/d/y' => 'm/d/y', 'd/m/y' => 'd/m/y', 'y/m/d' => 'y/m/d' ] ), 'value' => $this->user_obj->getDFormat() ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'preferences' ] );

        $markup = '<form id="login_form" class="form edit_preferences_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_preferences_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function change_password( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'cpassword' => [ 'type' => 'text', 'input_type' => 'password', 'label' => t( 'Current password' ), 'required' => 'required' ],
            'password'  => [ 'type' => 'text', 'input_type' => 'password', 'label' => t( 'New password' ), 'required' => 'required' ],
            'password2' => [ 'type' => 'text', 'input_type' => 'password', 'label' => t( 'New password again' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'change-password' ] );

        $markup = '<form id="change_password" class="form change_password_form"' . \util\attributes::add_attributes( filters()->do_filter( 'change_password_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function payout_options( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'pp_address'    => [ 'type' => 'text', 'label' => t( 'Paypal address' ), 'value' => $this->user_obj->getOption( 'pp_address' )->getValue(), 'required' => 'required' ],
            'stripe_address'=> [ 'type' => 'text', 'label' => t( 'Stripe address' ), 'value' => $this->user_obj->getOption( 'stripe_address' )->getValue(), 'required' => 'required' ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'payout-options' ] );

        $markup = '<form id="payout_options" class="form payout_options_form"' . \util\attributes::add_attributes( filters()->do_filter( 'payout_options_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function privacy_options( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'dname'     => [ 'type' => 'radio', 'label' => t( 'How to display my name' ), 'options' => [ 'username' => esc_html( $this->user_obj->getName() ), 'fullname' => esc_html( $this->user_obj->getFullName() ) ], 'required' => 'required' ],
            'transfer'  => [ 'type' => 'checkbox', 'label' => t( 'Transfer surveys' ), 'title' => t( 'Allow others to transfer surveys to you' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'dname'     => ( $this->user_obj->getDisplayFullName() ? 'fullname' : 'username' ),
            'transfer'  => $this->user_obj->getAllowTransfer()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'privacy-options' ] );

        $markup = '<form id="privacy_options" class="form privacy_options_form"' . \util\attributes::add_attributes( filters()->do_filter( 'privacy_options_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function security_options( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'twosv'     => [ 'type' => 'checkbox', 'label' => t( '2-Step verification' ), 'title' => t( 'Activate 2-Step Verification' ), 'description' => t( "Every time I log in I want to receive a security code which I'll use to complete my login" ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'twosv'     => ( $this->user_obj->get2StepVerification() ?: false ) 
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'security-options' ] );

        $markup = '<form id="security_options" class="form security_options_form"' . \util\attributes::add_attributes( filters()->do_filter( 'security_options_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function deposit( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'option'      => [ 'type' => 'select', 'position' => 1, 'label' => t( 'Method' ), 'options' => array_map( function ( $item ) {
                return esc_html( $item['name'] );
            }, filters()->do_filter( 'deposit-methods', [] ) ), 'value' => 'paypal', 'required' => 'required' ],
            'amount'      => [ 'type' => 'number', 'position' => 2, 'label' => t( 'Amount' ), 'value' => ( $min = cms_money( get_option( 'deposit_min' ) ) ) ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Deposit' ) ]
        ] );

        $vouchers = $this->user_obj->getVouchers()->setType( 1 );
        if( $vouchers->count() ) {
            $form->addFields( [ 'voucher'  => [ 'type' => 'radio', 'position' => 2.1, 'label' => t( 'Apply a voucher' ), 'options' => ( [ '' => t( 'No voucher' ) ] + array_map( function( $voucher ) use ( $vouchers ) {
                $vouchers->setObject( $voucher );
                return $vouchers->getTitle();
            }, $vouchers->fetch( -1 ) ) ), 'value' => '' ] ] );
        }

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'deposit2' );

        $markup = '<form id="deposit" class="form deposit_form"' . \util\attributes::add_attributes( filters()->do_filter( 'deposit_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function upgrade( object $plan, array $attributes = [] ) {
        $fields = [
            'months'    => [ 'type' => 'select', 'label' => t( 'Months' ), 'options' => array_map( function( $m ) use ( $plan ) {
                return sprintf( t( '%s (%s/month)' ), $m['month'], $m['priceF'] );
            }, $plan->getMonths( 24 ) ), 'required' => 'required' ],
            'method'    => [ 'type' => 'select', 'label' => t( 'Payment method' ), 'options' => [ 
                'wallet' => sprintf( t( 'Wallet (%s)' ), $this->user_obj->getBalanceF() )
            ] + array_map( function ( $item ) {
                return esc_html( $item['name'] );
            }, filters()->do_filter( 'deposit-methods', [] ) ), 'required' => 'required' ],
            'wallet'   => [ 'type' => 'hidden', 'value' => $this->user_obj->getBalance() ]
        ];

        if( !$this->user_obj->limits()->isFree() ) {
            $fields[] = [ 'type' => 'custom', 'callback' => function() {
                return '<div class="msg alert">' . sprintf( t( 'You are currently subscribed to another plan until %s. When you upgrade, all the benefits from your current plan will be lost.' ), custom_time( $this->user_obj->limits()->expiration(), 2 ) ) . '</div>';
            } ];
            $fields['agree'] = [ 'type' => 'checkbox', 'title' => t( 'I understand' ), 'description' => t( 'I understand that my current subscription will be terminated immediately' ) ];
            $fields['button']   = [ 'type' => 'button', 'label' => t( 'Upgrade now' ), 'when' => [ '=', 'data[agree]', true ] ];
        } else
        $fields['button']   = [ 'type' => 'button', 'label' => t( 'Upgrade now' ) ];

        $form = new \markup\front_end\form_fields( $fields );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'upgrade2', [ 'planId' => $plan->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="upgrade" class="form upgrade_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'upgrade_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function extend_subscription( object $plan, array $attributes = [] ) {
        $date_time  = new \DateTime;
        $diff_time  = $date_time->diff( new \DateTime( me()->limits()->expiration() ) );
        $months     = 23 - ( $diff_time->y * 12 + $diff_time->m );

        if( $months < 1 ) {
            return '<div class="msg alert mb0">' . t( "You can't extend your subscription until next month" ) . '</div>';
        }

        $fields = [
            'months'    => [ 'type' => 'select', 'label' => t( 'Months' ), 'options' => array_map( function( $m ) use ( $plan ) {
                return sprintf( t( '%s (%s/month)' ), $m['month'], $m['priceF'] );
            }, $plan->getMonths( $months ) ), 'required' => 'required' ],
            'method'    => [ 'type' => 'select', 'label' => t( 'Payment method' ), 'options' => [
                'wallet' => sprintf( t( 'Wallet (%s)' ), $this->user_obj->getBalanceF() )
            ] + array_map( function ( $item ) {
                return esc_html( $item['name'] );
            }, filters()->do_filter( 'deposit-methods', [] ) ), 'required' => 'required' ],
            'wallet'    => [ 'type' => 'hidden', 'value' => $this->user_obj->getBalance() ]
        ];

        $fields['button']   = [ 'type' => 'button', 'label' => t( 'Extend' ) ];

        $form = new \markup\front_end\form_fields( $fields );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'extend-subscription', [ 'planId' => $plan->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="extend_subscription" class="form extend_subscription_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'extend_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function downgrade( object $plan, array $attributes = [] ) {
        $fields = [
            'months'    => [ 'type' => 'select', 'label' => t( 'Months' ), 'options' => array_map( function( $m ) use ( $plan ) {
                return sprintf( t( '%s (%s/month)' ), $m['month'], $m['priceF'] );
            }, $plan->getMonths( 12 ) ), 'required' => 'required' ],
            'method'    => [ 'type' => 'select', 'label' => t( 'Payment method' ), 'options' => [
                'wallet' => sprintf( t( 'Wallet (%s)' ), $this->user_obj->getBalanceF() )
            ] + array_map( function ( $item ) {
                return esc_html( $item['name'] );
            }, filters()->do_filter( 'deposit-methods', [] ) ), 'required' => 'required' ],
            [ 'type' => 'custom', 'callback' => function() {
                return '<div class="msg error">' . sprintf( t( 'You are currently subscribed to another plan until %s. When you downgrade, all the benefits from your current plan will be lost.' ), custom_time( $this->user_obj->limits()->expiration(), 2 ) ) . '</div>';
            } ],
            'agree'     => [ 'type' => 'checkbox', 'title' => t( 'I understand' ), 'description' => t( 'I understand that my current subscription will be terminated immediately' ) ],
            'wallet'    => [ 'type' => 'hidden', 'value' => $this->user_obj->getBalance() ]
        ];

        $fields['button']   = [ 'type' => 'button', 'label' => t( 'Downgrade now' ), 'when' => [ '=', 'data[agree]', true ] ];

        $form = new \markup\front_end\form_fields( $fields );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'upgrade2', [ 'planId' => $plan->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="upgrade" class="form upgrade_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'upgrade_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_user_voucher( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'code'      => [ 'type' => 'text', 'label' => t( 'Voucher' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'add-voucher' ] );

        $markup = '<form id="add_voucher" class="form apply_voucher_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_voucher_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function withdraw( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'option'      => [ 'type' => 'select', 'label' => t( 'Method' ), 'options' => filters()->do_filter( 'withdraw-methods', [ 'paypal' => t( 'PayPal' ), 'stripe' => t( 'Stripe' ) ] ), 'value' => 'paypal', 'required' => 'required' ],
            'pp_address'  => [ 'type' => 'text', 'label' => t( 'Paypal address' ), 'after_label' => '<i class="far fa-envelope"></i>', 'value' => $this->user_obj->getOption( 'pp_address' )->getValue(), 'when' => [ '=', 'data[option]', 'paypal' ] ],
            'stripe_address'  => [ 'type' => 'text', 'label' => t( 'Stripe address' ), 'after_label' => '<i class="far fa-envelope"></i>', 'value' => $this->user_obj->getOption( 'stripe_address' )->getValue(), 'when' => [ '=', 'data[option]', 'stripe' ] ],
            'amount'      => [ 'type' => 'number', 'label' => t( 'Amount' ), 'description' => sprintf( t( 'The minimum required balance to withdraw funds is: %s.<br/>Your available withdrawal balance: %s' ), cms_money_format( ( $min = get_option( 'withdraw_min', 100 ) ) ), $this->user_obj->getRealBalanceF() ), 'value' => cms_money( $this->user_obj->getRealBalance() ) ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Withdraw' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'withdraw2' );

        $markup = '<form id="withdraw" class="form withdraw_form"' . \util\attributes::add_attributes( filters()->do_filter( 'withdraw_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_survey( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'category'  => [ 'type' => 'select', 'label' => t( 'Category' ), 'placeholder' => t( 'Select a category' ), 'options' => array_map( function( $item ) {
                return esc_html( $item->name );
            }, ( new \query\categories )->orderBy( 'id' )->setType( 'website' )->setLanguage()->fetch( -1 ) ), 'required' => 'required' ],
            'responses' => [ 'type' => 'number', 'label' => t( 'How many responses do you need?' ), 'min' => 1, 'max' => 5000, 'value' => 200, 'required' => 'required' ],
        ] );

        if( ( $myTeam = $this->user_obj->myTeam() ) ) {
            $userId = $this->user;
            $form->addFields( [
                'share'     => [ 'type' => 'radio', 'label' => t( 'Personal or share with your team' ), 'options' => [
                    's' => sprintf( t( 'Share this survey with <strong>%s</strong>' ), esc_html( $myTeam->getName() ) ),
                    'f' => t( 'Share with a few members only' ),
                    'p' => t( 'Personal survey' )
                ], 'value' => 's' ],
                'members'   => [ 'type' => 'custom', 'label' => t( 'Select the members you want to share the survey with:' ), 'callback' => function() use ( $userId, $myTeam ) {
                $members    = new \query\team\members;
                $members    ->excludeUserId( $userId )
                            ->setTeamId( $myTeam->getId() )
                            ->setApproved();

                if( !$members->count() ) {
                    return '<div class="msg info">' . t( 'Your team does not have any members' ) . '</div>';
                } else {
                    $markup = '<div class="chbxes">
                    <div>';
                    foreach( $members->fetch( -1 ) as $member ) {
                        $members->setObject( $member );
                        $user   = $members->getUserObject();
                        $markup .= '
                        <div>
                            <input type="checkbox" name="data[member][' . $user->getId() . ']" id="data[member][' . $user->getId() . ']"' . ( $members->getPerm() == 2 ? ' checked disabled' : '' ) . '>
                            <label for="data[member][' . $user->getId() . ']"><strong>' . esc_html( $user->getDisplayName() ) . '</strong></label>
                        </div>';
                    }
                    $markup .= '</div>
                    </div>';

                    return $markup;
                }
            }, 'when' => [ '=', 'data[share]', 'f' ] ] ] );

            if( !$myTeam->isOwner() ) {
                $form->addFields( [ 
                    [ 'type' => 'custom', 'callback' => function() {
                        return '<div class="msg info2">' . t( 'If you leave the team, surveys shared with your team will be removed from your account even if you are the initiator of the survey' ) . '</div>';
                    }, 'when' => [ 'IN', 'data[share]', [ 's', 'f' ] ] ]
                ] );
            }
        }

        $form->addFields( [
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'add-survey' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_survey" class="form add_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_survey( object $survey, array $attributes = [] ) {
        $fields = [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'category'  => [ 'type' => 'select', 'label' => t( 'Category' ), 'placeholder' => t( 'Select a category' ), 'options' => array_map( function( $item ) {
                return esc_html( $item->name );
            }, ( new \query\categories )->orderBy( 'id' )->fetch( -1 ) ), 'required' => 'required' ],
            'avatar'    => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'survey-avatar', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId() ],
            'responses' => [ 'type' => 'number', 'label' => t( 'How many responses do you need?' ), 'min' => $survey->getResponses(), 'max' => max( $survey->getResponses(), 5000 ),  'required' => 'required' ],
            'autoa'     => [ 'type' => 'checkbox', 'label' => t( 'Automatically approve new responses' ), 'title' => t( 'Yes' ), 'description' => t( "Unapproved responses won't be visible in exports or reports" ) ],
            'theme'     => [ 'type' => 'select', 'label' => t( 'Theme' ), 'options' => array_map( function( $item ) {
                return esc_html( $item['name'] );
            }, filters()->do_filter( 'survey-themes', [] ) ), 'required' => 'required' ],
        ];

        // This survey is approved
        if( $survey->getStatus() > 2 )
        $fields['status'] = [ 'type' => 'radio', 'label' => t( 'Status' ), 'classes' => 'inl-chb', 'options' => [ 3 => '<div class="mmsg onhold">' . t( 'Paused ' ) . '</div>', 4 => '<div class="mmsg success">' . t( 'Live ' ) . '</div>', 5 => '<div class="mmsg completed">' . t( 'Finished ' ) . '</div>' ] ];

        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Save' ) ];

        $form = new \markup\front_end\form_fields( $fields );

        $form->setValues( [
            'name'      => $survey->getName(),
            'category'  => $survey->getCategoryId(),
            'responses' => max( $survey->getResponses(), $survey->getResponsesTarget() ),
            'autoa'     => (bool) $survey->autovalidate(),
            'theme'     => $survey->getTemplate(),
            'status'    => $survey->getStatus()
        ] );

        if( $survey->getAvatar() )
        $form->setValue( 'avatar', [ $survey->getAvatar() => $survey->getAvatarURL() ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'edit', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_survey" class="form edit_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_step( object $survey, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'position' => 1, 'label' => t( 'Name' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'add-step', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_step" class="form add_step_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_step_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_step( object $step, array $attributes = [] ) {
        $steps  = steps();
        $steps  ->setSurveyId( $step->getSurveyId() );
        $steps  = $steps->fetch( -1 );
        $conds  = $step->getConditions()->fetch( -1 );
        $first  = array_shift( $conds );

        $conditions = '
        <div class="form_line form_lines">
            <div class="fields_row">
                <div class="form_line">
                    <div class="cconds">
                        <div class="ccond">
                            <span class="ns">' . t( '<strong>IF</strong> step has less than' ) . '</span>
                            <input class="pt" name="data[conditions][' . ( $first ? $first->id : 0 ) . '][points]" type="number" value="' . ( $first ? $first->points : '' ) . '" min="1">
                            <span class="ns">' . t( 'points <strong>THEN</strong>' ) . '</span>
                            <select name="data[conditions][' . ( $first ? $first->id : 0 ) . '][action]" class="step_act ac">
                                <optgroup label="' . t( 'Action' ) . '">
                                    <option value="finish"' . ( $first && $first->action === 0 ? ' selected' : '' ) . '>' . t( 'Finish' ) . '</option>
                                    <option value="disqualify"' . ( $first && $first->action === 1 ? ' selected' : '' ) . '>' . t( 'Disqualify' ) . '</option>
                                    <option value="message"' . ( $first && $first->action === 3 ? ' selected' : '' ) . '>' . t( 'Message' ) . '</option>
                                </optgroup>
                                <optgroup label="' . t( 'Go to a step' ) . '">' . implode( "\n", array_map( function( $v ) use ( $step, $first ) {
                                    return '<option value="' . $v->id . '"' . ( $first && $first->action === 2 && $first->action_id == $v->id ? ' selected' : '' ) . ( $step->getId() === $v->id ? ' disabled' : '' ) . '>' . esc_html( $v->name ) . '</option>';
                                }, $steps ) ) . '</optgroup>';
                                $conditions .= '
                            </select>
                        </div>
                    </div>
                    <div class="form_line' . ( !$first || $first->action !== 3 ? ' hidden' : '' ) . ' mt20">
                        <textarea name="data[conditions][' . ( $first ? $first->id : 0 ) . '][message]">' . ( $first && $first->emsg ? esc_html( $first->emsg ) : '' ) . '</textarea>
                    </div>
                </div>
            </div>
        </div>';

        foreach( $conds as $k => $cond ) { 
            $conditions .= '
            <div class="form_line form_lines">
                <div class="form_line">
                    <div class="fields_row">
                        <div class="form_line">
                            <div class="cconds">
                                <div class="ccond">
                                    <span class="ns">' . t( '<strong>ELSE IF</strong> step has less than' ) . '</span>
                                    <input class="pt" name="data[conditions][' . $cond->id . '][points]" type="number" value="' . $cond->points . '" min="1">
                                    <span class="ns">' . t( 'points <strong>THEN</strong>' ) . '</span>
                                    <select name="data[conditions][' . $cond->id . '][action]" class="step_act ac">
                                        <optgroup label="' . t( 'Action' ) . '">
                                            <option value="finish"' . ( $cond->action === 0 ? ' selected' : '' ) . '>' . t( 'Finish' ) . '</option>
                                            <option value="disqualify"' . ( $cond->action === 1 ? ' selected' : '' ) . '>' . t( 'Disqualify' ) . '</option>
                                            <option value="message"' . ( $cond->action === 3 ? ' selected' : '' ) . '>' . t( 'Message' ) . '</option>
                                        </optgroup>
                                        <optgroup label="' . t( 'Go to a step' ) . '">' . implode( "\n", array_map( function( $v ) use ( $step, $cond ) {
                                            return '<option value="' . $v->id . '"' . ( $cond->action == 2 && $cond->action_id === $v->id ? ' selected' : '' ) . ( $step->getId() === $v->id ? ' disabled' : '' ) . '>' . esc_html( $v->name ) . '</option>';
                                        }, $steps ) ) . '</optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="form_line' . ( $cond->action !== 3 ? ' hidden' : '' ) . ' mt20">
                                <textarea name="data[conditions][' . $cond->id . '][message]" >' . ( $cond->emsg ? esc_html( $cond->emsg ) : '' ) . '</textarea>
                            </div>
                        </div>
                        <div class="form_line l_opts">
                            <a href="#" class="remove "><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </div>';
        }
        
        $conditions .= '
        <div class="form_line form_repeater">
            <div class="form_line add_button">
                <a href="#" data-add_button="data[conditions]">' . t( 'Add condition' ) . '</a>
            </div>
            <div class="form_line">
                <div class="fields_row">
                    <div class="form_line" data-id="ff-data[conditions][#NEW#][0]">
                        <div class="cconds">
                            <div class="ccond">
                                <span class="ns">' . t( '<strong>ELSE IF</strong> step has less than' ) . '</span>
                                <input class="pt" name="data[conditions][#NEW#][points]" type="number" min="1">
                                <span class="ns">' . t( 'points <strong>THEN</strong>' ) . '</span>
                                <select name="data[conditions][#NEW#][action]" class="step_act ac">
                                    <optgroup label="' . t( 'Action' ) . '">
                                        <option value="finish">' . t( 'Finish' ) . '</option>
                                        <option value="disqualify">' . t( 'Disqualify' ) . '</option>
                                        <option value="message">' . t( 'Message' ) . '</option>
                                    </optgroup>
                                    <optgroup label="' . t( 'Go to a step' ) . '">' . implode( "\n", array_map( function( $v ) use ( $step ) {
                                        return '<option value="' . $v->id . '"' . ( $step->getId() === $v->id ? ' disabled' : '' ) . '>' . esc_html( $v->name ) . '</option>';
                                    }, $steps ) ) . '</optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="form_line hidden mt20">
                            <textarea name="data[conditions][#NEW#][message]"></textarea>
                        </div>
                    </div>
                    <div class="form_line l_opts">
                        <a href="#" class="remove "><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
        </div>';

        $conditions .= '
        <div class="form_line">
            <div class="cconds">
                <div class="ccond">
                    <span class="ns">' . t( '<strong>ELSE</strong>' ) . '</span>
                    <select name="data[c_fallback]" class="step_act ac">
                        <optgroup label="' . t( 'Action' ) . '">
                            <option value="finish"' . ( $step->getActionType() == 2 && $step->getActionId() == 0 ? ' selected' : '' ) . '>' . t( 'Finish' ) . '</option>
                            <option value="disqualify"' . ( $step->getActionType() == 2 && $step->getActionId() == 1 ? ' selected' : '' ) . '>' . t( 'Disqualify' ) . '</option>
                            <option value="message"' . ( $step->getActionType() == 2 && $step->getActionId() == 2 ? ' selected' : '' ) . '>' . t( 'Message' ) . '</option>
                        </optgroup>
                        <optgroup label="' . t( 'Go to a step' ) . '">' . implode( "\n", array_map( function( $v ) use ( $step ) {
                            return '<option value="' . $v->id . '"' . ( $step->getActionType() == 3 && $step->getActionId() === $v->id ? ' selected' : '' ) . ( $step->getId() === $v->id ? ' disabled' : '' ) . '>' . esc_html( $v->name ) . '</option>';
                        }, $steps ) ) . '</optgroup>
                    </select>
                </div>
            </div>
            <div class="form_line mt20' . ( $step->getActionType() !== 2 || $step->getActionId() !== 2 ? ' hidden' : '' ) . '">
                <textarea name="data[c_fallback_msg]">' . esc_html( $step->getEMsg() ) . '</textarea>
            </div>
        </div>';

        $advancedFields = [];
        $settings       = $step->getSetting();

        if( $step->isMain() ) {
            $advancedFields = [ 'time' => [  'label' => t( 'Time' ), 'fields' => [
                'mtime'  => [ 'type' => 'checkbox', 'title' => t( 'Modify time' ), 'description' => t( 'You can modify the time a respondent has to complete the survey when this step is reached for the first time' ) ],
                'ntime'  => [ 'type' => 'number', 'label' => t( 'New time to complete (minutes)' ), 'description' => t( 'If the survey is not completed in this time, the response will be automatically disqualified' ), 'min' => 1, 'when' => [ '=', 'data[mtime]', true ] ]
            ], 'grouped' => false ] ];
        } else {
            $advancedFields = [ 'navigation' => [  'label' => t( 'Navigation' ), 'fields' => [
                'hnav'  => [ 'type' => 'checkbox', 'title' => 'Allow steps navigation', 'description' => t( 'Allow respondents to navigate to the previous step' ) ]
            ], 'grouped' => false ],
            'time' => [  'label' => t( 'Time' ), 'fields' => [
                'mtime'  => [ 'type' => 'checkbox', 'title' => t( 'Modify time' ), 'description' => t( 'You can modify the time a respondent has to complete the survey when this step is reached for the first time' ) ],
                'ntime'  => [ 'type' => 'number', 'label' => t( 'New time to complete (minutes)' ), 'description' => t( 'If the survey is not completed in this time, the response will be automatically disqualified' ), 'min' => 1, 'when' => [ '=', 'data[mtime]', true ] ]
            ], 'grouped' => false ] ];
        }
            
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'after'     => [ 'type' => 'select', 'label' => t( 'After submit' ), 'options' => [ 'action' => t( 'Use an action' ), 'conditions' => t( 'Use conditions' ) ] ],
            'action'    => [ 'type' => 'group', 'grouped' => false, 'fields' => [
                'actions'   => [ 'type' => 'radio', 'label' => t( 'Action' ), 'options' => [ 'finish' => t( 'Finish' ), 'goto' => t( 'Go to another step' ) ] ],
                'step'      => [ 'type' => 'custom', 'callback' => function( $val ) use ( $steps, $step ) {
                    $markup = '
                    <select name="data[step]">
                        <optgroup label="' . t( 'Go to a step' ) . '">' . implode( "\n", array_map( function( $v ) use ( $steps, $step, $val ) {
                            return '<option value="' . $v->id . '"' . ( !empty( $val['step'] ) && $v->id === (int) $val['step'] ? ' selected' : '' ) . ( $step->getId() === $v->id ? ' disabled' : '' ) . '>' . esc_html( $v->name ) . '</option>';
                        }, $steps ) ) . '</optgroup>';
                        $markup .= '
                    </select>';

                    return $markup;
                }, 'when' => [ '=', 'data[actions]', 'goto' ] ]
            ], 'when' => [ '=', 'data[after]', 'action' ] ],
            [ 'type' => 'custom', 'callback' => $conditions, 'classes' => 'form_lines', 'when' => [ '=', 'data[after]', 'conditions' ] ],
            [ 'type' => 'dropdown', 'label' => t( 'Advanced' ), 'grouped' => false, 'fields' => $advancedFields ],
            [ 'type' => 'dropdown', 'label' => t( 'Front-end info' ), 'fields' => [
                'info' => [ 'label' => t( 'Title & description' ), 'fields' => [
                    'title' => [ 'type' => 'text', 'label' => t( 'Title' ) ],
                    'desc'  => [ 'type' => 'textarea', 'label' => t( 'Description' ) ]
                ] ] ], 'grouped' => false ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'name'      => $step->getName(),
            'after'     => ( $step->getActionType() == 2 || $step->getActionType() == 3 ? 'conditions' : 'action' ),
            'actions'   => ( $step->getActionType() == 1 ? 'goto' : 'finish' ),
            'step'      => $step->getActionId(),
            'hnav'      => empty( $settings['hnav'] ),
            'mtime'     => !empty( $settings['time'] ),
            'ntime'     => ( $settings['time'] ?? NULL ),
            'info'      => [
                'title' => ( $settings['title'] ?? '' ),
                'desc'  => ( $settings['desc'] ?? '' ),
            ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-step2', [ 'action2' => 'edit', 'step' => $step->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_step" class="form edit_step_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_step_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function delete_step( object $step, array $attributes = [] ) {
        $steps  = steps()
                ->setSurveyId( $step->getSurveyId() )
                ->select( [ 'id', 'name' ] );
        $steps  = $steps->fetch( -1 );

        unset( $steps[$step->getId()] );

        $form   = new \markup\front_end\form_fields( [
            'action'    => [ 'type' => 'select', 'label' => t( 'Questions at this step' ), 'options' => [ 'del' => t( 'Delete all questions at this step' ), 'move' => t( 'Move to another step' ) ] ],
            'step'      => [ 'type' => 'select', 'label' => t( 'Select a step' ), 'options' => array_map( function( $v ) {
                return esc_html( $v->name );
            }, $steps ), 'when' => [ '=', 'data[action]', 'move' ] ],
            [ 'type' => 'button', 'label' => t( 'Delete' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-step2', [ 'action2' => 'delete', 'step' => $step->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="delete_step" class="form delete_step_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'delete_step_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_survey_page( object $survey, string $page_id, array $pageInfo = [], array $attributes = [] ) {
        if( !empty( $pageInfo ) ) {
            $page = $pageInfo;
        } else {
            $page = getSurveyPage( $survey, $page_id );
            if( !$page ) return ;
        }

        $sh         = new \site\shortcodes( $page['content'] );
        $sh_list    = $sh->toArray();

        $form = new \markup\front_end\form_fields( [
            'content'       => [ 'type' => 'repeater', 'fields' => [
                [ 'type' => 'group', 'fields' => [
                    'content'   => [ 'type' => 'textarea', 'label' => t( 'Content' ) ],
                    'type'      => [ 'type' => 'select', 'options' => [ 'title' => t( 'Title' ), 'h' => t( 'Headline' ), 'p' => t( 'Paragraph' ), 'icon' => t( 'Icon' ), 'buttons' => t( 'Button(s)' ) ] ],
                    'attr'      => [ 'type' => 'inline-group', 'fields' => [
                        [ 'type' => 'custom', 'callback' => t( 'Visible <strong>IF</strong> points' ), 'classes' => 'wa asc' ],
                        'sign'      => [ 'type' => 'select', 'options' => [ '>' => '>', '>=' => '>=', '=' => '=', '<' => '<', '<=' => '<=' ], 'classes' => 'wa' ],
                        'points'    => [ 'type' => 'number' ],  
                    ] ],
                ], 'classes' => 'fl_bg', 'grouped' => false ]
            ], 'add_button' => t( 'Add content' ), 'when' => [ '!=', 'data[after_action]', 'r' ] ],
            'after_action'  => [ 'type' => 'radio', 'label' => t( 'Action' ), 'options' => [ '' => t( 'Display this page' ), 'dr' => t( 'Display this page & redirect to a web address' ), 'r' => t( 'Redirect imediately to a web address' ) ], 'value' => '' ],
            'redirect_to'   => [ 'type' => 'text', 'label' => t( 'Redirect to (web page address)' ), 'when' => [ 'IN', 'data[after_action]', [ 'r', 'dr' ] ] ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'content'   => array_map( function( $v ) {
                return [ 'content' => html_decode( $v['content'] ), 'type' => $v['type'], 'attr' => $v['attrs'] ];
            }, $sh_list ),
        ] );

        $after = $survey->meta()->get( 'af:' . $page_id );
        if( $after ) {
            $opt = json_decode( $after );
            $form->setValues( [
                'after_action'  => key( $opt ),
                'redirect_to'   => current( $opt ),
            ] );
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'edit-page', 'survey' => $survey->getId(), 'page' => $page_id ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_survey_page" class="form edit_survey_page_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_page_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function survey_before_actions( object $survey, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'require'   => [ 'type' => 'checkbox', 'title' => t( 'Request a number of points to finish this survey' ) ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points required or disqualify the answer' ), 'description' => t( 'Eg.: If you set 10, all answers that do not accumulate at least 10 points from all previous steps will be automatically disqualified. Points verification is made after the "Finish" action is triggered.' ), 'min' => 1, 'when' => [ '=', 'data[require]', true ] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $points = $survey->meta()->get( 'minPts', NULL );

        if( $points !== NULL ) {
            $form->setValues( [
                'require'   => true,
                'points'    => (int) $points
            ] );
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'before-actions', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_before_actions" class="form survey_before_actions_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_before_actions_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function survey_after_actions( object $survey, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'use-wh' => [ 'type' => 'checkbox', 'title' => t( 'Webhook' ) ],
            [ 'type' => 'group', 'grouped' => false, 'fields' => [
                'URL'   => [ 'type' => 'text', 'label' => t( 'URL' ) ]
            ], 'when' => [ '=', 'data[use-wh]', 1 ] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $pbURL = $survey->meta()->get( 'Webhook', NULL );

        if( !empty( $pbURL ) ) {
            $form->setValues( [
                'use-wh'    => true,
                'URL'       => $pbURL
            ] );
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'after-actions', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_after_actions" class="form survey_after_actions_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_after_actions_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_survey_label( object $survey, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'color'     => [ 'type' => 'custom', 'callback' => function() {
                $markup = '<div class="letl">';
                $rand   = rand( 0, 23 );
                foreach( range( 'A', 'X' ) as $i => $let ) {
                    $markup .= '
                    <label class="sav">
                        <input type="radio" name="data[color]" value="' . $let . '"' . ( $i == $rand ? ' checked' : '' ) . ' />
                        <i class="avt-' . $let . '"></i>
                    </label>';
                }
                $markup .= '</div>';
                return $markup;
            } ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'add-label', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_survey_label" class="form add_survey_label_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_survey_label_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_survey_label( object $label, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'color'     => [ 'type' => 'custom', 'callback' => function() use ( $label ) {
                $markup = '<div class="letl">';
                foreach( range( 'A', 'X' ) as $i => $let ) {
                    $markup .= '
                    <label class="sav">
                        <input type="radio" name="data[color]" value="' . $let . '"' . ( $let == $label->getColor() ? ' checked' : '' ) . ' />
                        <i class="avt-' . $let . '"></i>
                    </label>';
                }
                $markup .= '</div>';
                return $markup;
            } ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'name'      => $label->getName(),
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'edit-label', 'survey' => $label->getSurveyId(), 'label' => $label->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_survey_label" class="form edit_survey_label_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_label_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_response_comment( object $survey, object $response, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'msg'       => [ 'type' => 'textarea', 'label' => t( 'Message' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'msg'      => $response->getComment(),
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'edit-response-comment', 'survey' => $survey->getId(), 'response' => $response->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_response_comment" class="form edit_response_comment_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_response_comment_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_report( object $survey, object $report, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'title'     => [ 'type' => 'text', 'label' => t( 'Title' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'title'     => $report->getTitle()
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'edit-report', 'survey' => $survey->getId(), 'report' => $report->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_report" class="form edit_report_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function share_report( object $survey, object $report, array $attributes = [] ) {
        if( !( $myTeam = $this->user_obj->myTeam() ) ) {
            return '<div class="msg info mb0">' . t( 'Reports can only be shared between team members' ) . '</div>';
        }

        $form   = new \markup\front_end\form_fields;
        $userId = $this->user;
        $form   ->addFields( [
            'share'     => [ 'type' => 'radio', 'options' => [
                's' => sprintf( t( 'Share this report with <strong>%s</strong>' ), esc_html( $myTeam->getName() ) ),
                'f' => t( 'Share with a few members only' )
            ], 'value' => 's' ],
            'members'   => [ 'type' => 'custom', 'label' => t( 'Select the members you want to share the report with:' ), 'callback' => function() use ( $userId, $myTeam ) {
            $members    = new \query\team\members;
            $members    ->excludeUserId( $userId )
                        ->setTeamId( $myTeam->getId() )
                        ->setApproved();

            if( !$members->count() ) {
                return '<div class="msg info">' . t( 'Your team does not have any members' ) . '</div>';
            } else {
                $markup = '<div class="chbxes">
                <div>';
                foreach( $members->fetch( -1 ) as $member ) {
                    $members->setObject( $member );
                    $user   = $members->getUserObject();
                    $markup .= '
                    <div>
                        <input type="checkbox" name="data[member][' . $user->getId() . ']" id="data[member][' . $user->getId() . ']">
                        <label for="data[member][' . $user->getId() . ']"><strong>' . esc_html( $user->getDisplayName() ) . '</strong></label>
                    </div>';
                }
                $markup .= '</div>
                </div>';

                return $markup;
            }
        }, 'when' => [ '=', 'data[share]', 'f' ] ] ] );

        $form->addFields( [
            'button'    => [ 'type' => 'button', 'label' => t( 'Share' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'share-report', 'survey' => $survey->getId(), 'report' => $report->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="share_report" class="form share_report_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'share_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function export_report( object $survey, object $report, array $attributes = [] ) {
        $options    = [];
        $reportId   = $report->getId();
        $markup     = '
        <div class="form_line form_dropdown">
            <div>
                <span>' . t( 'CSV' ) . '</span>
                <i class="fas fa-angle-down"></i>
            </div>
            <div>';

            $form   = new \markup\front_end\form_fields;
            $form   ->addFields( [
                'export_csv'    => [ 'type' => 'radio', 'classes' => 'chbm200', 'options' => [
                    'report'    => t( 'Export this report' ), 
                    'responses' => t( 'Export responses from this report' ) 
                ] ],
                'csv_also_export'   => [ 'type' => 'checkboxes', 'label' => t( 'Also export' ), 'classes' => 'chbm200', 'options' => [
                    'points'    => t( 'Points' ),
                    'tid'       => t( 'Tracking id' ),
                    'rtime'     => t( 'Response time' ),
                    'vars'      => t( 'Variables' ),
                    'country'   => t( 'Country' ),
                    'labels'    => t( 'Labels' ),
                    'date'      => t( 'Date' )
                ], 'when' => [ '=', 'data[export_csv]', 'responses' ] ],
                [ 'type'        => 'button', 'label' => t( 'Export' ) ]
            ] );

            $form->setValues( [
                'export_csv'        => 'report',
                'csv_also_export'   => [ 'points' => 'points', 'tid' => 'tid', 'rtime' => 'rtime', 'vars' => 'vars', 'country' => 'country', 'labels' => 'labels', 'date' => 'date' ],
                'csv_settings'      => [ 'emptyq' => 'emptyq' ]
            ] );
    
            $this->last_form            = $form;
            $fields                     = $form->build();
            $attributes['method']       = 'POST';
            $attributes['action']       = admin_url( 'export/CSV/report/' . $report->getId() );
    
            $markup .= '<form id="share_report" class="form share_report_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'share_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

            $markup .= '</div>
        </div>';

        $options['csv'] = $markup;

        $markup = '
        <div class="form_line form_dropdown">
            <div>
                <span>' . t( 'Print' ) . '</span>
                <i class="fas fa-angle-down"></i>
            </div>
            <div>';
            
            $reports= new \query\survey\saved_reports;
            $reports->setSurveyId( $survey->getId() )
                    ->setUserId( $this->user_obj->getId() )
                    ->setSaved()
                    ->select( [ 'id', 'title' ] )
                    ->orderBy( 'id_desc' );

            $form   = new \markup\front_end\form_fields;
            $form   ->addFields( [
                'export_print'  => [ 'type' => 'radio', 'classes' => 'chbm200', 'options' => [ 
                    'report'    => t( 'Export this report' ), 
                    'responses' => t( 'Export responses from this report' ),
                    'compare'   => t( 'Compare and export multiple reports' ) ] ],
                'reports'       => [ 'type' => 'checkboxes', 'label' => t( 'Select the reports you want to compare' ), 'options' => array_map( function( $v ) {
                    return esc_html( $v->title );
                }, $reports->fetch( -1 ) ), 'when' => [ '=', 'data[export_print]', 'compare' ] ],
                'print_also_export' => [ 'type' => 'checkboxes', 'label' => t( 'Also export' ), 'classes' => 'chbm200', 'options' => [
                    'points'    => t( 'Points' ),
                    'tid'       => t( 'Tracking id' ),
                    'rtime'     => t( 'Response time' ),
                    'vars'      => t( 'Variables' ),
                    'country'   => t( 'Country' ),
                    'labels'    => t( 'Labels' ),
                    'date'      => t( 'Date' )
                ], 'when' => [ '=', 'data[export_print]', 'responses' ] ],
                'print_settings'=> [ 'type' => 'checkboxes', 'label' => t( 'Settings' ), 'classes' => 'chbm200', 'options' => [
                    'emptyq'    => t( 'Export questions without answer' )
                ], 'when' => [ '=', 'data[export_print]', 'responses' ] ],
                'orderby'       => [ 'type' => 'select', 'label' => t( 'Order by' ), 'options' => [
                    'date_d'    => t( 'Date (newest to oldest)' ),
                    'date'      => t( 'Date (oldest to newest)' ),
                    'points_d'  => t( 'Points (higher to lower)' ),
                    'points'    => t( 'Points (lower to lower)' ),
                    'time_d'    => t( 'Response time (fastest to slowest)' ),
                    'time'      => t( 'Response time (slowest to fastest)' )
                ], 'when' => [ '=', 'data[export_print]', 'responses' ] ],
                [ 'type' => 'button', 'label' => t( 'View & print' ) ]
            ] );

            $form->setValues( [
                'export_print'      => 'report',
                'reports'           => [ $reportId => $reportId ],
                'print_also_export' => [ 'points' => 'points', 'tid' => 'tid', 'rtime' => 'rtime', 'vars' => 'vars', 'country' => 'country', 'labels' => 'labels', 'date' => 'date' ],
                'print_settings'    => [ 'emptyq' => 'emptyq' ]
            ] );
    
            $this->last_form            = $form;
            $fields                     = $form->build();
            $attributes['action']       = admin_url( 'export/print/report/' . $report->getId() );
            $attributes['enctype']      = 'multipart/form-data';
    
            $markup .= '<form id="share_report" class="form share_report_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'share_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

            $markup .= '</div>
        </div>';

        $options['print'] = $markup;

        return '<div class="form_lines">' . implode( "\n", filters()->do_filter( 'export_options', $options, $survey, $report ) ) . '</div>';
    }

    public function delete_report( object $survey, object $report, string $location, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'agree'     => [ 'type' => 'checkbox', 'label' => t( 'This action cannot be undone' ), 'title' => t( 'I understand' ) ],
            'location'  => [ 'type' => 'hidden', 'value' => $location ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Delete' ), 'when' => [ '=', 'data[agree]', true ] ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'delete-report', 'survey' => $survey->getId(), 'report' => $report->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="delete_report" class="form delete_report_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'delete_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_response( object $survey, array $attributes = [] ) {
        $fields = [
            [ 'type' => 'custom', 'label' => t( 'Option information' ), 'callback' => t( 'All information that could be assigned to a response is optional' ) ],
            [ 'type' => 'dropdown', 'fields' => [ 'countries' => [ 'label' => t( 'Country' ), 'fields' => [
                'country' => [ 'type' => 'radio', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                    return esc_html( t( $item->name ) );
                }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ), 'value' => [] ]
                ], 'grouped' => false ]
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'labels' => [ 'label' => t( 'Assign a label' ), 'fields' => [
                'label' => [ 'type' => 'checkboxes', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                    return esc_html( t( $item->name ) );
                }, $survey->getLabels()->fetch( -1 ) ), 'value' => [] ]
                ], 'grouped' => false ]
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'countries' => [ 'label' => t( 'Comment' ), 'fields' => [
                'comment' => [ 'type' => 'textarea' ]
                ], 'grouped' => false ]
            ], 'grouped' => false ],

            'action' => [ 'type' => 'select', 'label' => t( 'Action after' ), 'options' => [ 'go' => t( 'Respond' ), 'nothing' => t( 'Do nothing' ), 'duplicate' => t( 'Duplicate the response' ) ] ],
            'duplicates' => [ 'type' => 'number', 'label' => t( 'How many empty responses?' ), 'description' => t( 'The number of blank responses you want to add using the same settings' ), 'min' => 1, 'value' => 1, 'when' => [ '=', 'data[action]', 'duplicate' ] ]
        ];

        $fields['btn']              = [ 'type' => 'button', 'label' => t( 'Add response now' ), 'classes' => 'tc' ];
        $form                       = new \markup\front_end\form_fields( $fields );
        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'add-response', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_response" class="form add_response_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_response_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function export_response( object $survey, object $response, array $attributes = [] ) {
        $options    = [];
        $markup     = '
        <div class="form_line form_dropdown">
            <div>
                <span>' . t( 'CSV' ) . '</span>
                <i class="fas fa-angle-down"></i>
            </div>
            <div>';

            $form   = new \markup\front_end\form_fields;
            $form   ->addFields( [
                'csv_also_export'   => [ 'type' => 'checkboxes', 'label' => t( 'Also export' ), 'classes' => 'chbm200', 'options' => [
                    'points'    => t( 'Points' ),
                    'tid'       => t( 'Tracking id' ),
                    'rtime'     => t( 'Response time' ),
                    'vars'      => t( 'Variables' ),
                    'country'   => t( 'Country' ),
                    'labels'    => t( 'Labels' ),
                    'date'      => t( 'Date' )
                ] ],
                [ 'type'        => 'button', 'label' => t( 'Export' ) ]
            ] );

            $form->setValues( [
                'csv_also_export'   => [ 'points' => 'points', 'tid' => 'tid', 'rtime' => 'rtime', 'vars' => 'vars', 'country' => 'country', 'labels' => 'labels', 'date' => 'date' ],
                'csv_settings'      => [ 'emptyq' => 'emptyq' ]
            ] );
    
            $this->last_form            = $form;
            $fields                     = $form->build();
            $attributes['method']       = 'POST';
            $attributes['action']       = admin_url( 'export/CSV/response/' . $response->getId() );
    
            $markup .= '<form id="share_response" class="form share_response_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'share_response_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

            $markup .= '</div>
        </div>';

        $options['csv'] = $markup;

        $markup = '
        <div class="form_line form_dropdown">
            <div>
                <span>' . t( 'Print' ) . '</span>
                <i class="fas fa-angle-down"></i>
            </div>
            <div>';

            $form   = new \markup\front_end\form_fields;
            $form   ->addFields( [
                'print_also_export' => [ 'type' => 'checkboxes', 'label' => t( 'Also export' ), 'classes' => 'chbm200', 'options' => [
                    'points'    => t( 'Points' ),
                    'tid'       => t( 'Tracking id' ),
                    'rtime'     => t( 'Response time' ),
                    'vars'      => t( 'Variables' ),
                    'country'   => t( 'Country' ),
                    'labels'    => t( 'Labels' ),
                    'date'      => t( 'Date' )
                ] ],
                [ 'type' => 'button', 'label' => t( 'View & print' ) ]
            ] );

            $form->setValues( [
                'export_print'      => 'report',
                'print_also_export' => [ 'points' => 'points', 'tid' => 'tid', 'rtime' => 'rtime', 'vars' => 'vars', 'country' => 'country', 'labels' => 'labels', 'date' => 'date' ],
                'print_settings'    => [ 'emptyq' => 'emptyq' ]
            ] );
    
            $this->last_form            = $form;
            $fields                     = $form->build();
            $attributes['action']       = admin_url( 'export/print/response/' . $response->getId() );
            $attributes['enctype']      = 'multipart/form-data';
    
            $markup .= '<form id="share_response" class="form share_response_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'share_response_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

            $markup .= '</div>
        </div>';

        $options['print'] = $markup;

        return '<div class="form_lines">' . implode( "\n", filters()->do_filter( 'export_options', $options, $survey, $response ) ) . '</div>';
    }

    public function delete_response( object $survey, object $result, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'agree'     => [ 'type' => 'checkbox', 'label' => t( 'This action cannot be undone' ), 'title' => t( 'I understand' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Delete' ), 'when' => [ '=', 'data[agree]', true ] ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-result2', [ 'action2' => 'delete', 'survey' => $survey->getId(), 'result' => $result->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="delete_response" class="form delete_response_form_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'delete_response_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }


    public function add_question( object $survey, array $attributes = [] ) {
        $opt    = $_POST['options'] ?? [];
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'position' => 1, 'label' => t( 'Question' ), 'required' => 'required' ],
            'info'      => [ 'type' => 'text', 'position' => 2, 'label' => t( 'Info' ), 'description' => t( 'A short piece of information that may help the respondent to better understand the question' ) ],
            'type'      => [ 'type' => 'select', 'position' => 3, 'label' => t( 'Question type' ), 'placeholder' => t( 'Select a question type' ), 'options' => ( $survey_types = survey_types() )->getTheList(), 'required' => 'required' ],
            'sposition' => [ 'type' => 'custom', 'position' => 4, 'label' => t( 'Move' ), 'callback' => function() use ( $survey, $opt ) {
                $steps  = steps();
                $steps  ->setSurveyId( $survey->getId() );
                $move   = '';

                foreach( $steps->fetch( -1 ) as $sId => $step ) {
                    $steps->resetInfo()->setObject( $step );
                    $i          = 2;
                    $sopt       = [];
                    $sopt[1]    = [ 'el' => '<option value="' . $sId . ',1"' . ( isset( $opt['step'] ) && isset( $opt['pos'] ) && $opt['step'] == $sId && $opt['pos'] == 'first' ? ' selected' : '' ), 'name' => t( 'At beginning' ) ];
                    foreach( $steps->getQuestions()->setVisible( 2 )->fetch( -1 ) as $q ) {
                        $sopt[$i]               = [ 'el' => '<option value="' . $q->step . ',' . ( $q->position+1 ) . '"' ];
                        $sopt[$i]['name']       = sprintf( t( 'After "%s"' ), esc_html( $q->title ) );
                        if( !empty( $opt['question'] ) && isset( $opt['pos'] ) && $opt['question'] == $q->id ) {
                            if( $opt['pos'] == 'before' ) {
                                $sopt[($i-1)]['attrs']  = ' selected';
                            } else if( $opt['pos'] == 'after' ) {
                                $sopt[$i]['attrs']      = ' selected';
                            }
                        }
                        $i++;
                    }

                    if( isset( $opt['step'] ) && isset( $opt['pos'] ) && $opt['step'] == $sId && $opt['pos'] == 'last' ) {
                        $sopt[($i-1)]['attrs']  = ' selected';
                    }

                    $move .= '<optgroup label="' . esc_html( $steps->getName() ) . '">' . implode( "\n", array_map( function( $v ) {
                        return $v['el'] . ( !empty( $v['attrs'] ) ? ' ' . $v['attrs'] : '' ) . '>' . $v['name'] . '</option>';
                    }, $sopt ) ) . '</optgroup>';
                }

                return '<select name="data[sposition]">' . $move . '</select>';
            } ],
            'required'  => [ 'type' => 'checkbox', 'position' => 6, 'label' => t( 'Require an answer?' ), 'title' => t( 'Required' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $survey_types->modifySubmitForm( $form );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'add-question', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_question" class="form add_question_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_question_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_question( object $question, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'position' => 1, 'label' => t( 'Question' ), 'required' => 'required' ],
            'info'      => [ 'type' => 'text', 'position' => 2, 'label' => t( 'Info' ), 'description' => t( 'A short piece of information that may help the respondent to better understand the question' ) ],
            'type'      => [ 'type' => 'info', 'position' => 3, 'label' => t( 'Question type' ), 'required' => 'required' ],
            'sposition' => [ 'type' => 'custom', 'position' => 4, 'label' => t( 'Move' ), 'callback' => function( $values ) use ( $question ) {
                $steps  = steps();
                $steps  ->setSurveyId( $question->getSurveyId() );
                $move   = '';
                
                foreach( $steps->fetch( -1 ) as $sId => $step ) {
                    $steps      ->setObject( $step );
                    $i          = 2;
                    $sopt       = [];
                    $sopt[1]    = [ 'el' => '<option value="' . $sId . ',1"' . ( $question->getStepId() == $sId && $question->getPosition() == 0 ? ' selected' : '' ), 'name' => t( 'At beginning' ) ];
                    foreach( $steps->getQuestions()->setVisible( 2 )->fetch( -1 ) as $q ) {
                        if( $question->getStepId() == $sId && $question->getId() == $q->id ) {
                            $sopt[($i-1)]['attrs']  = 'disabled';
                            $sopt[$i]               = [ 'el' => '<option value="' . $sId . ',' . $i . '" selected' ];
                            $sopt[$i]['name']       = '&#8594; ' . esc_html( $q->title );
                        } else {
                            $sopt[$i]               = [ 'el' => '<option value="' . $sId . ',' . $i . '"' ];
                            $sopt[$i]['name']       = sprintf( t( 'After "%s"' ), esc_html( $q->title ) );
                        }
                        $i++;
                    }
                    $move .= '<optgroup label="' . esc_html( $steps->getName() ) . '">' . implode( "\n", array_map( function( $v ) {
                        return $v['el'] . ( !empty( $v['attrs'] ) ? ' ' . $v['attrs'] : '' ) . '>' . $v['name'] . '</option>';
                    }, $sopt ) ) . '</optgroup>';
                }

                return '<select name="data[sposition]">' . $move . '</select>';
            } ],
            'required'  => [ 'type' => 'checkbox', 'position' => 5, 'label' => t( 'Require an answer?' ), 'title' => t( 'Required' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $survey_types   = survey_types();
        $survey_types   ->setType( $question->getType() );
        $survey_types   ->modifySubmitForm( $form, $question );
        $form           ->setValues( [
            'name'      => $question->getTitle(),
            'info'      => $question->getInfo(),
            'type'      => $survey_types->getName(),
            'step'      => $question->getStepId(),
            'sposition' => $question->getPosition(),
            'required'  => $question->isRequired()
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-question2', [ 'action2' => 'edit', 'question' => $question->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_question" class="form edit_question_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_question_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function restore_question( object $question, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'sposition' => [ 'type' => 'custom', 'label' => t( 'Position' ), 'callback' => function() use ( $question ) {
                $steps  = steps();
                $steps  ->setSurveyId( $question->getSurveyId() );
                $move   = '';

                foreach( $steps->fetch( -1 ) as $sid => $step ) {
                    $steps      ->setObject( $step );
                    $i          = 2;
                    $sopt       = [];
                    $sopt[1]    = [ 'el' => '<option value="' . $sid . ',1"', 'name' => t( 'At beginning' ) ];
                    foreach( $steps->getQuestions()->setVisible( 2 )->fetch( -1 ) as $q ) {
                        $sopt[$i]               = [ 'el' => '<option value="' . $q->step . ',' . $i . '"' ];
                        $sopt[$i]['name']       = sprintf( t( 'After "%s"' ), esc_html( $q->title ) );
                        $i++; 
                    }
                    $move .= '<optgroup label="' . esc_html( $steps->getName() ) . '">' . implode( "\n", array_map( function( $v ) {
                        return $v['el'] . ( !empty( $v['attrs'] ) ? ' ' . $v['attrs'] : '' ) . '>' . $v['name'] . '</option>';
                    }, $sopt ) ) . '</optgroup>';
                }

                return '<select name="data[sposition]">' . $move . '</select>';
            } ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Restore' ) ]
        ] );

        $form->setValues( [
            'step'      => $question->getStepId(),
            'sposition' => $question->getPosition()
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-question2', [ 'action2' => 'restore', 'question' => $question->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="restore_question" class="form restore_question_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'restore_question_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function delete_question( object $question, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'agree'     => [ 'type' => 'checkbox', 'position' => 1, 'title' => t( 'I want to delete this question' ), 'description' => t( 'This question will be permanently deleted and its answers will be permanently lost' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Delete' ), 'when' => [ '=', 'data[agree]', true ] ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-question2', [ 'action2' => 'delete', 'question' => $question->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="delete_question" class="form delete_question_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'delete_question_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_collector( object $survey, array $attributes = [] ) {
        $audience = filters()->do_filter( 'survey-audience-options', [
            'country'   => [ 'type' => 'checkbox', 'label' => t( 'Country' ), 'title' => t( 'Any' ) ],
            'countries' => [ 'type' => 'checkboxes', 'label' => t( 'Select the countries' ), 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                return esc_html( t( $item->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->select( [ 'id', 'name' ] )->selectKey( 'id' )->fetch( -1 ) ), 'value' => [], 'when' => [ '=', 'data[audience][country]', false ] ],
            'gender'    => [ 'type' => 'radio', 'label' => t( 'Gender' ), 'options' => [ 0 => t( 'Both' ), 1 => t( 'Male' ), 2 => t( 'Female' ) ] ],
            'any_age'   => [ 'type' => 'checkbox', 'label' => t( 'Age' ), 'title' => t( 'Any' ) ],
            'age'       => [ 'type' => 'checkboxes', 'label' => t( 'Select the age categories' ), 'options' => [ 1 => t( '18-24 years old' ), 2 => t( '25-34 years old' ), 3 => t( '35-44 years old' ), 4 => t( '45-54 years old' ), 5 => t( '55-64 years old' ), 6 => t( '65-74 years old' ) ], 'value' => [], 'when' => [ '=', 'data[audience][any_age]', false ] ],
        ] );

        $adminOpts = [];

        if( me()->isAdmin() ) {
            $adminOpts = [ 'type' => 'dropdown', 'label' => t( 'Admin options' ), 'grouped' => false, 'fields' => [
                'admin_lpoints' => [ 'label' => t( 'Loyalty points' ), 'fields' => [
                    'lpoints'      => [ 'type' => 'number', 'label' => t( 'Loyalty points for each answer' ) ],
                ], 'grouped' => false ]
            ] ];
        }

        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'type'      => [ 'type' => 'radio', 'label' => t( 'How to collect responses' ), 'options' => [ 1 => t( 'Buy responses' ), 0 => t( 'Use my own method (website, social networks, etc.)' ) ], 'value' => 0 ],
            [ 'type' => 'dropdown', 'label' => t( 'Audience' ), 'grouped' => false, 'fields' => [ 'audience' => [ 'label' => t( 'Target audience' ), 'fields' => $audience ] ], 'when' => [ '=', 'data[type]', 1 ] ],
            'price'     => [ 'type' => 'number', 'label' => t( 'Price per response' ), 'step' => '.01', 'min' => ( $min = ( !me()->isAdmin() ? (double) get_option( 'min_cpa' ) : 0 ) ), 'value' => $min, 'when' => [ '=', 'data[type]', 1 ] ],
            $adminOpts,
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $form->setValues( [
            'audience' => [
                'country'   => false,
                'gender'    => 0,
                'any_age'   => false
            ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'add-collector', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_collector" class="form add_collector_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_collector( object $collector, array $attributes = [] ) {
        $audience = filters()->do_filter( 'survey-audience-options', [
            'country'   => [ 'type' => 'checkbox', 'label' => t( 'Country' ), 'title' => t( 'Any' ) ],
            'countries' => [ 'type' => 'checkboxes', 'label' => t( 'Select the countries' ), 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                return esc_html( t( $item->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->select( [ 'id', 'name' ] )->selectKey( 'id' )->fetch( -1 ) ), 'value' => [], 'when' => [ '=', 'data[audience][country]', false ] ],
            'gender'    => [ 'type' => 'radio', 'label' => t( 'Gender' ), 'options' => [ 0 => t( 'Both' ), 1 => t( 'Male' ), 2 => t( 'Female' ) ], 'value' => 0 ],
            'any_age'   => [ 'type' => 'checkbox', 'label' => t( 'Age' ), 'title' => t( 'Any' ) ],
            'age'       => [ 'type' => 'checkboxes', 'label' => t( 'Select the age categories' ), 'options' => [ 1 => t( '18-24 years old' ), 2 => t( '25-34 years old' ), 3 => t( '35-44 years old' ), 4 => t( '45-54 years old' ), 5 => t( '55-64 years old' ), 6 => t( '65-74 years old' ) ], 'value' => [], 'when' => [ '=', 'data[audience][any_age]', false ] ],
        ] );

        $adminOpts = [];

        if( me()->isAdmin() ) {
            $adminOpts = [ 'type' => 'dropdown', 'label' => t( 'Admin options' ), 'grouped' => false, 'fields' => [
                'admin_lpoints' => [  'label' => t( 'Loyalty points' ), 'fields' => [
                    'lpoints'      => [ 'type' => 'number', 'label' => t( 'Loyalty points for each answer' ), 'value' => $collector->getLoyaltyPoints() ],
                ], 'grouped' => false ]
            ] ];
        }

        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'link'      => [ 'type' => 'inline-group', 'label' => t( 'URL' ), 'grouped' => false, 'fields' => [
                'link'  => [ 'type' => 'text', 'readonly' => 'readonly' ],
                [ 'type' => 'custom', 'callback' => function() use ( $collector ) {
                    return '<a href="#" data-copy="' . esc_url( $collector->getPermalink() ) . '" class="btn">' . t( 'Copy' ) . '</a>';
                }, 'classes' => 'wa mta' ] ]
            ],
            'type'      => [ 'type' => 'radio', 'label' => t( 'How to collect responses' ), 'options' => [ 1 => t( 'Buy responses' ), 0 => t( 'Use my own method (website, social networks, etc.)' ) ], 'value' => 0 ],
            'audience'  => [ 'type' => 'dropdown', 'label' => t( 'Audience' ), 'grouped' => false, 'fields' => [ 'audience' => [ 'label' => t( 'Target audience' ), 'fields' => $audience ] ], 'when' => [ '=', 'data[type]', 1 ] ],
            'price'     => [ 'type' => 'number', 'label' => t( 'Price per response' ), 'step' => '.01', 'min' => ( $min = ( !me()->isAdmin() ? (double) get_option( 'min_cpa' ) : 0 ) ), 'value' => $min, 'when' => [ '=', 'data[type]', 1 ] ],
            'advanced'  => [ 'type' => 'dropdown', 'label' => t( 'Advanced' ), 'grouped' => false, 'fields' => [
                'advanced' => [  'label' => t( 'Encryption' ), 'fields' => [
                    'encrypt' => [ 'type' => 'checkbox', 'title' => t( 'Encrypt key for tracking ID' ), 'description' => t( 'This will prevent people to modify tracking ID and you always get accurate tracking information' ) ],
                    [ 'type' => 'group', 'grouped' => false, 'fields' => [
                        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
                            'enckey'  => [ 'type' => 'text', 'label' => t( 'Encryption key' ), 'readonly' => 'readonly' ],
                            [ 'type' => 'custom', 'callback' => function() use ( $collector ) {
                                return '<a href="#" class="btn" data-ajax="manage-collector2" data-params=\'' . cms_json_encode( [ 'action2' => 'generate-key', 'collector' => $collector->getId() ] ) . '\'>' . t( 'Generate a new key' ) . '</a>';
                            }, 'classes' => 'wa mta' ] ]
                        ],
                        'allow_empty' => [ 'type' => 'checkbox', 'title' => t( 'Allow responders without tracking ID' ), 'description' => t( 'Validation works only with a tracking ID. Here you can allow responders to take your survey without a tracking ID or restrict the survey' ) ],
                    ], 'grouped' => false, 'when' => [ '=', 'data[encrypt]', true ] ],
                    'crlink' => [ 'type' => 'custom', 'callback' => function() use ( $collector ) {
                        return '<a href="#" class="btn" data-popup="manage-collector" data-options=\'' . ( cms_json_encode( [ 'action' => 'crlink', 'collector' => $collector->getId() ] ) ) . '\'>' . t( 'Create a link' ) . '</a>';
                    }, 'classes' => 'wa' ]
                ], 'grouped' => false ],
                'password' => [  'label' => t( 'Password' ), 'fields' => [
                    'use_password'  => [ 'type' => 'checkbox', 'title' => t( 'Protect this collector with a password' ), 'description' => t( 'Only responders that own this password will be able to answer the survey using this collector' ) ],
                    'password'      => [ 'type' => 'text', 'label' => t( 'Password' ), 'when' => [ '=', 'data[use_password]', true ] ]
                ], 'grouped' => false ]
            ], 'when' => [ '=', 'data[type]', 0 ] ],
            $adminOpts,
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $options = $collector->options();
        $setting = $collector->getSetting();

        $form->setValues( [
            'name'      => $collector->getName(),
            'type'      => $collector->getType(),
            'price'     => $collector->getCPA(),
            'link'      => $collector->getPermalink(),
            'enckey'    => md5( uniqId() ),
            'audience'  => [
                'country'   => ( isset( $options[1][0] ) ?: false ),
                'countries' => ( $options[1] ?? [] ),
                'gender'    => ( isset( $options[2] ) ? current( $options[2] ) : 0 ),
                'any_age'   => ( isset( $options[3][0] ) ?: false ),
                'age'       => ( $options[3] ?? [] ),
            ]
        ] );

        if( !empty( $setting['enckey'] ) ) {
            $form->setValues( [
                'encrypt'       => true,
                'enckey'        => $setting['enckey'],
                'allow_empty'   => $setting['allowe'] ?? false
            ] );  
        }

        if( !empty( $setting['password'] ) ) {
            $form->setValues( [
                'use_password'  => true,
                'password'      => $setting['password']
            ] );  
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-collector2', [ 'action2' => 'edit', 'collector' => $collector->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_collector" class="form edit_collector_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function crlink_collector( object $collector, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'enckey'    => [ 'type' => 'text', 'label' => t( 'Encryption key' ), 'required' => 'required' ],
            'trackId'   => [ 'type' => 'text', 'label' => t( 'Track ID' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Generate' ) ],
            'link'      => [ 'type' => 'textarea', 'label' => t( 'URL with encrypted key for the "trackId" parameter' ), 'readonly' => 'readonly' ],
        ] );
        
        $setting = $collector->getSetting();

        $form->setValues( [
            'enckey'    => ( $enckey = $setting['enckey'] ?? '' ),
            'trackId'   => 'test',
            'link'      => $collector->getPermalink() . '?key=' . md5( $enckey . 'test' ) . '&trackId=test'
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-collector2', [ 'action2' => 'crlink', 'collector' => $collector->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="crlink_collector" class="form crlink_collector_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'crlink_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function settings_survey( object $survey, array $attributes = [] ) {
        $fields = [
            'restart'   => [ 'type' => 'checkbox', 'label' => t( 'Expired responses' ), 'title' => t( 'Allow expired responses to restart' ), 'description' => t( 'By default expired responses cannot be reopened' ) ],
            'rtime'     => [ 'type' => 'number', 'label' => t( 'Survey time limit' ), 'description' => t( 'In minutes. This setting can be overridden by a rule defined in step settings' ) ],
            [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ];

        $meta   = $survey->meta();
        $form   = new \markup\front_end\form_fields( $fields );

        $form->setValues( [
            'restart'   => (bool) $meta->get( 'restart' ),
            'rtime'     => $meta->get( 'rtime', RESPONSE_TIME_LIMIT ),
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'settings', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="settings_survey" class="form settings_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'settings_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_logo_survey( object $survey, array $attributes = [] ) {
        $meta   = $survey->meta();
        $form   = new \markup\front_end\form_fields( [
            'logo' => [ 'type' => 'image', 'label' => t( 'Logo' ), 'description' => t( "It will be visible on survey's page. In header section" ), 'category' => 'survey-logo', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId() ],
            [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        if( ( $logoId = $meta->get( 'logo', false ) ) && ( $logoURL = mediaLinks( $logoId )->getItemURL() ) ) {
            $form->setValues( [
                'logo' => [ $logoId => $logoURL ]
            ] );
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'logo', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_logo_survey" class="form edit_logo_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_logo_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function meta_tags_survey( object $survey, array $attributes = [] ) {
        $fields = [
            'title' => [ 'type' => 'text', 'label' => t( 'Title' ) ],
            'desc'  => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
            'image' => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'survey-meta', 'identifierId' => $survey->getId(), 'ownerId' => $survey->getUserId() ],
            [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ];

        $meta   = $survey->meta();
        $form   = new \markup\front_end\form_fields( $fields );

        $form->setValues( [
            'title' => $meta->get( 'meta_title' ),
            'desc'  => $meta->get( 'meta_desc' )
        ] );

        if( ( $metaImageId = $meta->get( 'meta_image', false ) ) && ( $metaImageURL = mediaLinks( $metaImageId )->getItemURL() ) ) {
            $form->setValues( [
                'image' => [ $metaImageId => $metaImageURL ]
            ] );
        }

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'meta-tags', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="meta_tags_survey" class="form meta_tags_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'meta_tags_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function texts_survey( object $survey, array $attributes = [] ) {
        $fields = [
            'texts'     => [ 'type' => 'group', 'fields' => [
                'agree'     => [ 'type' => 'text', 'label' => t( 'Agree & take the survey' ) ],
                'start'     => [ 'type' => 'text', 'label' => t( 'Start survey' ) ],
                'next'      => [ 'type' => 'text', 'label' => t( 'Next' ) ],
                'prev'      => [ 'type' => 'text', 'label' => t( 'Previous' ) ],
                'rtime'     => [ 'type' => 'text', 'label' => t( 'Response time has expired' ) ],
                'restart'   => [ 'type' => 'text', 'label' => t( 'Restart survey' ) ],
                'rfield'    => [ 'type' => 'text', 'label' => t( 'This field is required' ) ],
                'fextension'=> [ 'type' => 'text', 'label' => t( 'File extension is not allowed' ) ],
                'ftoobig'   => [ 'type' => 'text', 'label' => t( 'This file is too big. Maximum allowed size: %s Mb' ) ],
                'iemail'    => [ 'type' => 'text', 'label' => t( 'This is not a valid email address' ) ]
            ] ],
            [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ];

        $meta   = $survey->meta();
        $form   = new \markup\front_end\form_fields( $fields );

        $form->setValues( [
            'texts' => $meta->getJson( 'texts', [] )
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'texts', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="texts_survey" class="form texts_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'texts_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function terms_of_use_survey( object $survey ) {
        $text       = $survey->meta()->get( 'tou' );
        $sh         = new \site\shortcodes( $text );
        $sh_list    = $sh->toArray();

        $form = new \markup\front_end\form_fields( [
            'content'   => [ 'type' => 'repeater', 'fields' => [
                [ 'type' => 'group', 'fields' => [
                    'content'   => [ 'type' => 'textarea', 'label' => t( 'Content' ) ],
                    'type'      => [ 'type' => 'select', 'options' => [ 'title' => t( 'Title' ), 'h' => t( 'Headline' ), 'p' => t( 'Paragraph' ), 'bigtext' => t( 'Big text paragraph' ) ] ]
                ], 'classes' => 'fl_bg', 'grouped' => false ]
            ], 'add_button' => t( 'Add content' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        if( empty( $sh_list ) )
        $sh_list = [ [ 'content' => '', 'type' => 'p', 'attrs' => '' ] ];

        $form->setValues( [
            'content'   => array_map( function( $v ) {
                return [ 'content' => html_decode( $v['content'] ), 'type' => $v['type'], 'attr' => $v['attrs'] ];
            }, $sh_list ),
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'terms-of-use', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="terms_of_use" class="form terms_of_use_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'terms_of_use_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function advanced_settings_survey( object $survey, array $attributes = [] ) {
        $markup = '<h3>' . t( 'Clear collectors' ) . '</h2>';

        $collectors = $survey->getCollectors();

        if( !$collectors->count() ) {
            $markup .= '<div class="msg alert mb40">' . t( 'There are no collectors for this survey') . '</div>';
        } else {

            $clt = [];

            foreach( $collectors->fetch( -1 ) as $collector ) {
                $collectors->setObject( $collector );
                $clt[$collectors->getId()] = esc_html( $collectors->getName() ) . ' (' . ( ( $responses = $collectors->getResults()->count() ) == 0 ? t( 'No responses' ) : ( $responses == 1 ? t( '1 response' ) : sprintf( t( '%s responses' ), $responses ) ) ) . ')';
            }

            $form = new \markup\front_end\form_fields( [
                'collectors'    => [ 'type' => 'checkboxes', 'options' => $clt, 'description' => t( 'All responses from collectors selected to clear will be permanently removed.' ), 'value' => [] ],
                'confirm'       => [ 'type' => 'checkbox', 'label' => t( 'I understand that all responses will be permanently deleted' ), 'title' => t( 'Confirmation' ), 'when' => [ 'NOT_EMPTY', 'data[collectors]', NULL ] ],
                'button'        => [ 'type' => 'button', 'label' => t( 'Clear' ), 'when' => [ [ '=', 'data[confirm]', true ], [ 'NOT_EMPTY', 'data[collectors]', NULL ] ] ]
            ] );
    
            $this->last_form            = $form;
            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'clear-collectors', 'survey' => $survey->getId() ] );
            $attributes['enctype']      = 'multipart/form-data';
    
            $markup .= '<form id="add_collector" class="form add_collector_form mb40" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

        }

        $markup .= '<h3>' . t( 'Transfer' ) . '</h2>';

        $form = new \markup\front_end\form_fields( [
            'tagreed'   => [ 'type' => 'checkbox', 'title' => t( 'Transfer this survey' ), 'description' => t( 'Transfer this survey and its data to another account. It will be removed from your account. All collaborators will be removed.' ) ],
            [ 'type' => 'group', 'grouped' => false, 'fields' => [
                'email'     => [ 'type' => 'text', 'label' => t( 'New account email address' ) ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Transfer' ) ]
            ], 'when' => [ [ '=', 'data[tagreed]', 1 ] ]
        ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'transfer-survey', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="add_collector" class="form add_collector_form mb40" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '<h3>' . t( 'Delete survey' ) . '</h2>';

        $form = new \markup\front_end\form_fields( [
            'dagreed'   => [ 'type' => 'checkbox', 'title' => t( 'Delete this survey' ), 'description' => t( 'This survey will be permanently deleted and all associated data will be lost forever.' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Delete this survey permanently' ), 'when' => [ '=', 'data[dagreed]', 1 ] ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'delete-survey', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="add_collector" class="form add_collector_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_collector_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function update_survey_budget( object $survey, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'amount'    => [ 'type' => 'number', 'label' => t( 'Budget' ), 'step' => '.01', 'min' => -$survey->getBudget(), 'max' => $this->user_obj->getBalance(), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'amount'    => min( 50, $this->user_obj->getBalance() ),
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'budget', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_survey" class="form add_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function survey_report( object $survey, int $position = NULL, bool $isNew = false, array $values = [], array $attributes = [] ) {
        $collectors = $survey->getCollectors();
        $steps      = $survey->getSteps();
        $l_steps    = [];
        $qAvailable = survey_types()->getTypesSummary();

        foreach( $steps->fetch( -1 ) as $step ) {
            $steps                      ->setObject( $step );
            $questions                  = $steps->getQuestions()
                                        ->setVisible( 2 );
            $l_steps[$step->id]['t']    = $steps->getName();
            $l_steps[$step->id]['id']   = $step->id;
            $l_steps[$step->id]['qs']   = array_map( function( $v ) {
                return esc_html( $v->title );
            }, array_filter( $questions->fetch( -1 ), function ( $v ) use( $qAvailable ) {
                return ( array_search( $v->type, $qAvailable ) !== false );
            } ) );
        }

        $s_fields   = [];

        array_map( function( $v ) use ( &$s_fields ) {
            $s_fields['s' . $v['id']]   = [ 'type' => 'custom', 'label' => esc_html( $v['t'] ) ];
            $s_fields['qs' . $v['id']]  = [ 'type' => 'checkboxes', 'options' => array_map( function( $v ) {
                return esc_html( $v );
            }, $v['qs'] ), 'classes' => 'fl_bg' ];
        }, $l_steps );

        $fields = [
            [ 'type' => 'dropdown', 'fields' => [ 'filters' => [ 'label' => t( 'Filters' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Collectors' ), 'fields' => [
                'collectors' => [ 'type' => 'checkboxes', 'options' => array_map( function( $v ) {
                    return esc_html( $v->name );
                }, $collectors->fetch( -1 ) ) ]
                ], 'grouped' => false ] ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'answers' => [ 'label' => t( 'Answers' ), 'fields' => [
                    'range' => [ 'type' => 'custom', 'callback' => function() use ( $survey, $values ) {
                        $markup = '
                        <div class="vqs">';
                        if( !empty( $values['q'] ) ) {
                            $questions  = questions();
                            $questions  ->markupView( 'filters' );
                            foreach( $values['q'] as $q => $value ) {
                                $questions  ->resetInfo()
                                            ->setId( $q );
                                if( $questions->getObject() && $questions->getSurveyId() == $survey->getId() ) {
                                    $markup .= $questions->markup( $value );
                                }
                            }
                        }
                        $markup .= '
                         </div>
                         
                         <div class="form_line add_button">
                            <a href="#" data-before="cms_set_questions_form_values" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'select-questions', 'survey' => $survey->getId() ] ) ) . '\'>' . t( 'Manage questions' ) . '</a>
                        </div>';
    
                        return $markup;
                    } ] ] ] 
                ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'countries' => [ 'label' => t( 'Countries' ), 'fields' => [
                    'countries' => [ 'type' => 'checkboxes', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                        return esc_html( t( $item->name ) );
                    }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ), 'value' => [] ]
                    ], 'grouped' => false ]
                ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'date_range' => [ 'label' => t( 'Time period' ), 'fields' => [
                    'type'  => [ 'type' => 'select', 'options' => [ 0 => t( 'Choose' ), '12hours' => t( 'Last 12 hours' ), '24hours' => t( 'Last 24 hours' ), '7days' => t( 'Last 7 days' ), '30days' => t( 'Last 30 days' ), 'range' => t( 'Range' ) ] ],
                    [ 'type' => 'inline-group', 'fields' => [
                        'from'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'From' ) ],
                        'to'    => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'To' ) ]
                    ], 'grouped' => false, 'when' => [ '=', 'data[date_range][type]', 'range' ] ]
                    ] ] 
                ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'response_time' => [ 'label' => t( 'Response time' ), 'fields' => [
                    [ 'type' => 'inline-group', 'fields' => [
                        'opr'   => [ 'type' => 'select', 'options' => [ 'g' => t( 'Greater than' ), 'l' => t( 'Less than' ) ] ],
                        'val'   => [ 'type' => 'number', 'min' => 1 ],
                        'int'   => [ 'type' => 'select', 'options' => [ 's' => t( 'Seconds' ), 'm' => t( 'Minutes' ), 'h' => t( 'Hours' ) ] ]
                    ], 'grouped' => false ] ] ] 
                ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'points' => [ 'label' => t( 'Points' ), 'fields' => [
                    [ 'type' => 'inline-group', 'fields' => [
                        'min'   => [ 'type' => 'number', 'label' => t( 'Min' ),'min' => 0, 'value' => '' ],
                        'max'   => [ 'type' => 'number', 'label' => t( 'Max' ),'min' => 0, 'value' => '' ]
                    ], 'grouped' => false ] ] ]
                ], 'grouped' => false ],

                'tids' => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Tracking id' ), 'fields' => [
                        'tid'  => [ 'type' => 'text', 'placeholder' => t( 'Tracking id' ) ],
                    ], 'grouped' => false ] 
                ] ],

                [ 'type' => 'dropdown', 'fields' => [ 'variables' => [ 'label' => t( 'Variables' ), 'fields' => [
                    'variables' => [ 'type' => 'repeater', 'fields' => [
                        [ 'type' => 'inline-group', 'fields' => [
                            'text'  => [ 'type' => 'text', 'placeholder' => t( 'Variable' ) ],
                            'find'      => [ 'type' => 'select', 'options' => [ 'any' => t( 'Find anywhere' ) ], 'classes' => 'wa' ]
                        ], 'grouped' => false ]
                    ], 'add_button' => t( 'Add variable' ) ]
                    ], 'grouped' => false ] 
                ], 'grouped' => false ],

                [ 'type' => 'dropdown', 'fields' => [ 'labels' => [ 'label' => t( 'Labels' ), 'fields' => [
                    'label' => [ 'type' => 'checkboxes', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                        return esc_html( t( $item->name ) );
                    }, $survey->getLabels()->fetch( -1 ) ), 'value' => [] ]
                    ], 'grouped' => false ]
                ], 'grouped' => false ],
            ], 'grouped' => false ] ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'show' => [ 'label' => t( 'Show' ), 'fields' => $s_fields ] ], 'grouped' => false ],
            'save'  => [ 'type' => 'checkbox', 'title' => t( 'Save the report for later viewing and analysis' ) ],
            'name'  => [ 'type' => 'text', 'label' => t( 'Name' ), 'classes' => 'reportInput', 'value' => t( 'My report' ), 'when' => [ '=', 'data[save]', true ] ],
        ];

        if( $position ) {
            $fields['tpos']         = [ 'type' => 'hidden', 'value' => $position ];
            $fields['newReport']    = [ 'type' => 'hidden', 'value' => $isNew ];
        }

        $fields['btn']              = [ 'type' => 'button', 'label' => t( 'Generate report' ) ];
        $form                       = new \markup\front_end\form_fields( $fields );
        $form                       ->setValues( $values );
        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'generate-report', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_generate_report" class="form survey_generate_report_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_generate_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function survey_reports( object $survey, array $attributes = [] ) {
        $reports    = new \query\survey\saved_reports;
        $reports    ->setSurveyId( $survey->getId() )
                    ->setUserId( $this->user_obj->getId() )
                    ->setSaved()
                    ->select( [ 'id', 'title' ] )
                    ->orderBy( 'id_desc' );

        $fields     = [
            'report'  => [ 'type' => 'select', 'label' => t( 'Report' ), 'options' => ( [ '' => t( 'Select a report' ) ] + array_map( function( $v ) {
                return esc_html( $v->title );
            }, $reports->fetch( -1 ) ) ) ],
        ];

        $fields['btn']              = [ 'type' => 'button', 'label' => t( 'View report' ), 'when' => [ 'NOT_EMPTY', 'data[report]' ] ];
        $form                       = new \markup\front_end\form_fields( $fields );
        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'view-report', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_reports" class="form survey_reports_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_reports_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function advanced_responses( object $survey, array $values = [], array $attributes = [] ) {
        $collectors = $survey->getCollectors();
        $steps      = $survey->getSteps();
        $l_steps    = [];

        foreach( $steps->fetch( -1 ) as $step ) {
            $steps                      ->setObject( $step );
            $questions                  = $steps->getQuestions();
            $l_steps[$step->id]['t']    = $steps->getName();
            $l_steps[$step->id]['id']   = $step->id;
            $l_steps[$step->id]['qs']   = array_map( function( $v ) {
                return $v->title;
            }, $questions->fetch( -1 ) );
        }

        $s_fields   = [];

        array_map( function( $v ) use ( &$s_fields ) {
            $s_fields[$v['id']]  = [ 'type' => 'checkbox', 'title' => esc_html( $v['t'] ), 'value' => true ];
            $s_fields[] = [ 'type' => 'checkboxes', 'options' => array_map( function( $v ) {
                return esc_html( $v );
            }, $v['qs'] ), 'when' => [ '=', 'data[show][' . $v['id'] . ']', false ], 'classes' => 'fl_bg' ];
        }, $l_steps );

        $fields = [
            [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Collectors' ), 'fields' => [
            'collectors' => [ 'type' => 'checkboxes', 'options' => array_map( function( $v ) {
                return esc_html( $v->name );
            }, $collectors->fetch( -1 ) ) ]
            ], 'grouped' => false ] ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'answers' => [ 'label' => t( 'Answers' ), 'fields' => [
                'range' => [ 'type' => 'custom', 'callback' => function() use ( $survey, $values ) {
                    $markup = '
                    <div class="vqs">';
                    if( !empty( $values['q'] ) ) {
                        $questions  = questions();
                        $questions  ->markupView( 'filters' );
                        foreach( $values['q'] as $type => $values ) {
                            foreach( $values as $q => $value ) {
                                $questions  ->resetInfo()
                                            ->setId( $q );
                                if( $questions->getObject() && $questions->getSurveyId() == $survey->getId() ) {
                                    $markup .= $questions->markup( $value );
                                }
                            }
                        }
                    }
                    $markup .= '
                     </div>
                     
                     <div class="form_line add_button">
                        <a href="#" data-before="cms_set_questions_form_values" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'select-questions', 'survey' => $survey->getId() ] ) ) . '\'>' . t( 'Manage questions' ) . '</a>
                    </div>';

                    return $markup;
                } ] ] ] 
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'countries' => [ 'label' => t( 'Countries' ), 'fields' => [
                'countries' => [ 'type' => 'checkboxes', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                    return esc_html( t( $item->name ) );
                }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ), 'value' => [] ]
                ], 'grouped' => false ]
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'date_range' => [ 'label' => t( 'Time period' ), 'fields' => [
                'type'  => [ 'type' => 'select', 'options' => [ 0 => t( 'Choose' ), '12hours' => t( 'Last 12 hours' ), '24hours' => t( 'Last 24 hours' ), '7days' => t( 'Last 7 days' ), '30days' => t( 'Last 30 days' ), 'range' => t( 'Range' ) ] ],
                [ 'type' => 'inline-group', 'fields' => [
                    'from'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'From' ) ],
                    'to'    => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'To' ) ]
                ], 'grouped' => false, 'when' => [ '=', 'data[date_range][type]', 'range' ] ]
                ] ] 
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'response_time' => [ 'label' => t( 'Response time' ), 'fields' => [
                [ 'type' => 'inline-group', 'fields' => [
                    'opr'   => [ 'type' => 'select', 'options' => [ 'g' => t( 'Greater than' ), 'l' => t( 'Less than' ) ] ],
                    'val'   => [ 'type' => 'number', 'min' => 1 ],
                    'int'   => [ 'type' => 'select', 'options' => [ 's' => t( 'Seconds' ), 'm' => t( 'Minutes' ), 'h' => t( 'Hours' ) ] ]
                ], 'grouped' => false ] ] ] 
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'points' => [ 'label' => t( 'Points' ), 'fields' => [
                [ 'type' => 'inline-group', 'fields' => [
                    'min'   => [ 'type' => 'number', 'label' => t( 'Min' ),'min' => 0, 'value' => '' ],
                    'max'   => [ 'type' => 'number', 'label' => t( 'Max' ),'min' => 0, 'value' => '' ]
                ], 'grouped' => false ] ] ]
            ], 'grouped' => false ],

            'tids' => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Tracking id' ), 'fields' => [
                    'tid'  => [ 'type' => 'text', 'placeholder' => t( 'Tracking id' ) ],
                ], 'grouped' => false ] 
            ] ],

            [ 'type' => 'dropdown', 'fields' => [ 'variables' => [ 'label' => t( 'Variables' ), 'fields' => [
                'variables' => [ 'type' => 'repeater', 'fields' => [
                    [ 'type' => 'inline-group', 'fields' => [
                        'text'  => [ 'type' => 'text', 'placeholder' => t( 'Variable' ) ],
                        'find'      => [ 'type' => 'select', 'options' => [ 'any' => t( 'Find anywhere' ) ], 'classes' => 'wa' ]
                    ], 'grouped' => false ]
                ], 'add_button' => t( 'Add variable' ) ]
                ], 'grouped' => false ]
            ], 'grouped' => false ],

            [ 'type' => 'dropdown', 'fields' => [ 'labels' => [ 'label' => t( 'Labels' ), 'fields' => [
                'label' => [ 'type' => 'checkboxes', 'classes' => 'chbm200', 'options' => array_map( function( $item ) {
                    return esc_html( t( $item->name ) );
                }, $survey->getLabels()->fetch( -1 ) ), 'value' => [] ]
                ], 'grouped' => false ]
            ], 'grouped' => false ]
        ];

        $fields['btn']              = [ 'type' => 'button', 'label' => t( 'Save' ) ];
        $form                       = new \markup\front_end\form_fields( $fields );
        $form                       ->setValues( $values );
        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'results-filter', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_generate_report" class="form survey_generate_report_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_generate_report_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function survey_filter_questions( object $survey, array $values = [], array $attributes = [] ) {
        $steps      = $survey->getSteps();
        $l_steps    = [];

        foreach( $steps->fetch( -1 ) as $step ) {
            $steps                      ->setObject( $step );
            $questions                  = $steps->getQuestions()
                                        ->setVisible( 2 );
            $l_steps[$step->id]['t']    = $steps->getName();
            $l_steps[$step->id]['id']   = $step->id;
            $l_steps[$step->id]['qs']   = array_map( function( $v ) {
                return $v->title;
            }, $questions->fetch( -1 ) );
        }

        $s_fields   = [];

        array_map( function( $v ) use ( &$s_fields ) {
            $s_fields[$v['id']]  = [ 'type' => 'checkbox', 'title' => esc_html( $v['t'] ), 'value' => true ];
            $s_fields[] = [ 'type' => 'checkboxes', 'options' => array_map( function( $v ) {
                return esc_html( $v );
            }, $v['qs'] ), 'when' => [ '=', 'data[show][' . $v['id'] . ']', false ], 'classes' => 'fl_bg' ];
        }, $l_steps );

        $fields = [
            [ 'type' => 'custom2', 'callback' => function() use ( $l_steps, $values ) {
                $markup = '';
                foreach( $l_steps as $step ) {
                    $qss    = count( $step['qs'] );
                    if( $qss ) {
                        $markup .= '<div class="form_line">
                        <h4 class="mb20">' . esc_html( $step['t'] ) . '</h4>';

                        $markup .= '
                        <div class="form_line checkbox checkboxes fl_bg">
                            <div class="chbxes">
                                <div>';
                                foreach( $step['qs'] as $qId => $qTitle ) {
                                    $markup .= '
                                    <div>
                                        <input type="checkbox" name="data[q][' . $qId . ']" id="data[q][' . $qId . ']" value="' . $qId . '"' . ( array_search( $qId, $values ) !== false ? ' checked' : '' ) . ' />
                                        <label for="data[q][' . $qId . ']">' . esc_html( $qTitle ) . '</label>
                                    </div>';
                                }
                                $markup .= '
                                </div>';
                                if( $qss > 10 ) {
                                    $markup .= '
                                    <div>
                                        <input type="text" placeholder="' . t( 'Search' ) . '" data-search>
                                    </div>';
                                }
                            $markup .= '
                            </div>
                        </div>';

                        $markup .= '</div>';
                    }
                }
                return $markup;
            } ]
        ];

        $fields['btn']              = [ 'type' => 'button', 'label' => t( 'Save' ) ];
        $form                       = new \markup\front_end\form_fields( $fields );
        $form                       ->setValues( $values );
        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'select-questions', 'survey' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="survey_filter_questions" class="form survey_filter_questions_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'survey_filter_questions_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function create_team( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'create-team' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_team" class="form add_team_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_team_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function identity_verification( array $attributes = [] ) {
        $docs   = get_option( 'kyc_settings' );
        $docs   = json_decode( $docs, true );
        $twos   = $docs['handhelp'] ?? false;
        $docs   = $docs['langs'][getLanguage()['locale_e']] ?? [];
        $fields = [
            'document'  => [ 'type' => 'custom', 'label' => t( 'Document' ), 'description' => t( 'Any of the following documents' ), 'callback' => function() use ( $docs ) {
                return '<ul class="def">' . implode( "\n", array_map( function( $v ) {
                    return '<li>' . esc_html( $v['doc'] ) . '</li>';
                }, $docs ) ) . '</ul>';
            }, 'required' => 'required' ],
            'doc_img'   => [ 'type' => 'image', 'category' => 'kyc' ]
        ];
        if( $twos )
        $fields['selfie']   = [ 'type' => 'image', 'label' => t( 'Selfie picture holding the document'), 'category' => 'kyc' ];
        $fields['button']   = [ 'type' => 'button', 'label' => t( 'Send' ) ];
        $form   = new \markup\front_end\form_fields( $fields );

        $this->last_form            = $form;
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'identity-verification' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="identity_verification" class="form identity_verification_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'identity_verification_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

}
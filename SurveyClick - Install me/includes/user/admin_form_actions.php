<?php

namespace user;

class admin_form_actions extends \util\db {

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

    public function add_user( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-user-admin-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-user-admin', true, $this->user, $data ) )
            return ; 
        else if( !isset( $data['username' ] ) || !isset( $data['full_name'] ) || !isset( $data['address'] ) || !isset( $data['email'] ) || empty( $data['password'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filter_var( $data['email'], FILTER_VALIDATE_EMAIL ) )
            throw new \Exception( t( 'Invalid email address' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9]{3,50}$/iu', $data['username'] ) )
            throw new \Exception( t( 'Invalid username' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9 ]{3,50}$/iu', $data['full_name'] ) )
            throw new \Exception( t( 'Invalid name' ) );

        $myPerms = $this->user_obj->isOwner() ? [ 'user' => 0, 'moderator' => 1, 'administrator' => 2, 'owner' => 3 ] : ( $this->user_obj->isAdmin() ? [ 'user' => 0, 'moderator' => 1 ] : [] );

        if( !isset( $data['role'] ) || !isset( $myPerms[$data['role']] ) )
            throw new \Exception( t( "Unexpected" ) );

        $surveyor   = isset( $data['more']['sur'] );
        $emailVerif = isset( $data['more']['valid'] );
        $persVerif  = isset( $data['more']['verified'] );

        if( isset( $data['country'] ) ) {
            $countries  = new \query\countries( $data['country'] );

            if( $countries->getObject() ) {
                $country_id = $countries->getIso3166();
                $timezones  = $countries->getTimezones();
                $timezone   = key( $timezones );
            }
        }

        $birthday   = isset( $data['year'] ) && isset( $data['month'] ) && isset( $data['day'] ) ? implode( '-', [ $data['year'], $data['month'], $data['day'] ] ) : [ 2000, 1, 1 ];
        $gender     = isset( $data['gender'] ) && in_array( $data['gender'], [ 'M', 'F' ] ) ? $data['gender'] : 'M';

        $fields = filters()->do_filter( 'form:fields:add-user-admin', [
            'avatar'    => [ 'type' => 'image', 'category' => 'user-avatar' ]
        ], $this->user );

        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->build();

        $media  = $form->uploadFiles( $data );

        if( isset( $media['data[avatar]'] ) )
        $avatar = key( $media['data[avatar]'] );

        $query  = 'INSERT INTO ' . $this->table( 'users' );
        $query .= ' (name, full_name, birthday, gender, email, password, avatar, address, valid, country, perm, surveyor, verified, tz) VALUES (?, ?, ?, ?, ?, MD5(?), ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ssssssisisiiis', $data['username'], $data['full_name'], $birthday, $gender, $data['email'], $data['password'], $avatar, $data['address'], $emailVerif, $country_id, $myPerms[$data['role']], $surveyor, $persVerif, $timezone );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            if( !empty( $data['sendemail'] ) ) {
                try {
                    $mail = $this->user_obj->mail( 'new_account' )
                        ->useDefaultShortcodes()
                        ->setShortcodes( [
                            '%NAME%'        => $data['full_name'],
                            '%USERNAME%'    => $data['username'],
                            '%PASSWORD%'    => $data['password']
                        ] );

                    try {
                        $mail->send( [ $data['email'] ] );
                    }
                    catch( \Exception $e ) { }
                }
                catch( \Exception $e ) { }
            }

            // save action
            $this->user_obj->admin_actions()->save_action( $id, 'adduser' );

            actions()->do_action( 'after:add-user-admin', $id, $data, $media );

            return true;
        }

        throw new \Exception( t( 'Username or email already exists' ) );
    }

    public function edit_user( int $user, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-user-admin-form-sanitize-data', $data );
        $user   = new \query\users( $user );
        if( !$user->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-edit-user-admin', true, $user, $this->user, $data ) )
            return ; 
        else if( !isset( $data['username' ] ) || !isset( $data['full_name'] ) || !isset( $data['address'] ) || !isset( $data['email'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( !filter_var( $data['email'], FILTER_VALIDATE_EMAIL ) )
            throw new \Exception( t( 'Invalid email address' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9]{3,50}$/iu', $data['username'] ) )
            throw new \Exception( t( 'Invalid username' ) );
        else if( !preg_match( '/^[\p{Cyrillic}\p{Latin}0-9 ]{3,50}$/iu', $data['full_name'] ) )
            throw new \Exception( t( 'Invalid name' ) );
        else if( $user->getPerm() >= $this->user_obj->getPerm() )
            throw new \Exception( t( "Your can't edit this profile" ) );

        $myPerms = $this->user_obj->isOwner() ? [ 'user' => 0, 'moderator' => 1, 'administrator' => 2, 'owner' => 3 ] : ( $this->user_obj->isAdmin() ? [ 'user' => 0, 'moderator' => 1 ] : [] );

        if( !isset( $data['role'] ) || !isset( $myPerms[$data['role']] ) )
            throw new \Exception( t( "Unexpected" ) );

        $userId     = $user->getId();
        $surveyor   = isset( $data['more']['sur'] );
        $emailVerif = isset( $data['more']['valid'] );
        $persVerif  = isset( $data['more']['verified'] );
        $country_id = $user->getCountryId();
        $timezone   = $user->getTz();

        if( isset( $data['country'] ) ) {
            $countries  = new \query\countries( $data['country'] );

            if( $countries->getObject() && $country_id != $countries->getIso3166() ) {
                $country_id = $countries->getIso3166();
                $timezones  = $countries->getTimezones();
                $timezone   = key( $timezones );
            }
        }

        // Only inputs that can deal with media
        $avatar = $user->getAvatar() ? [ $user->getAvatar() => $user->getAvatarURL() ] : NULL;
        $fields = filters()->do_filter( 'form:fields:edit-user-admin', [
            'avatar'    => [ 'type' => 'image', 'category' => 'user-avatar' ]
        ], $user );

        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->setValues( filters()->do_filter( 'form:values:edit-user-admin', [
            'avatar'  => $avatar,
        ], $user, $data ) );
        $form   ->build();

        $media  = $form->uploadFiles( $data );

        if( isset( $media['data[avatar]'] ) )
        $media_avatar = key( $media['data[avatar]'] );

        $birthday   = isset( $data['year'] ) && isset( $data['month'] ) && isset( $data['day'] ) ? implode( '-', [ $data['year'], $data['month'], $data['day'] ] ) : $user->getBirthday();
        $gender     = isset( $data['gender'] ) && in_array( $data['gender'], [ 'M', 'F' ] ) ? $data['gender'] : $user->getGender();

        $query  = 'UPDATE ' . $this->table( 'users' );
        $query .= ' SET name = ?, full_name = ?, birthday = ?, gender = ?, email = ?, avatar = ?, address = ?, valid = ?, country = ?, perm = ?, surveyor = ?, verified = ?, tz = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sssssssisiiisi', $data['username'], $data['full_name'], $birthday, $gender, $data['email'], $media_avatar, $data['address'], $emailVerif, $country_id, $myPerms[$data['role']], $surveyor, $persVerif, $timezone, $userId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $userId, 'edituser' );

            actions()->do_action( 'after:edit-user-admin', $user, $data, $media );

            return true;
        }

        throw new \Exception( t( 'Username or email already exists' ) );
    }

    public function send_alert( int $user, array $data ) {
        $data   = filters()->do_filter( 'send-alert-admin-form-sanitize-data', $data );
        $user   = new \query\users( $user );
        if( !$user->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-send-alert-admin', true, $user, $this->user, $data ) )
            return ; 
        else if( !isset( $data['title' ] ) || !isset( $data['text' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $userId = $user->getId();

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'alerts' );
        $query .= ' SET user = ?, text = JSON_OBJECT("type", "msg", "title", ?, "txt", ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iss', $userId, $data['title'], $data['text'] );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:send-alert-admin', $this->user, $user, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function ban_user( int $user, array $data ) {
        $data   = filters()->do_filter( 'ban-user-admin-form-sanitize-data', $data );
        $user   = new \query\users( $user );
        if( !$user->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-ban-user-admin', true, $user, $this->user, $data ) )
            return ; 
        else if( !isset( $data['date' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( $user->getPerm() >= $this->user_obj->getPerm() )
            throw new \Exception( t( "Your can't edit this profile" ) );

        $userId = $user->getId();
        $exp    = custom_time( $data['date'], -5 );

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET ban = FROM_UNIXTIME(?) WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $exp, $userId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $userId, 'ban', [ 
                'expiration'    => $exp
            ] );

            actions()->do_action( 'after:ban-user-admin', $this->user, $user, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function change_password( int $user, array $data ) {
        $data   = filters()->do_filter( 'change-password-admin-form-sanitize-data', $data );
        $user   = new \query\users( $user );
        if( !$user->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        $pass   = isset( $data['password'] ) ? trim( $data['password'] ) : '';
        $email  = isset( $data['sendmail'] );

        if( !filters()->do_filter( 'custom-error-change-password-admin', true, $user, $this->user, $data ) )
            return ; 
        if( strlen( $pass ) < 4 )
            throw new \Exception( t( 'New password is too short' ) );
        else if( $user->getPerm() >= $this->user_obj->getPerm() )
            throw new \Exception( t( "Your can't edit this profile" ) );

        $userId = $user->getId();

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET password = MD5(?) WHERE id = ?';

        $details = serialize( $data );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $pass, $userId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $userId, 'passchanged' );

            actions()->do_action( 'after:change-password-admin', $this->user, $user, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function user_balance( int $user, array $data ) {
        $data   = filters()->do_filter( 'user-balance-admin-form-sanitize-data', $data );
        $user   = new \query\users( $user );
        if( !$user->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-user-balance-admin', true, $user, $this->user, $data ) )
            return ; 
        if( !isset( $data['balance' ] ) || !isset( $data['bonus'] ) )
            throw new \Exception( t( 'Something went wrong' ) );
        else if( $user->getPerm() >= $this->user_obj->getPerm() )
            throw new \Exception( t( "Your can't edit this profile" ) );

        $userId = $user->getId();

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET balance = ?, bonus = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ddi', $data['balance'], $data['bonus'], $userId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $userId, 'ubalance', [ 
                'balance_old'   => $user->getRealBalance(), 
                'balance_new'   => $data['balance'],
                'bonus_old'     => $user->getBonus(),
                'bonus_new'     => $data['bonus']
            ] );

            actions()->do_action( 'after:update-user-balance-admin', $this->user, $user, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_team( int $team, array $data ) {
        $data   = filters()->do_filter( 'edit-team-admin-form-sanitize-data', $data );
        $team   = new \query\team\teams( $team );
        if( !$team->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        $name   = isset( $data['name'] ) ? trim( $data['name'] ) : '';

        if( !filters()->do_filter( 'custom-error-edit-team-admin', true, $team, $this->user, $data ) )
            return ; 
        if( empty( $name ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $teamId = $team->getId();

        $query  = 'UPDATE ';
        $query .= $this->table( 'teams' );
        $query .= ' SET name = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $name, $teamId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $teamId, 'editteam' );

            actions()->do_action( 'after:edit-team-admin', $this->user, $team, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_survey_status( int $survey, array $data ) {
        $data   = filters()->do_filter( 'edit-survey-status-admin-form-sanitize-data', $data );
        $survey = surveys( $survey );
        if( !$survey->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( isset( $data['status'] ) && in_array( (int) $data['status'], range( 0, 5 ) ) )
        $status = $data['status'];

        if( !filters()->do_filter( 'custom-error-edit-survey-status-admin', true, $survey, $this->user, $data ) )
            return ; 
        if( !isset( $status ) )
            throw new \Exception( t( 'Something went wrong' ) );

        if( $survey->getStatus() == $status )
        return true;

        $surveyId   = $survey->getId();
        $issueInv   = $status == 5 && $survey->getBudgetSpent() > 0 && ( $userObj = $survey->getUser() ) && $userObj->getObject();
        $spent      = $issueInv ? 0 : $survey->getBudgetSpent();

        $query  = 'UPDATE ';
        $query .= $this->table( 'surveys' );
        $query .= ' SET status = ?, spent = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'idi', $status, $spent, $surveyId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $surveyId, 'editsurveystatus', [
                'id'    => $survey->getId(),
                'old'   => $survey->getStatus(),
                'new'   => $status
            ] );

            if( $issueInv ) {
                // Add invoice & receipt
                $invoicing  = $userObj->invoicing();
                $invoicing  ->newInvoice( [
                    [ '%survey_label%', 1, $survey->getBudgetSpent() ],
                ] )         
                            ->setType( 'survey' )
                            ->createInvoice( true );
            }

            actions()->do_action( 'after:edit-survey-status-admin', $this->user, $survey, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_shop_category( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-shop-category-admin-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-category-admin', true, $this->user, $data ) )
            return ; 
        else if( empty( $data['name' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'INSERT INTO ' . $this->table( 'shop_categories' );
        $query .= ' (user, name, country, status) VALUES (?, ?, ?, ?)';

        $status = isset( $data['status'] ) ?: NULL;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'issi', $this->user, $data['name'], $data['country'], $status );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $id, 'addshopcategory' );

            actions()->do_action( 'after:add-shop-category-admin', $id, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_shop_category( object $category, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-shop-category-admin-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-category-admin', true, $this->user, $category, $data ) )
            return ; 
        else if( empty( $data['name' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'UPDATE ' . $this->table( 'shop_categories' );
        $query .= ' SET user = ?, name = ?, country = ?, status = ? WHERE id = ?';

        $category_id = $category->getId();
        $status = isset( $data['status'] ) ?: NULL;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'issii', $this->user, $data['name'], $data['country'], $status, $category_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $category, 'editshopcategory' );

            actions()->do_action( 'after:edit-shop-category-admin', $category, $category, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_shop_item( array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'add-shop-item-admin-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-add-user-admin', true, $this->user, $data ) )
            return ; 
        else if( empty( $data['name' ] ) || empty( $data['price'] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'INSERT INTO ' . $this->table( 'shop_items' );
        $query .= ' (user, name, description, stock, price, country, status) VALUES (?, ?, ?, ?, ?, ?, ?)';

        $status = isset( $data['status'] ) ?: NULL;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'issiisi', $this->user, $data['name'], $data['desc'], $data['stock'], $data['price'], $data['country'], $status );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;

        if( $e ) {
            // Only inputs that can deal with media
            $fields = filters()->do_filter( 'form:fields:add-shop-item-admin', [
                'media' => [ 'type' => 'image', 'category' => 'shop_item' ]
            ], $id );

            $form   = new \markup\front_end\form_fields( $fields );
            $form   ->build();

            $media  = $form->uploadFiles( $data );

            if( count( $media['data[media]'] ) ) {
                $media = key( $media['data[media]'] );
                $query  = 'UPDATE ' . $this->table( 'shop_items' );
                $query .= ' SET media = ? WHERE id = ?';
        
                $stmt->prepare( $query );
                $stmt->bind_param( 'ii', $media, $id );
                $stmt->execute();
            }

            // Save categories
            if( !empty( $data['category'] ) ) {
                foreach( (array) $data['category'] as $category ) {
                    $query  = 'INSERT INTO ' . $this->table( 'shop_category_items' );
                    $query .= ' (category, item) VALUES (?, ?)';
            
                    $stmt->prepare( $query );
                    $stmt->bind_param( 'ii', $category, $id );
                    $stmt->execute();
                }
            }

            // save action
            $this->user_obj->admin_actions()->save_action( $id, 'addshopitem' );

            actions()->do_action( 'after:add-shop-item-admin', $id, $data, $media );

            $stmt->close();

            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_shop_item( object $item, array $data ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $data   = filters()->do_filter( 'edit-shop-item-admin-form-sanitize-data', $data );

        if( !filters()->do_filter( 'custom-error-edit-item-admin', true, $this->user, $item, $data ) )
            return ; 
        else if( empty( $data['name' ] ) )
            throw new \Exception( t( 'Something went wrong' ) );

        $query  = 'UPDATE ' . $this->table( 'shop_items' );
        $query .= ' SET user = ?, name = ?, description = ?, stock = ?, price = ?, country = ?, status = ? WHERE id = ?';

        $item_id    = $item->getId();
        $status     = isset( $data['status'] ) ?: NULL;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'issiisii', $this->user, $data['name'], $data['desc'], $data['stock'], $data['price'], $data['country'], $status, $item_id );
        $e  = $stmt->execute();

        if( $e ) {
            // Only inputs that can deal with media
            $fields = filters()->do_filter( 'form:fields:edit-shop-item-admin', [
                'media' => [ 'type' => 'image', 'category' => 'shop_item' ]
            ], $item );

            $media  = $item->getMedia() ? [ $item->getMedia() => $item->getMediaURL() ] : NULL;

            $form   = new \markup\front_end\form_fields( $fields );
            $form   ->setValues( filters()->do_filter( 'form:values:edit-shop-item-admin', [
                'media'  => $media,
            ], $item, $data ) );
            $form   ->build();

            $media  = $form->uploadFiles( $data );

            if( count( $media['data[media]'] ) ) {
                $media  = key( $media['data[media]'] );
                $query  = 'UPDATE ' . $this->table( 'shop_items' );
                $query  .= ' SET media = ? WHERE id = ?';
        
                $stmt->prepare( $query );
                $stmt->bind_param( 'ii', $media, $item_id );
                $stmt->execute();
            } else if( $item->getMedia() ) {
                $query  = 'UPDATE ' . $this->table( 'shop_items' );
                $query  .= ' SET media = NULL WHERE id = ?';
        
                $stmt->prepare( $query );
                $stmt->bind_param( 'i', $item_id );
                $stmt->execute();
            }

            $categories = $item->getCategories()
            ->select( [ 'category' ] )
            ->selectKey( 'category' )
            ->fetch( -1 );
            $deleted    = [];
            $added      = [];

            if( !empty( $data['category'] ) ) {
                $deleted    = array_diff_key( $categories, $data['category'] );
                $added      = array_diff_key( $data['category'], $categories );

                // Save categories
                if( !empty( $data['category2'] ) ) {
                    foreach( (array) $data['category'] as $category ) {
                        $query  = 'INSERT INTO ' . $this->table( 'shop_category_items' );
                        $query .= ' (category, item) VALUES (?, ?)';
                
                        $stmt->prepare( $query );
                        $stmt->bind_param( 'ii', $category, $id );
                        $e  = $stmt->execute();
                    }
                }
            } else if( !empty( $categories ) )
                $deleted    = $categories;

            if( !empty( $added ) ) {
                $query  = 'INSERT INTO ' . $this->table( 'shop_category_items' );
                $query .= ' (category, item) VALUES (?, ?)';
        
                $stmt->prepare( $query );

                foreach( $added as $catId => $cat ) {
                    $stmt->bind_param( 'ii', $catId, $item_id );
                    $stmt->execute();
                }
            }

            if( !empty( $deleted ) ) {
                $query  = 'DELETE FROM ' . $this->table( 'shop_category_items' );
                $query .= ' WHERE category = ? AND item = ?';
        
                $stmt->prepare( $query );
                
                foreach( $deleted as $catId => $cat ) {
                    $stmt->bind_param( 'ii', $catId, $item_id );
                    $stmt->execute();
                }
            }

            // save action
            $this->user_obj->admin_actions()->save_action( $item_id, 'editshopitem' );

            actions()->do_action( 'after:edit-shop-item-admin', $item, $data, $media );

            $stmt->close();

            return true;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_shop_order_status( int $order, array $data ) {
        $data   = filters()->do_filter( 'edit-shop-order-status-admin-form-sanitize-data', $data );
        $order  = new \query\shop\orders( $order );
        if( !$order->getObject() )
        throw new \Exception( t( 'Unexpected' ) );

        if( isset( $data['status'] ) && in_array( (int) $data['status'], range( 0, 2 ) ) )
        $status = $data['status'];

        if( !filters()->do_filter( 'custom-error-edit-shop-order-status-admin', true, $order, $this->user, $data ) )
            return ; 
        if( !isset( $status ) )
            throw new \Exception( t( 'Something went wrong' ) );

        if( $order->getStatus() == $status )
        return true;

        $orderId   = $order->getId();

        $query  = 'UPDATE ';
        $query .= $this->table( 'shop_orders' );
        $query .= ' SET status = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $status, $orderId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // save action
            $this->user_obj->admin_actions()->save_action( $orderId, 'editorderstatus', [
                'old'   => $order->getStatus(),
                'new'   => $status
            ] );

            actions()->do_action( 'after:edit-shop-order-status-admin', $this->user, $order, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

}
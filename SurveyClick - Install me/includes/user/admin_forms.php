<?php

namespace user;

class admin_forms {

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

    public function add_user( array $attributes = [] ) {
        $years      = range( ( date( 'Y' ) - 5 ), ( date( 'Y' ) - 90 ) );
        $myPerms    = $this->user_obj->isOwner() ? [ 'user' => t( 'User' ), 'moderator' => t( 'Moderator' ), 'administrator' => t( 'Administrator' ), 'owner' => t( 'Owner' ) ] : ( $this->user_obj->isAdmin() ? [ 'user' => t( 'User' ), 'moderator' => t( 'Moderator' ) ] : [] );

        $form       = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:add-user-admin', [
            'username'  => [ 'type' => 'text', 'label' => t( 'Username' ), 'required' => 'required' ],
            'full_name' => [ 'type' => 'text', 'label' => t( 'Full name' ), 'required' => 'required' ],
            'birthday'  => [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [ [ 'type' => 'inline-group', 'label' => t( 'Birthday' ), 'grouped' => false, 'fields' => [
                'year'  => [ 'type' => 'select', 'options' => array_combine( $years, $years ) ],
                'month' => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 12 ), range( 01, 12 ) ) ) ],
                'day'   => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 31 ), range( 01, 31 ) ) ) ],
            ] ], 'gender' => [  'type' => 'select', 'label' => t( 'Gender' ), 'placeholder' => t( 'Gender' ), 'options' => [ 'M' => t( 'Male' ), 'F' => t( 'Female' ) ] ] ] ],
            'avatar'    => [ 'type' => 'image', 'label' => t( 'Avatar' ), 'category' => 'user-avatar' ],
            'email'     => [ 'type' => 'text', 'label' => t( 'Email address' ), 'required' => 'required'  ],
            'sendemail' => [ 'type' => 'checkbox', 'title' => t( 'Send credentials to this email address' ), 'value' => true ],
            [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
                'password'  => [ 'type' => 'text', 'label' => t( 'Password' ), 'required' => 'required' ],
                [ 'type' => 'custom', 'callback' => function() {
                    return '<a href="#" class="generate-password btn">' . t( 'Generate' ) . '</a>';
                }, 'classes' => 'sae wa' ]
            ] ],
            'role'      => [ 'type' => 'select', 'label' => t( 'Role' ), 'options' => $myPerms ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => array_map( function( $item ) {
                return esc_html( t( $item->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ],
            'address'   => [ 'type' => 'textarea', 'label' => t( 'Address' ) ],
            'more'      => [ 'type' => 'checkboxes', 'label' => t( 'Permissions' ), 'options' => [ 'sur' => t( 'Surveyor' ), 'valid' => t( 'Email verified' ), 'verified' => t( 'Identity verified' ) ], 'value' => [] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] ) );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-new2', [ 'action2' => 'add-user' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_user_admin" class="form add_user_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_user_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_user( object $user, array $attributes = [] ) {
        $years      = range( ( date( 'Y' ) - 5 ), ( date( 'Y' ) - 90 ) );
        $birthday   = $user->getBirthday() ? explode( '-', $user->getBirthday() ) : [ 0, 1, 1 ];
        $myPerms    = $this->user_obj->isOwner() ? [ 'user' => t( 'User' ), 'moderator' => t( 'Moderator' ), 'administrator' => t( 'Administrator' ), 'owner' => t( 'Owner' ) ] : ( $this->user_obj->isAdmin() ? [ 'user' => t( 'User' ), 'moderator' => t( 'Moderator' ) ] : [] );
        $role       = $user->isOwner() ? 'owner' : ( $user->isAdmin() ? 'administrator' : ( $user->isModerator() ? 'moderator' : 'user' ) );
        $markup     = '';

        if( $user->getPerm() >= $this->user_obj->getPerm() ) {
            $markup .= '<div class="msg alert">' . t( "Your can't edit this profile" ) . '</div>';
        }
        
        $form       = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:edit-user-admin', [
            'username'  => [ 'type' => 'text', 'label' => t( 'Username' ), 'required' => 'required' ],
            'full_name' => [ 'type' => 'text', 'label' => t( 'Full name' ) ],
            'birthday'  => [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [ [ 'type' => 'inline-group', 'label' => t( 'Birthday' ), 'grouped' => false, 'fields' => [
                'year'  => [ 'type' => 'select', 'options' => array_combine( $years, $years ) ],
                'month' => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 12 ), range( 01, 12 ) ) ) ],
                'day'   => [ 'type' => 'select', 'options' => array_map( function( $v ) { return sprintf( '%02d', $v ); }, array_combine( range( 01, 31 ), range( 01, 31 ) ) ) ],
            ] ], 'gender' => [  'type' => 'select', 'label' => t( 'Gender' ), 'placeholder' => t( 'Gender' ), 'options' => [ 'M' => t( 'Male' ), 'F' => t( 'Female' ) ] ] ] ],
            'avatar'    => [ 'type' => 'image', 'category' => 'user-avatar', 'identifierId' => $user->getId(), 'label' => t( 'Avatar' ) ],
            'email'     => [ 'type' => 'text', 'label' => t( 'Email address' ), 'required' => 'required'  ],
            'role'      => [ 'type' => 'select', 'label' => t( 'Role' ), 'options' => $myPerms ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => array_map( function( $item ) {
                return esc_html( t( $item->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ],
            'address'   => [ 'type' => 'textarea', 'label' => t( 'Address' ) ],
            'more'      => [ 'type' => 'checkboxes', 'label' => t( 'Permissions' ), 'options' => [ 'sur' => t( 'Surveyor' ), 'valid' => t( 'Email verified' ), 'verified' => t( 'Identity verified' ) ], 'value' => [] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] ) );

        $perms      = [];
        if( $user->isSurveyor() )
        $perms['sur']  = true;
        if( $user->hasEmailVerified() )
        $perms['valid']  = true;
        if( $user->isVerified() )
        $perms['verified']  = true;
        
        $form->setValues( [
            'username'  => $user->getName(),
            'full_name' => $user->getFullName(),
            'country'   => $user->getCountryId(),
            'address'   => $user->getAddress(),
            'email'     => $user->getEmail(),
            'role'      => $role,
            'gender'    => $user->getGender(),
            'more'      => $perms
        ] );

        if( ( $birthday = $user->getBirthday() ) && ( $birthday = explode( '-', $birthday ) ) ) {
            $form->setValues( [
                'year'  => (int) $birthday[0],
                'month' => (int) $birthday[1],
                'day'   => (int) $birthday[2]
            ] );
        }

        if( $user->getAvatar() )
        $form->setValue( 'avatar', [ $user->getAvatar() => $user->getAvatarURL() ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-users2', [ 'action2' => 'edit', 'id' => $user->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="edit_profile_admin" class="form edit_profile_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_profile_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function change_user_password( object $user, array $attributes = [] ) {
        $markup = '';

        if( $user->getPerm() >= $this->user_obj->getPerm() ) {
            $markup .= '<div class="msg alert">' . t( "Your can't edit this profile" ) . '</div>';
        }

        $form   = new \markup\front_end\form_fields( [
            [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
                'password'  => [ 'type' => 'text', 'label' => t( 'Password' ), 'required' => 'required' ],
                [ 'type' => 'custom', 'callback' => function() {
                    return '<a href="#" class="generate-password btn">' . t( 'Generate' ) . '</a>';
                }, 'classes' => 'sae wa' ]
            ] ],
            'sendmail'  => [ 'type' => 'checkbox', 'label' => t( 'Email password' ), 'title' => t( 'Send the new password via email' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Reset' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-users2', [ 'action2' => 'change-password', 'id' => $user->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="change_user_password_admin" class="form change_user_password_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'change_user_password_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function send_alert( object $user, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'title'     => [ 'type' => 'text', 'label' => t( 'Title' ), 'required' => 'required' ],
            'text'      => [ 'type' => 'textarea', 'label' => t( 'Text' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Send' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-users2', [ 'action2' => 'send-alert', 'id' => $user->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="send_alert_admin" class="form send_alert_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'send_alert_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function ban_user( object $user, array $attributes = [] ) {
        $markup = '';

        if( $user->getPerm() >= $this->user_obj->getPerm() ) {
            $markup .= '<div class="msg alert">' . t( "Your can't edit this profile" ) . '</div>';
        }

        if( $user->isBanned() )
        $markup .= '<div class="msg alert">' . sprintf( t( '%s is already banned until <strong>%s</strong>' ), esc_html( $user->getDisplayName() ), custom_time( $user->getBannedUntil(), 2 ) ) . '</div>';

        $form   = new \markup\front_end\form_fields( [
            'date'      => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'Expiration' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Ban!' ) ]
        ] );

        if( $user->isBanned() ) {
            $form->setValues( [
                'date'  => custom_time( $user->getBannedUntil(), 2, 'Y-m-d H:i:s' )
            ] );
        }

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-users2', [ 'action2' => 'ban', 'id' => $user->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="ban_user_admin" class="form ban_user_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'ban_user_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function user_balance( object $user, array $attributes = [] ) {
        $markup = '';

        if( $user->getPerm() >= $this->user_obj->getPerm() ) {
            $markup .= '<div class="msg alert">' . t( "Your can't edit this profile" ) . '</div>';
        }

        $form   = new \markup\front_end\form_fields( [
            'balance'   => [ 'type' => 'number', 'step' => '.01', 'label' => t( 'Available balance' ) ],
            'bonus'     => [ 'type' => 'number', 'step' => '.01', 'label' => t( 'Bonus balance' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'balance'   => (double) $user->getRealBalance(),
            'bonus'     => (double) $user->getBonus()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-users2', [ 'action2' => 'user-balance', 'id' => $user->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup .= '<form id="user_balance_admin" class="form user_balance_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'user_balance_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_team( object $team, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'name'  => $team->getName()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-teams2', [ 'action2' => 'edit', 'id' => $team->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_team_admin" class="form edit_team_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_team_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_survey_status( object $survey, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'label' => t( 'Status' ), 'options' => [ 5 => t( 'Finished' ), 4 => t( 'Live' ), 3 => t( 'Paused' ), 2 => t( 'Waiting approval' ), 1 => t( 'Require setup' ), 0 => t( 'Rejected' ) ] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'status'    => $survey->getStatus()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'admin-manage-surveys2', [ 'action2' => 'change-status', 'id' => $survey->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_survey_status_admin" class="form edit_survey_status_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_status_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_shop_item( array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'category'  => [ 'type' => 'checkboxes', 'label' => t( 'Categories' ), 'options' => array_map( function( $category ) {
                return esc_html( $category->name );
            }, ( new \query\shop\categories )->orderBy( 'id' )->fetch( -1 ) ) ],
            'media'     => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'shop_item' ],
            'desc'      => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
            'stock'     => [ 'type' => 'number', 'min' => 0, 'label' => t( 'Stock' ), 'description' => t( 'Leave empty for unlimited' ) ],
            'price'     => [ 'type' => 'number', 'min' => 0, 'label' => t( 'Price ' ), 'required' => 'required' ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => ( [ '' => t( 'All' ) ] + array_map( function( $country ) {
                return esc_html( t( $country->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ) ],
            'status'    => [ 'type' => 'checkbox', 'label' => t( 'Status ' ), 'title' => t( 'Available' ), 'value' => 1 ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-shop2', [ 'action2' => 'add-item' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_shop_item_admin" class="form add_shop_item_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_shop_item_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_shop_item( object $item, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ),  'required' => 'required' ],
            'category'  => [ 'type' => 'checkboxes', 'label' => t( 'Categories' ), 'options' => array_map( function( $category ) {
                return esc_html( $category->name );
            }, ( new \query\shop\categories )->orderBy( 'id' )->fetch( -1 ) ) ],
            'media'     => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'shop_item' ],
            'desc'      => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
            'stock'     => [ 'type' => 'number', 'min' => 0, 'label' => t( 'Stock' ), 'description' => t( 'Leave empty for unlimited' ) ],
            'price'     => [ 'type' => 'number', 'min' => 0, 'label' => t( 'Price ' ),  'required' => 'required' ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => ( [ '' => t( 'All' ) ] + array_map( function( $country ) {
                return esc_html( t( $country->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ) ],
            'status'    => [ 'type' => 'checkbox', 'label' => t( 'Status ' ), 'title' => t( 'Available' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'name'      => $item->getName(),
            'desc'      => $item->getDescription(),
            'category'  => $item->getCategories()->select( [ 'category' ] )->selectKey( 'category' )->fetch( -1 ),
            'stock'     => $item->getStock(),
            'price'     => $item->getPrice(),
            'country'   => $item->getCountry(),
            'status'    => (boolean) $item->getStatus()
        ] );

        if( $item->getMedia() )
        $form->setValue( 'media', [ $item->getMedia() => $item->getMediaURL() ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-shop2', [ 'action2' => 'edit-item', 'id' => $item->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_shop_item_admin" class="form edit_shop_item_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_shop_item_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function add_shop_category( array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => ( [ '' => t( 'All' ) ] + array_map( function( $country ) {
                return esc_html( t( $country->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ) ],
            'status'    => [ 'type' => 'checkbox', 'label' => t( 'Status ' ), 'title' => t( 'Available' ), 'value' => 1 ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-shop2', [ 'action2' => 'add-category' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="add_shop_category_admin" class="form add_shop_category_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'add_shop_category_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_shop_category( object $category, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ),  'required' => 'required' ],
            'country'   => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => ( [ '' => t( 'All' ) ] + array_map( function( $country ) {
                return esc_html( t( $country->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ) ) ],
            'status'    => [ 'type' => 'checkbox', 'label' => t( 'Status ' ), 'title' => t( 'Available' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'name'      => $category->getName(),
            'country'   => $category->getCountry(),
            'status'    => (boolean) $category->getStatus()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-shop2', [ 'action2' => 'edit-category', 'id' => $category->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_shop_category_admin" class="form edit_shop_category_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_shop_category_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function edit_shop_order_status( object $order, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'label' => t( 'Status' ), 'options' => [ 0 => t( 'Canceled' ), 1 => t( 'Pending' ), 2 => t( 'Approved' ) ] ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form   ->setValues( [
            'status'    => $order->getStatus()
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'manage-shop2', [ 'action2' => 'change-order-status', 'id' => $order->getId() ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="edit_shop_order_status_admin" class="form edit_shop_order_status_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_shop_order_status_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public function clean_website( array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            [ 'type' => 'inline-group', 'fields' => [
                    'surveys'       => [ 'type' => 'checkbox', 'label' => t( 'Surveys' ), 'description' => t( 'Deleted surveys will not be removed completely until you perform this action' ), 'title' =>  t( 'Remove already deleted surveys' ) ],
                    'surveys_old'   => [ 'type' => 'select', 'label' => t( 'Older than' ), 'options' =>  [ 1 => t( '1 day' ), 3 => t( '3 days' ), 7 => t( '7 days' ), 30 => t( '30 days' ), 0 => t( 'Delete all' ) ], 'classes' => 'asc', 'when' => [ '=', 'data[surveys]', true ] ],
                ], 'grouped' => false 
            ],
            'media'     => [ 'type' => 'checkbox', 'label' => t( 'Media files' ), 'description' => t( 'Deleted media files will not be removed completely until you perform this action' ), 'title' =>  t( 'Remove deleted media files' ) ],
            [ 'type' => 'inline-group', 'fields' => [
                    'chat'      => [ 'type' => 'checkbox', 'label' => t( 'Chat messages' ), 'title' =>  t( 'Remove old chat messages' ) ],
                    'chat_old'  => [ 'type' => 'select', 'label' => t( 'Older than' ), 'options' =>  [ 1 => t( '1 month' ), 3 => t( '3 months' ), 6 => t( '6 months' ), 12 => t( '12 months' ) ], 'classes' => 'asc', 'when' => [ '=', 'data[chat]', true ] ],
                ], 'grouped' => false 
            ],
            'code'      => [ 'type' => 'checkbox', 'label' => t( 'Verification codes' ), 'title' =>  t( 'Remove all expired verification codes' ) ],
            'carts'     => [ 'type' => 'checkbox', 'label' => t( 'Abandoned loyalty points carts' ), 'title' =>  t( 'Remove all loyalty points carts older than 30 days' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Clean' ) ]
        ] );

        $this->modifyLastForm( $form );
        $fields                     = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'clean-website' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form id="clean_website_admin" class="form clean_website_admin_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'clean_website_admin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

}
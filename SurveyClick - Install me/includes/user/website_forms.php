<?php

namespace user;

class website_forms {

    public static function general_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'website_name'  => [ 'type' => 'text', 'label' => t( 'Website Name' ), 'value' => get_option( 'website_name' ), 'required' => 'required' ],
            'website_desc'  => [ 'type' => 'textarea', 'label' => t( 'Website Description' ), 'value' => get_option( 'website_desc' ), 'required' => 'required' ],
            'website_url'   => [ 'type' => 'text', 'label' => t( 'Website URL' ), 'value' => get_option( 'site_url' ), 'required' => 'required' ],
            'withdraw_min'  => [ 'type' => 'text', 'label' => t( 'Minimum Witdraw Amount' ), 'value' => get_option( 'withdraw_min' ), 'required' => 'required' ],
            'deposit_min'   => [ 'type' => 'text', 'label' => t( 'Minimum Deposit Amount' ), 'value' => get_option( 'deposit_min' ), 'required' => 'required' ],
            'def_country'   => [ 'type' => 'select', 'label' => t( 'Default Country' ), 'options' => array_map( function( $lng ) {
                return esc_html( $lng->name );
            }, getCountries() ), 'value' => get_option( 'def_country' ), 'required' => 'required' ],
            'def_language'  => [ 'type' => 'select', 'label' => t( 'Default Language' ), 'options' => array_map( function( $lng ) {
                return esc_html( $lng['name'] );
            }, getLanguages() ), 'value' => get_option( 'def_language' ), 'required' => 'required' ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'general' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form class="form general_settings_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'general_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function paypal_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'paypal_client_id'  => [ 'type' => 'text', 'label' => t( 'Client ID' ), 'value' => get_option( 'paypal_client_id' ), 'required' => 'required' ],
            'paypal_secret'     => [ 'type' => 'text', 'label' => t( 'Secret' ), 'value' => get_option( 'paypal_secret' ), 'required' => 'required' ],
            'button'            => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'paypal' ] );

        $markup = '<form class="form paypal_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'paypal_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function stripe_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'stripe_secret_key' => [ 'type' => 'text', 'label' => t( 'Stripe Secret Key' ), 'value' => get_option( 'stripe_secret_key' ), 'required' => 'required' ],
            'button'            => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'stripe' ] );

        $markup = '<form class="form stripe_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'stripe_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function email_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'email_type'  => [ 'type' => 'select', 'label' => t( 'Send via' ), 'value' => get_option( 'email_type' ), 'required' => 'required', 'options' => [
                'mail'  => t( 'PHP Mail' ),
                'smtp'  => t( 'SMTP Server' )
            ] ],
            'mail_smtp' => [ 'type' => 'group', 'label' => t( 'SMTP Settings' ),  'fields' => [
                'server'    => [ 'type' => 'text', 'label' => t( 'Server' ) ],
                'port'      => [ 'type' => 'text', 'label' => t( 'Port' ) ],
                'username'  => [ 'type' => 'text', 'label' => t( 'Username' ) ],
                'password'  => [ 'type' => 'text', 'label' => t( 'Password' ) ]
            ], 'when' => [ '=', 'data[email_type]', 'smtp' ] ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        if( ( $smtp_opt = get_option_array( 'mail_smtp' ) ) )
        $form->setValues( [ 'mail_smtp' => $smtp_opt ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'email' ] );

        $markup = '<form class="form email_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'email_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '
        <div class="mt40">
        <h3>' . t( 'Email templates' ) . '</h3>
        </div>
        
        <div class="list">';

        $templates  = new \site\se_template;
        
        try {
            $tpls = $templates->getTemplates();

            foreach( $tpls as $file => $tpl ) {
                $templates  ->setCurrent( $tpl );
                $form       = new \markup\front_end\form_fields;
                $fields     = [];

                $fields[]   = [ 'type' => 'custom', 'label' => t( 'File name' ), 'callback' => basename( $file ) ];

                if( ( $desc = $templates->getDescription() ) )
                $fields[]   = [ 'type' => 'custom', 'label' => t( 'Description' ), 'callback' => t( esc_html( $desc ) ) ];

                if( ( $lang = $templates->getLanguage() ) )
                $fields[]   = [ 'type' => 'custom', 'label' => t( 'Language' ), 'callback' => t( esc_html( $lang ) ) ];

                $fields[]   = [ 'type' => 'custom', 'label' => t( 'Shortcodes' ), 'callback' => function() use ( $templates ) {
                    $text = '<div class="rp h100">';
                    foreach( $templates->getShortcodesMap() as $key => $shortcode ) {
                        $text .= '<div><strong>' . esc_html( $key ) . '</strong> - ';
                        $text .= t( esc_html( $shortcode ) ) . '</div>';
                    }
                    $text .= '</div>';
                    return $text;
                } ];

                $fields[]           = [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
                    'from_name'     => [ 'type' => 'text', 'label' => t( 'From (name)' ), 'value' => $templates->getFromName(), 'disabled' => ( !$templates->isEditable() ? true : NULL ) ],
                    'from_email'    => [ 'type' => 'text', 'label' => t( 'From (email addr.)' ), 'value' => $templates->getFromEmailAddress(), 'disabled' => ( !$templates->isEditable() ? true : NULL )  ],
                ] ];

                $fields['subject']  = [ 'type' => 'text', 'label' => t( 'Subject' ), 'value' => $templates->getSubject(), 'disabled' => ( !$templates->isEditable() ? true : NULL ) ];
                $fields['body']     = [ 'type' => 'textarea', 'label' => t( 'Body' ), 'value' => $templates->getBody(), 'disabled' => ( !$templates->isEditable() ? true : NULL ) ];
                $fields['template'] = [ 'type' => 'hidden', 'value' => $file ];
                $fields[]           = [ 'type' => 'button', 'label' => t( 'Save' ) ];

                $form->addFields( [ [ 'type' => 'dropdown', 'grouped' => false, 'fields' => [ 'tpl' => [ 'label' => $templates->getName(), 'fields' => $fields ] ] ] ] );

                $fields = $form->build();
                $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'edit-email-template' ] );
            
                $markup .= '<form class="form email_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'email_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
                $markup .= $fields;
                $markup .= '</form>';
            }
        }
        
        catch( \Exception $e ) {
            $markup .= '<div class="msg error">' . $e->getMessage() . '</div>';
        }

        $markup .= '</div>';

        return $markup;
    }

    public static function prices_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'min_cpa_self'  => [ 'type' => 'number', 'label' => t( 'CPA for self promoted surveys' ), 'step' => '.001', 'description' => t( 'Per additional response' ), 'value' => get_option( 'min_cpa_self' ), 'min' => 0.001, 'required' => 'required' ],
            'min_cpa'       => [ 'type' => 'number', 'label' => t( 'Minimum CPA for surveys' ), 'step' => '.001', 'value' => get_option( 'min_cpa' ), 'min' => 0.001, 'required' => 'required' ],
            'comm_cpa'      => [ 'type' => 'number', 'label' => t( 'Commission for responses' ), 'step' => '.1', 'description' => t( 'In percents' ), 'value' => get_option( 'comm_cpa' ), 'required' => 'required' ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'prices' ] );

        $markup = '<form class="form prices_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'prices_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function referral_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'registration' => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Registration (and verified account)' ), 'fields' => [ 'reg' => [ 'type' => 'repeater', 'fields' => [
                'level' => [ 'type' => 'custom', 'label' => t( 'Level' ), 'classes' => 'autoi w100 tc' ],
                'stars' => [ 'type' => 'number', 'label' => t( 'Loyalty stars' ), 'step' => '.1' ]
            ], 'add_button' => t( 'Add level' ) ] ], 'grouped' => false ] ], 'grouped' => false ],

            'eachupgrade' => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Each upgrade' ), 'fields' => [ 'eachupgrade' => [ 'type' => 'repeater', 'fields' => [
                'level' => [ 'type' => 'custom', 'label' => t( 'Level' ), 'classes' => 'autoi w100 tc' ],
                'stars' => [ 'type' => 'number', 'label' => t( 'Loyalty stars' ), 'step' => '.1' ]
            ], 'add_button' => t( 'Add level' ) ] ], 'grouped' => false ] ], 'grouped' => false ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $settings = get_option_json( 'ref_system' );
        
        if( gettype( $settings ) == 'array' ) {
            array_walk_recursive( $settings, function( $v ) {
                $v = [ 'stars' => $v ];
            } );

            $form->setValues( $settings );
        }

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'referral' ] );

        $markup = '<form class="form referral_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'referral_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function security_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'recapcha'        => [ 'type' => 'dropdown', 'label' => t( 'reCAPTCHA' ), 'fields' => [ [ 'label' => t( 'reCAPTCHA settings' ), 'fields'  => [
                'recaptcha_key'     => [ 'type' => 'text', 'label' => t( 'Key' ) ],
                'recaptcha_secret'  => [ 'type' => 'text', 'label' => t( 'Secret' ) ] ], 'grouped' => false
            ] ], 'grouped' => false ],
            'femail_verify' => [ 'type' => 'select', 'label' => t( 'Require email verification' ), 'options' => [ 1 => t( 'Yes' ), 0 => t( 'No' ) ], 'value' => (int) get_option( 'femail_verify', 0 ), 'description' => t( "Members' access will be restricted until the email address is verified" ) ],
            'auto_approve_surveys' => [ 'type' => 'select', 'label' => t( 'Auto approve surveys' ), 'options' => [ 1 => t( 'Yes' ), 0 => t( 'No' ) ], 'value' => (int) get_option( 'auto_approve_surveys', 1 ) ],
            'button' => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'recaptcha_key'         => get_option( 'recaptcha_key', '' ),
            'recaptcha_secret'      => get_option( 'recaptcha_secret', '' )
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'security' ] );

        $markup = '<form class="form security_settings_form"' . \util\attributes::add_attributes( filters()->do_filter( 'security_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function seo_settings( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'favicon'       => [ 'type' => 'image', 'label' => t( 'Favicon' ), 'value' => get_option_json( 'front_end_favicon', [] ), 'category' => 'settings' ],
            'title'         => [ 'type' => 'text', 'label' => t( 'Meta tag: Title' ), 'value' => get_option( 'meta_tag_title' ) ],
            'description'   => [ 'type' => 'textarea', 'label' => t( 'Meta tag: Description' ), 'value' => get_option( 'meta_tag_desc' ) ],
            'keywords'      => [ 'type' => 'textarea', 'label' => t( 'Meta tag: Keywords' ), 'value' => get_option( 'meta_tag_keywords' ) ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'seo' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form class="form seo_settings_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'seo_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function install_theme( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( ( filters()->do_filter( 'form:fields:install-theme', [
            'theme'  => [ 'type' => 'file', 'label' => t( 'Theme' ), 'category' => '', 'accept' => '.zip' ]
        ] ) + [ 'button'  => [ 'type' => 'button', 'label' => t( 'Install' ) ] ] ) );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'install-theme' ] );

        $markup = '<form class="form install_theme_form"' . \util\attributes::add_attributes( filters()->do_filter( 'install_theme_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function install_plugin( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( ( filters()->do_filter( 'form:fields:install-plugin', [
            'plugin'  => [ 'type' => 'file', 'label' => t( 'Plugin' ), 'category' => '', 'accept' => '.zip' ]
        ] ) + [ 'button'  => [ 'type' => 'button', 'label' => t( 'Install' ) ] ] ) );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'install-plugin' ] );

        $markup = '<form class="form install_plugin_form"' . \util\attributes::add_attributes( filters()->do_filter( 'install_plugin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_category( string $type, array $attributes = [] ) {
        $builder    = new \dev\builder\categories;
        $builder    ->setType( $type );

        try {
        $builder->checkType();
        }

        catch( \Exception $e ) {
            return ;
        }

        if( !$builder->useCategories() ) return ;

        $fields = [
            'name'    => [ 'type' => 'text', 'label' => t( 'Name' ), 'position' => 1, 'required' => 'required' ],
            'desc'    => [ 'type' => 'textarea', 'label' => t( 'Description' ), 'position' => 2 ],
            'slug'    => [ 'type' => 'text', 'label' => t( 'Slug' ), 'position' => 3, 'required' => 'required' ],
            'parent'  => [ 'type' => 'select', 'label' => t( 'As a child for' ), 'position' => 4, 'options' => ( [ '' => t( 'None' ) ] + array_map( function( $cat ) {
                return esc_html( $cat->name );
            }, categories()->setType( $type )->fetch( -1 ) ) ) ],
            'lang'    => [ 'type' => 'select', 'label' => t( 'Language' ), 'position' => 5, 'options' => array_map( function( $language ) {
                return esc_html( $language['name_en'] );
            }, getLanguages() ) ],
            'more'    => [ 'type'  => 'dropdown', 'label' => t( 'More options' ), 'position' => 6, 'fields' => [ 'meta' => [ 'label' => t( 'Meta tags' ), 'fields' => [
                'title'   => [ 'type' => 'text', 'label' => t( 'Title' ) ],
                'mdesc'   => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
                'keys'    => [ 'type' => 'textarea', 'label' => t( 'Keywords' ) ],
            ] ] ], 'grouped' => false ]
        ];

        $builder  ->manageFields( $fields );

        $fields['type']   = [ 'type' => 'hidden', 'value' => $type ];
        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Add' ) ];

        $form     = new \markup\front_end\form_fields( $fields );
        $values   = [];
        $builder  ->manageDefaultValues( $values );
        $form     ->setValues( $values );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-category' ] );

        $markup = '<form class="form add_category_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_category_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_category( object $category, array $attributes = [] ) {
        $builder    = new \dev\builder\categories;
        $builder    ->setType( $category->getType() );

        try {
            $builder->checkType();
        }

        catch( \Exception $e ) {
            return ;
        }

        if( !$builder->useCategories() ) return ;

        $fields     = [
        'name'    => [ 'type' => 'text', 'label' => t( 'Name' ), 'position' => 1, 'required' => 'required' ],
        'desc'    => [ 'type' => 'textarea', 'label' => t( 'Description' ), 'position' => 2 ],
        'slug'    => [ 'type' => 'text', 'label' => t( 'Slug' ), 'position' => 3, 'required' => 'required' ],
        'parent'  => [ 'type' => 'select', 'label' => t( 'As a child for' ), 'position' => 4, 'options' => ( [ '' => t( 'None' ) ] + array_map( function( $cat ) {
            return esc_html( $cat->name );
        }, categories()->setType( $category->getType() )->excludeId( $category->getId() )->fetch( -1 ) ) ) ],
        'lang'    => [ 'type' => 'select', 'label' => t( 'Language' ), 'position' => 5, 'options' => array_map( function( $language ) {
            return esc_html( $language['name_en'] );
        }, getLanguages() ) ],
        'more'    => [ 'type'  => 'dropdown', 'label' => t( 'More options' ), 'position' => 6, 'fields' => [ 'meta' => [ 'label' => t( 'Meta tags' ), 'fields' => [
            'title'   => [ 'type' => 'text', 'label' => t( 'Title' ) ],
            'mdesc'   => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
            'keys'    => [ 'type' => 'textarea', 'label' => t( 'Keywords' ) ],
        ] ] ], 'grouped' => false ]
        ];

        $builder  ->setObject( $category )
                ->manageFields( $fields );
        $fields['type']   = [ 'type' => 'hidden', 'value' => $category->getType() ];
        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Save' ) ];
        $form     = new \markup\front_end\form_fields( $fields );
        $values   = [
        'name'    => $category->getName(),
        'desc'    => $category->getDescription(),
        'slug'    => $category->getSlug(),
        'parent'  => $category->getParentId(),
        'lang'    => $category->getLanguageId(),
        'meta'    => [
            'title' => $category->getMetaTitle(),
            'mdesc' => $category->getMetaDesc(),
            'keys'  => $category->getMetaKeywords()
        ] 
        ];

        $builder  ->manageValues( $values );
        $form     ->setValues( $values );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-category', 'category' => $category->getId() ] );

        $markup = '<form class="form edit_category_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_category_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }
  
    public static function add_page( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
        'title'   => [ 'type' => 'text', 'label' => t( 'Title' ), 'required' => 'required' ],
        'slug'    => [ 'type' => 'text', 'label' => t( 'Slug' ), 'required' => 'required' ],
        'text'    => [ 'type' => 'textarea', 'label' => t( 'Content' ) ],
        'country' => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => array_map( function( $cat ) {
            return esc_html( $cat['name'] );
        }, getCountries() ) ],
        [ 'type'  => 'dropdown', 'label' => t( 'Meta tags' ), 'label2' => t( 'More' ), 'grouped' => false, 'fields' => [ 'meta' => [ 'fields' => [
            'title'   => [ 'type' => 'text', 'label' => t( 'Title' ) ],
            'mdesc'   => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
            'keys'    => [ 'type' => 'textarea', 'label' => t( 'Keywords' ) ],
        ] ] ] ],
        'button'  => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-page' ] );

        $markup = '<form class="form add_page_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_page_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_page( object $page, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'title'   => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'slug'    => [ 'type' => 'text', 'label' => t( 'Slug' ), 'required' => 'required' ],
            'text'    => [ 'type' => 'textarea', 'label' => t( 'Content' ) ],
            'country' => [ 'type' => 'select', 'label' => t( 'Country' ), 'options' => array_map( function( $country ) {
                return esc_html( $country['name'] );
            }, getCountries() ) ],
            [ 'type'  => 'dropdown', 'label' => t( 'Meta tags' ), 'label2' => t( 'More' ), 'grouped' => false, 'fields' => [ 'meta' => [ 'fields' => [
                'title'   => [ 'type' => 'text', 'label' => t( 'Title' ) ],
                'mdesc'   => [ 'type' => 'textarea', 'label' => t( 'Description' ) ],
                'keys'    => [ 'type' => 'textarea', 'label' => t( 'Keywords' ) ],
            ] ] ] ],
            'button'  => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'title'   => $page->getTitle(),
            'slug'    => $page->getSlug(),
            'text'    => $page->getText(),
            'country' => $page->getCountryId(),
            'meta[title]' => $page->getMetaTitle(),
            'meta[mdesc]' => $page->getMetaDesc(),
            'meta[keys]'  => $page->getMetaKeywords()
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-page', 'page' => $page->getId() ] );

        $markup = '<form class="form edit_page_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_page_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_voucher( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
        'code'        => [ 'type' => 'text', 'label' => t( 'Code' ), 'required' => 'required' ],
        'applying'    => [ 'type' => 'select', 'label' => t( 'Applying' ), 'options' => [ 0 => t( 'Free (without any conditions)' ), 1 => t( 'On deposit' ) ] ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'amount'    => [ 'type' => 'number', 'label' => t( 'Amount' ), 'step' => '.01', 'min' => 0.01, 'required' => 'required' ],
            'atype'     => [ 'type' => 'select', 'label' => t( 'Type' ), 'options' => [ 0 => t( 'Fixed amount' ), 1 => t( 'Percent' ) ] ],
        ] ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'used_by'   => [ 'type' => 'select', 'label' => t( 'Can be used by' ), 'options' => [ 0 => t( 'Anyone' ), 1 => t( 'User' ) ] ],
            'user'      => [ 'type' => 'text', 'label' => t( 'User' ), 'when' => [ '=', 'data[used_by]', 1 ] ],
        ] ],
        'limit'       => [ 'type' => 'number', 'label' => t( 'Limit' ), 'description' => t( 'Set 0 for an unlimited number of times this voucher can be used' ), 'value' => 1, 'min' => 0, 'required' => 'required' ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'expiration'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'Expiration' ), 'when' => [ '=', 'data[never_exp]', false ] ],
            'never_exp'   => [ 'type' => 'checkbox', 'label' => t( 'Never expires' ), 'title' => t( 'This voucher will never expire' ), 'classes' => 'asc' ],
        ] ],
        'available'   => [ 'type' => 'checkbox', 'label' => t( 'Status' ), 'title' => t( 'Available' ), 'value' => 1 ],
        'button'      => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-voucher' ] );

        $markup = '<form class="form add_voucher_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_voucher_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_voucher( object $voucher, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
        'code'        => [ 'type' => 'text', 'label' => t( 'Code' ), 'required' => 'required' ],
        'applying'    => [ 'type' => 'select', 'label' => t( 'Applying' ), 'options' => [ 0 => t( 'Free (without any conditions)' ), 1 => t( 'On deposit' ) ], 'required' => 'required' ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'amount'    => [ 'type' => 'number', 'label' => t( 'Amount' ), 'step' => '.01', 'min' => 0.01, 'required' => 'required' ],
            'atype'     => [ 'type' => 'select', 'label' => t( 'Type' ), 'options' => [ 0 => t( 'Fixed amount' ), 1 => t( 'Percent' ) ] ],
        ] ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'used_by'   => [ 'type' => 'select', 'label' => t( 'Can be used by' ), 'options' => [ 0 => t( 'Anyone' ), 1 => t( 'User' ) ] ],
            'user'      => [ 'type' => 'text', 'label' => t( 'User' ), 'when' => [ '=', 'data[used_by]', 1 ] ],
        ] ],
        'limit'       => [ 'type' => 'number', 'label' => t( 'Limit' ), 'description' => t( 'Set 0 for an unlimited number of times this voucher can be used' ), 'value' => 1, 'min' => 0, 'required' => 'required' ],
        [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
            'expiration'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'Expiration' ), 'when' => [ '=', 'data[never_exp]', false ] ],
            'never_exp'   => [ 'type' => 'checkbox', 'label' => t( 'Never expires' ), 'title' => t( 'This voucher will never expire' ), 'classes' => 'asc' ],
        ] ],
        'available'   => [ 'type' => 'checkbox', 'label' => t( 'Status' ), 'title' => t( 'Available' ) ],
        'button'      => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'code'        => $voucher->getCode(),
            'applying'    => $voucher->getType(),
            'amount'      => $voucher->getAmount(),
            'atype'       => $voucher->getAmountType(),
            'used_by'     => ( $voucher->getUserId() ? 1 : 0 ),
            'user'        => ( $voucher->getUserId() ?? '' ),
            'limit'       => ( $voucher->getLimit() ?? 0 ),
            'expiration'  => ( $voucher->getExpiration() ? custom_time( $voucher->getExpiration(), 2, 'Y-m-d H:i:s' ) : date( 'Y-m-d' ) . ' 00:00' ),
            'never_exp'   => ( !$voucher->getExpiration() ?: 0 ),
            'available'   => ( $voucher->getStatus() ?: 0 ),
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-voucher', 'voucher' => $voucher->getId() ] );

        $markup = '<form class="form edit_voucher_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_voucher_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_plan( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'        => [ 'type' => 'text', 'label' => t( 'Plan name' ), 'required' => 'required' ],
            'surveys'     => [ 'type' => 'number', 'label' => t( 'Surveys limit' ), 'description' => t( '-1 stands for unlimited' ), 'min' => -1, 'required' => 'required' ],
            'responses'   => [ 'type' => 'number', 'label' => t( 'Responses per survey' ), 'description' => t( '-1 stands for unlimited' ), 'min' => -1, 'required' => 'required' ],
            'questions'   => [ 'type' => 'number', 'label' => t( 'Questions per survey' ), 'description' => t( '-1 stands for unlimited' ), 'min' => -1, 'required' => 'required' ],
            'collectors'  => [ 'type' => 'number', 'label' => t( 'Collectors' ), 'description' => t( '-1 stands for unlimited' ), 'min' => -1, 'required' => 'required' ],
            'team'        => [ 'type' => 'number', 'label' => t( 'Team members' ), 'description' => t( '-1 stands for unlimited. 0 is for team section not allowed' ), 'min' => -1, 'required' => 'required' ],
            'avb_space'   => [ 'type' => 'number', 'label' => t( 'Available space' ), 'description' => t( '-1 stands for unlimited. In MB' ), 'min' => -1, 'required' => 'required' ],
            'rbrand'      => [ 'type' => 'checkbox', 'label' => t( 'Remove brand' ), 'title' => t( "Remove our brand from survey's footer" ) ],
            'price'       => [ 'type' => 'number', 'label' => t( 'Regular monthly price' ), 'step' => '.01', 'min' => 0, 'required' => 'required' ],
            'visible'     => [ 'type' => 'checkbox', 'label' => t( 'Status' ), 'title' => t( 'Available' ), 'value' => 1 ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'add-plan' ] );

        $markup = '<form class="form add_plan_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_plan_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_plan( object $plan, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'name'        => [ 'type' => 'text', 'label' => t( 'Plan name' ), 'required' => 'required' ],
            'surveys'     => [ 'type' => 'number', 'label' => t( 'Surveys limit' ), 'description' => t( '-1 stands for unlimited' ), 'required' => 'required' ],
            'responses'   => [ 'type' => 'number', 'label' => t( 'Responses per survey' ), 'description' => t( '-1 stands for unlimited' ), 'required' => 'required' ],
            'questions'   => [ 'type' => 'number', 'label' => t( 'Questions per survey' ), 'description' => t( '-1 stands for unlimited' ), 'required' => 'required' ],
            'collectors'  => [ 'type' => 'number', 'label' => t( 'Collectors' ), 'description' => t( '-1 stands for unlimited' ), 'required' => 'required' ],
            'team'        => [ 'type' => 'number', 'label' => t( 'Team members' ), 'description' => t( '-1 stands for unlimited. 0 is for team section not allowed' ), 'required' => 'required' ],
            'avb_space'   => [ 'type' => 'number', 'label' => t( 'Available space' ), 'description' => t( '-1 stands for unlimited. In MB' ), 'min' => -1, 'required' => 'required' ],
            'rbrand'      => [ 'type' => 'checkbox', 'label' => t( 'Remove brand' ), 'title' => t( "Remove our brand from survey's footer" ) ],
            'price'       => [ 'type' => 'number', 'label' => t( 'Regular monthly price' ), 'step' => '.01', 'min' => 0, 'required' => 'required' ],
            'visible'     => [ 'type' => 'checkbox', 'label' => t( 'Status' ), 'title' => t( 'Available' ) ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        if( $plan->getObject() && ( $planId = $plan->getId() ) ) {

        // values
        $form->setValues( [
            'name'        => $plan->getName(),
            'surveys'     => $plan->getSurveys(),
            'responses'   => $plan->getResponses(),
            'questions'   => $plan->getQuestions(),
            'collectors'  => $plan->getCollectors(),
            'team'        => $plan->getTeam(),
            'avb_space'   => $plan->getAvailableSpace(),
            'rbrand'      => (bool) $plan->getRemoveBrand(),
            'price'       => $plan->getPrice(),
            'visible'     => ( $plan->isVisible() == 2 )
        ] );

        } else {

        // the default plan
        $planId   = 0;
        $defPlan  = me()->limits()->getFreeSubscription();

        // remove price
        $form->removeFields( [ 'price', 'visible' ] );

        // set default values
        $form->setValues( [
            'name'        => $defPlan['name'],
            'surveys'     => $defPlan['surveys'],
            'responses'   => $defPlan['responses'],
            'questions'   => $defPlan['questions'],
            'collectors'  => $defPlan['collectors'],
            'team'        => $defPlan['tmembers'],
            'rbrand'      => $defPlan['rBrand'],
            'avb_space'   => $defPlan['space']
        ] );

        }

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'edit-plan', 'plan' => $planId ] );

        $markup = '<form class="form edit_plan_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_plan_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_plan_offer( object $plan, array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'min_months'  => [ 'type' => 'number', 'label' => t( 'Minimum months' ), 'required' => 'required', 'value' => 1, 'min' => 1 ],
            'price'       => [ 'type' => 'number', 'label' => t( 'Price' ), 'required' => 'required', 'step' => '.01', 'min' => 0.01 ],
            [ 'type' => 'inline-group', 'label' => t( 'Available from' ), 'fields' => [
                'start'   => [ 'type' => 'text', 'input_type' => 'datetime-local', 'when' => [ '=', 'data[active]', false ] ],
                'active'  => [ 'type' => 'checkbox', 'title' => t( 'Active immediately' ), 'classes' => 'asc' ],
            ], 'grouped' => false ],
            [ 'type' => 'inline-group', 'label' => t( 'Available up to' ), 'fields' => [
                'expire'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'when' => [ '=', 'data[nexp]', false ] ],
                'nexp'    => [ 'type' => 'checkbox', 'title' => t( 'Never expires' ), 'classes' => 'asc' ],
            ], 'grouped' => false ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'add-plan-offer', 'plan' => $plan->getId() ] );

        $markup = '<form class="form add_plan_offer_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_plan_offer_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_plan_offer( object $offer, array $attributes = [] ) {
        $plans  = new \query\plans\plans;
        $plans  ->setVisible( 1, '>=' );
        $form   = new \markup\front_end\form_fields( [
            'plan'        => [ 'type' => 'select', 'label' => t( 'Plan' ), 'options' => array_map( function( $plan ) {
                return esc_html( $plan->name );
            },  $plans->fetch( -1 ) ) ],
            'min_months'  => [ 'type' => 'number', 'label' => t( 'Minimum months' ), 'required' => 'required', 'value' => 1, 'min' => 1 ],
            'price'       => [ 'type' => 'number', 'label' => t( 'Price' ), 'required' => 'required', 'step' => '.01', 'min' => 0.01 ],
            'start'       => [ 'type' => 'text', 'label' => t( 'Available from' ), 'input_type' => 'datetime-local' ],
            [ 'type' => 'inline-group', 'label' => t( 'Available up to' ), 'fields' => [
                'expire'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'when' => [ '=', 'data[nexp]', false ] ],
                'nexp'    => [ 'type' => 'checkbox', 'title' => t( 'Never expires' ), 'classes' => 'asc' ],
            ], 'grouped' => false ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        // values
        $form->setValues( [
            'plan'      => $offer->getPlanId(),
            'min_months'=> $offer->getMinMonths(),
            'price'     => $offer->getPrice(),
            'start'     => $offer->getStartDate(),
            'nexp'      => !(bool) $offer->getEndDate(),
            'expire'    => $offer->getEndDate()
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'edit-plan-offer', 'offer' => $offer->getId() ] );

        $markup = '<form class="form edit_plan_offer_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_plan_offer_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function invoicing_settings( array $attributes = [] ) {
        $fields = [
            'tax'       => [ 'type' => 'number', 'step' => '0.01', 'label' => t( 'Tax %' ), 'description' => t( 'Percentage of the total amount representing taxes' ) ],
            'tax_label' => [ 'type' => 'text', 'label' => t( 'Tax label' ) ],
            's_label'   => [ 'type' => 'text', 'label' => t( 'Item label' ), 'description' => t( 'For invoices & receipts generated for the budget used for surveys' ) ],
            'plans_i'   => [ 'type' => 'text', 'label' => t( 'Prefix for plan invoices' ), 'description' => t( 'Prefix for invoice numbers generated after a plan has been purchased or extended' ) ],
            'plans_r'   => [ 'type' => 'text', 'label' => t( 'Prefix for plan receipts' ) ],
            'surveys_i' => [ 'type' => 'text', 'label' => t( 'Prefix for survey invoices' ), 'description' => t( 'Prefix for invoice numbers generated after a survey is finished and a spending summary is ready' ) ],
            'surveys_r' => [ 'type' => 'text', 'label' => t( 'Prefix for survey receipts' ) ],
            'c_name'    => [ 'type' => 'text', 'label' => t( 'Commpany name' ), 'description' => t( 'Appears on invoices/receipts' ) ],
            'c_address' => [ 'type' => 'textarea', 'label' => t( 'Commpany address' ), 'description' => t( 'Appears on invoices/receipts' ) ],
            'email'     => [ 'type' => 'checkbox', 'title' => t( 'Send email after the invoice is generated' ) ],
            'alert'     => [ 'type' => 'checkbox', 'title' => t( 'Send alert after the invoice is generated' ) ]
        ];

        $form = new \markup\front_end\form_fields( $fields + [
            'button'  => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $options  = [];
        $saved    = get_option( 'invoicing_settings' );

        if( $saved ) {
            $options    = json_decode( $saved, true );
            $form       ->setValues( array_merge( [ 'tax_label' => t( 'TAX' ), 's_label' => t( 'Survey answers' ) ], $options ) );
        }

        $fields = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'invoicing' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form class="form invoicing_settings_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'invoicing_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function kyc_settings( array $attributes = [] ) {
        $fields = [
            'handhelp' => [ 'type' => 'checkbox', 'label' => t( 'Handheld Identify Document' ), 'title' => t( 'Activate' ), 'description' => t( 'If you activate this option, a selfie photo of the member holding the document is required' ) ]
        ];

        $langs = [];
        foreach( getLanguages() as $langId => $lang ) {
            $langs[$langId] = [ 'type' => 'repeater', 'label' => sprintf( t( 'Require documents (in %s)' ), $lang['name_en'] ), 'fields' => [
                'doc' => [ 'type' => 'text' ]
            ] ];
        }

        $fields['langs'] = [ 'type' => 'group', 'fields' => $langs ];

        $form = new \markup\front_end\form_fields( $fields + [
            'button'  => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $options  = [];
        $saved    = get_option( 'kyc_settings' );

        if( $saved ) {
            $options = json_decode( $saved, true );
            $form->setValues( $options );
        }

        $fields = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'kyc' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form class="form kyc_settings_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'kyc_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function tos_settings( array $attributes = [] ) {
        $langs = [];
        foreach( getLanguages() as $langId => $lang ) {
            $langs[$langId] = [ 'type' => 'select', 'label' => sprintf( t( 'Terms of use: %s' ), $lang['name_en'] ), 'options' => array_map( function( $v ) {
                return esc_html( $v->title );
            }, pages()->select( [ 'id', 'title' ] )->setType( 'website' )->setLanguage( $langId )->fetch( -1 ) ) ];
        }
    
        $fields['langs'] = [ 'type' => 'group', 'fields' => $langs, 'grouped' => false ];

        $form = new \markup\front_end\form_fields( $fields + [
            'button'  => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $options    = [];
        $settings   = get_option_json( 'terms_of_use', [] );

        if( !empty( $settings ) )
        $form->setValues( $settings );

        $fields = $form->build();
        $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'tos' ] );
        $attributes['enctype']      = 'multipart/form-data';

        $markup = '<form class="form tos_settings_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'tos_settings_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_owner_subscription( array $attributes = [] ) {
        $plans  = new \query\plans\plans;
        $plans  = $plans->fetch( -1 );

        $form   = new \markup\front_end\form_fields( [
            'plan'  => [ 'type' => 'select', 'label' => t( 'Plan' ), 'options' => array_map( function( $v ) {
                return esc_html( $v->name );
            }, $plans ), 'required' => 'required' ],
            'user'  => [ 'type' => 'text', 'label' => t( 'User' ), 'required' => 'required' ],
            [ 'type' => 'inline-group', 'grouped' => false, 'fields' => [
                'expiration'    => [ 'type' => 'select', 'label' => t( 'Months' ), 'options' => array_combine( range( 1, 24 ), range( 1, 24 ) ), 'when' => [ '=', 'data[custom_exp]', false ] ],
                'expiration_c'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'Expiration' ), 'when' => [ '=', 'data[custom_exp]', true ] ],
                'custom_exp'    => [ 'type' => 'checkbox', 'label' => t( 'Custom expiration' ), 'title' => t( 'Use a custom expiration date' ), 'classes' => 'asc' ],
            ] ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-subscription' ] );

        $markup = '<form class="form add_subscription_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_owner_subscription( object $plan, array $attributes = [] ) {
        $plans  = new \query\plans\plans;
        $plans  = $plans->fetch( -1 );

        $form   = new \markup\front_end\form_fields( [
            'plan'  => [ 'type' => 'select', 'label' => t( 'Plan' ), 'options' => array_map( function( $v ) {
                return esc_html( $v->name );
            }, $plans ), 'required' => 'required' ],
            'user'  => [ 'type' => 'text', 'label' => t( 'User' ), 'disabled' => 'disabled' ],
            'expiration'  => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'Expiration' ) ],
            'button'      => [ 'type' => 'button', 'label' => t( 'Save' ) ]
        ] );

        $form->setValues( [
            'plan'          => $plan->getPlanId(),
            'user'          => $plan->getUserId(),
            'expiration'    => custom_time( $plan->getExpiration(), 2, 'Y-m-d H:i:s' )
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-subscription', 'subscription' => $plan->getId() ] );

        $markup = '<form class="form edit_owner_subscription_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_owner_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function add_country( array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'iso3166'   => [ 'type' => 'text', 'label' => t( 'ISO 3166' ), 'required' => 'required' ],
            'language'  => [ 'type' => 'select', 'label' => t( 'Default language' ), 'options' => array_map( function( $v ) {
                return esc_html( $v['name'] );
            }, getLanguages() ), 'required' => 'required' ],
            'hourf'     => [ 'type' => 'select', 'label' => t( 'Hour format' ), 'options' => [ 12 => t( '12-hour clock' ), 24 => t( '24-hour clock' ) ], 'required' => 'required' ],
            'datef'     => [ 'type' => 'select', 'label' => t( 'Date format' ), 'options' => [ 'm/d/y' => 'm/d/y', 'd/m/y' => 'd/m/y', 'y/m/d' => 'y/m/d' ], 'required' => 'required' ],
            'timezones' => [ 'type' => 'textarea', 'label' => t( 'Timezones' ), 'required' => 'required' ],
            'fday'      => [ 'type' => 'select', 'label' => t( 'First day of the week' ), 'options' => [ 0 => t( 'Sunday' ), 1 => t( 'Monday' ) ], 'required' => 'required' ],
            'mformat'   => [ 'type' => 'select', 'label' => t( 'Money format' ), 'options' => [ '%s %a' => t( 'Symbol Number ($ 1.99)' ), '%a %s' => t( 'Number Symbol ( 1.99 $' ) ], 'required' => 'required' ],
            'mseparator'=> [ 'type' => 'select', 'label' => t( 'Money separator' ), 'options' => [ ' |,' => '1 000,99', ' |.' => '1 000,99', '.|,' => '1.000,99', ',|.' => '1,000.99' ], 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-country' ] );

        $markup = '<form class="form add_subscription_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function edit_country( object $country, array $attributes = [] ) {
        $form   = new \markup\front_end\form_fields( [
            'name'      => [ 'type' => 'text', 'label' => t( 'Name' ), 'required' => 'required' ],
            'iso3166'   => [ 'type' => 'text', 'label' => t( 'ISO 3166' ), 'required' => 'required' ],
            'language'  => [ 'type' => 'select', 'label' => t( 'Default language' ), 'options' => array_map( function( $v ) {
                return esc_html( $v['name'] );
            }, getLanguages() ), 'required' => 'required' ],
            'hourf'     => [ 'type' => 'select', 'label' => t( 'Hour format' ), 'options' => [ 12 => t( '12-hour clock' ), 24 => t( '24-hour clock' ) ], 'required' => 'required' ],
            'datef'     => [ 'type' => 'select', 'label' => t( 'Date format' ), 'options' => [ 'm/d/y' => 'm/d/y', 'd/m/y' => 'd/m/y', 'y/m/d' => 'y/m/d' ], 'required' => 'required' ],
            'timezones' => [ 'type' => 'textarea', 'label' => t( 'Timezones' ), 'required' => 'required' ],
            'fday'      => [ 'type' => 'select', 'label' => t( 'First day of the week' ), 'options' => [ 0 => t( 'Sunday' ), 1 => t( 'Monday' ) ], 'required' => 'required' ],
            'mformat'   => [ 'type' => 'select', 'label' => t( 'Money format' ), 'options' => [ '%s %a' => t( 'Symbol Number ($ 1.99)' ), '%a %s' => t( 'Number Symbol ( 1.99 $)' ) ], 'required' => 'required' ],
            'mseparator'=> [ 'type' => 'select', 'label' => t( 'Money separator' ), 'options' => [ ' |,' => '1 000,99', ' |.' => '1 000,99', '.|,' => '1.000,99', ',|.' => '1,000.99' ], 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Edit' ) ]
        ] );

        $form->setValues( [
            'name'      => $country->getName(),
            'iso3166'   => $country->getIso3166(),
            'language'  => $country->getLanguage(),
            'hourf'     => $country->getHourFormat(),
            'datef'     => $country->getDateFormat(),
            'timezones' => $country->getTimezonesStr(),
            'fday'      => $country->getFirstDay(),
            'mformat'   => $country->getMoneyFormat(),
            'mseparator'=> $country->getMoneySeparator()
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-country', 'country' => $country->getId() ] );

        $markup = '<form class="form edit_country_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_country_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function notif_subscribers( int $type = 1, array $attributes = [] ) {
        $typeStr    = 'will_expire';
        if( $type == 2 )
        $typeStr    = 'expired';

        $lastNotifWe= get_option( 'subnotif_will_expire' );
        $lastNotifE = get_option( 'subnotif_expired' );

        $fields     = [
            [ 'type' => 'inline-group', 'fields' => [
                'type'      => [ 'type' => 'select', 'label' => t( 'Send email notifications if subscriptions' ), 'options' => [
                    'will_expire'   => t( 'Will expire' ),
                    'expired'       => t( 'Have expired' )
                ], 'value' => $typeStr, 'required' => 'required' ],
                'interval'  => [ 'type' => 'select', 'label' => t( 'In' ), 'classes' => 'wa', 'when' => [ '=', 'data[type]', 'will_expire' ], 'options' => [
                    7   => sprintf( t( '%s days' ), 7 ),
                    6   => sprintf( t( '%s days' ), 6 ),
                    5   => sprintf( t( '%s days' ), 5 ),
                    4   => sprintf( t( '%s days' ), 4 ),
                    3   => sprintf( t( '%s days' ), 3 ),
                    2   => sprintf( t( '%s days' ), 2 ),
                    1   => t( '1 day' ),
                ], 'required' => 'required' ],
            ], 'grouped' => false ]
        ];

        if( $lastNotifWe )
        $fields['info1']    = [ 'type' => 'custom', 'callback' => sprintf( t( 'Last notification sent <strong>%s</strong>' ), custom_time( $lastNotifWe, 2 ) ), 'when' => [ '=', 'data[type]', 'will_expire' ] ];
        if( $lastNotifE )
        $fields['info2']    = [ 'type' => 'custom', 'callback' => sprintf( t( 'Last notification sent <strong>%s</strong>' ), custom_time( $lastNotifE, 2 ) ), 'when' => [ '=', 'data[type]', 'expired' ] ];
        $fields['button']   = [ 'type' => 'button', 'label' => t( 'Send' ) ];

        $form       = new \markup\front_end\form_fields( $fields );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'notif-subscribers' ] );

        $markup = '<form class="form notif_subscribers_form"' . \util\attributes::add_attributes( filters()->do_filter( 'notif_subscribers_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function remove_expired_subscriptions( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'days'  => [ 'type' => 'select', 'label' => t( 'Expiration time' ), 'options' => [
                60  => sprintf( t( '%s days' ), 60 ),
                30  => sprintf( t( '%s days' ), 30 ),
                15  => sprintf( t( '%s days' ), 15 ),
                7   => sprintf( t( '%s days' ), 7 ),
                6   => sprintf( t( '%s days' ), 6 ),
                5   => sprintf( t( '%s days' ), 5 ),
                4   => sprintf( t( '%s days' ), 4 ),
                3   => sprintf( t( '%s days' ), 3 ),
                0   => t( 'All expired subscriptions' )
            ], 'value' => 30, 'required' => 'required' ],
            'button'        => [ 'type' => 'button', 'label' => t( 'Remove' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'remove-expired-subscriptions' ] );

        $markup = '<form class="form remove_expired_subscriptions_form"' . \util\attributes::add_attributes( filters()->do_filter( 'remove_expired_subscriptions_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function autorenew_subscriptions( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            [ 'type' => 'custom', 'callback' => t( 'Subscriptions that are set to "auto-renew" will automatically renew.' ) ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Auto-renew now' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'website-options2', [ 'action2' => 'autorenew-subscriptions' ] );

        $markup = '<form class="form autorenew_subscriptions_form"' . \util\attributes::add_attributes( filters()->do_filter( 'autorenew_subscriptions_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

}
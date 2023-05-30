<?php

namespace visitor;

class forms {

    public static function login( array $attributes = [] ) {
        $userec = ( $recaptcha_key = get_option( 'recaptcha_key' ) ) && !empty( $recaptcha_key );
        $fields = [ 
            'email'     => [ 'type' => 'text', 'placeholder' => 'Email address/username', 'after_label' => '<i class="fas fa-envelope"></i>', 'required' => 'required' ],
            'password'  => [ 'type' => 'password', 'placeholder' => 'Password', 'after_label' => '<i class="fas fa-key"></i>', 'required' => 'required' ]
        ];

        if( $userec ) {
            $fields['captcha'] = [ 'type' => 'info', 'value' => t( 'This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and
            <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.' ) ];
            $fields['ca_code'] = [ 'type' => 'hidden' ];
            $fields['ca_code'] = [ 'type' => 'hidden' ];
        }

        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Sign In' ) ];

        $form = new \markup\front_end\form_fields( $fields );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'form-action', [ 'form' => 'login' ] );

        $markup = '<form id="login_form" class="form login_form"' . \util\attributes::add_attributes( filters()->do_filter( 'user_login_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        if( $userec ) {
        $markup .= '
        <script>
            function recaptcha_request() {
            grecaptcha.ready(function() {
                grecaptcha.execute( "' . esc_html( $recaptcha_key ) . '", { action: "login" } ).then( function( token ) {
                document.querySelector(\'[data-id="data[ca_code]"]\').value = token;
                });
            });
            }
            var login_form = document.getElementById( "login_form" );
            login_form.addEventListener( "submit", function() {
                recaptcha_request();
            });
            recaptcha_request();
        </script>';
        }

        return $markup;
    }

    public static function register( array $attributes = [] ) {
        $userec = ( $recaptcha_key = get_option( 'recaptcha_key' ) ) && !empty( $recaptcha_key );
        $fields = [ 
            'username'  => [ 'type' => 'text', 'placeholder' => t( 'Username' ), 'after_label' => '<i class="fas fa-user-circle"></i>', 'required' => 'required' ],
            'email'     => [ 'type' => 'text', 'placeholder' => t( 'Email address' ), 'after_label' => '<i class="fas fa-envelope"></i>', 'required' => 'required' ],
            'country'   => [ 'type' => 'select', 'placeholder' => t( 'Country' ), 'options' => array_map( function( $item ) {
                return esc_html( t( $item->name ) );
            }, ( new \query\countries )->orderBy( 'id' )->fetch( -1 ) ), 'after_label' => '<i class="fas fa-globe"></i>', 'value' => filters()->do_filter( 'default_country', 0 ), 'required' => 'required' ],
            'password'  => [ 'type' => 'password', 'placeholder' => t( 'Password' ), 'after_label' => '<i class="fas fa-key"></i>', 'required' => 'required' ],
            'password2' => [ 'type' => 'password', 'placeholder' => t( 'Confirm password' ), 'after_label' => '<i class="fas fa-key"></i>', 'required' => 'required' ],
            'surveyor'  => [ 'type' => 'checkbox', 'title' => t( 'Surveyor' ), 'description' => t( "You'll be able to create surveys" ) ],
            'agreed'    => [ 'type' => 'checkbox', 'label' => t( 'Terms of use' ), 'title' => sprintf( t( 'I agreed with the %s' ), '<a href="#" data-popup="' . ajax()->get_call_url( 'terms-of-use' ) . '">' . t( 'terms of use' ) . '</a>' ) ]
        ];

        if( $userec ) {
            $fields['captcha'] = [ 'type' => 'info', 'value' => t( 'This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and
            <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.' ) ];
            $fields['ca_code'] = [ 'type' => 'hidden' ];
        }

        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Register' ) ];
        $detectedLanguage = \util\etc::userCountry();

        $form   = new \markup\front_end\form_fields( $fields );

        if( $detectedLanguage )
        $form   ->setValue( 'country', strtolower( $detectedLanguage ) );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'form-action', [ 'form' => 'register' ] );

        $markup = '<form id="register_form" class="form register_form"' . \util\attributes::add_attributes( filters()->do_filter( 'user_register_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        if( $userec ) {
            $markup .= '
            <script>
                function recaptcha_request() {
                grecaptcha.ready(function() {
                    grecaptcha.execute( "' . esc_html( $recaptcha_key ) . '", { action: "register_form" } ).then( function( token ) {
                    document.querySelector(\'[data-id="data[ca_code]"]\').value = token;
                    });
                });
                }
                var register_form = document.getElementById( "register_form" );
                register_form.addEventListener( "submit", function() {
                    recaptcha_request();
                });
                recaptcha_request();
            </script>';
        }

        return $markup;
    }

    public static function reset_password( array $attributes = [] ) {
        $markup = $email = '';
        $users  = users();

        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['data']['email'] ) && ( $email = $_POST['data']['email'] ) ) { 
            if( !$users->setIdByEmail( $email ) || !$users->getObject() )
            $markup .= '<div class="msg error">' . t( 'Invalid email address' ) . '</div>';
        } else if( $_SERVER['REQUEST_METHOD'] == 'GET' && isset( $_GET['code'] ) && isset( $_GET['email'] ) && ( $email = $_GET['email'] ) ) {
            if( !$users->setIdByEmail( $email ) || !$users->getObject() )
            $markup .= '<div class="msg error">' . t( 'Invalid email address' ) . '</div>';
        }

        if( $users->getObject() && $users->actions()->checkVerificationCode( true, 3 ) ) {
            
            $form = new \markup\front_end\form_fields( [
                'code'      => [ 'type' => 'text', 'placeholder' => t( 'Authorization code' ), 'after_label' => '<i class="fas fa-lock-open"></i>', 'classes' => 't', 'value' => ( isset( $_GET['code'] ) ? (int) $_GET['code'] : '' ), 'required' => 'required' ],
                'email2'    => [ 'type' => 'text', 'placeholder' => t( 'Email address' ), 'after_label' => '<i class="fas fa-envelope"></i>', 'value' => esc_html( $email ), 'required' => 'required', 'disabled' => 'disabled' ],
                'password'  => [ 'type' => 'password', 'placeholder' => t( 'New password' ), 'after_label' => '<i class="fas fa-key"></i>', 'required' => 'required' ],
                'password2' => [ 'type' => 'password', 'placeholder' => t( 'Confirm new password' ), 'after_label' => '<i class="fas fa-key"></i>', 'required' => 'required' ],
                'email'     => [ 'type' => 'hidden', 'value' => esc_html( $email ) ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Reset password' ) ]
            ] );

            $fields = $form->build();
            $attributes['data-ajax'] = ajax()->get_call_url( 'form-action', [ 'form' => 'reset-password' ] );

            $markup .= '<form id="pr_form" class="form password_recovery_form"' . \util\attributes::add_attributes( filters()->do_filter( 'user_password_recovery_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $markup .= $fields;
            $markup .= '</form>';

            return $markup;
        }

        $userec = ( $recaptcha_key = get_option( 'recaptcha_key' ) ) && !empty( $recaptcha_key );
        $fields = [ 'email' => [ 'type' => 'text', 'placeholder' => t( 'Email address' ), 'after_label' => '<i class="fas fa-envelope"></i>', 'required' => 'required' ] ];

        if( $userec ) {
        $fields['captcha']  = [ 'type' => 'info', 'value' => t( 'This site is protected by reCAPTCHA and the Google
        <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and
        <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.' ) ];
        $fields['ca_code']  = [ 'type' => 'hidden' ];
        }
        
        $fields['button'] = [ 'type' => 'button', 'label' => t( 'Reset password' ) ];

        $form = new \markup\front_end\form_fields( $fields );

        $fields = $form->build();

        $markup .= '<form id="pr_form" method="POST" class="form reset_password_form"' . \util\attributes::add_attributes( filters()->do_filter( 'user_reset_password_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        if( $userec ) {
        $markup .= '
        <script>
            function recaptcha_request() {
            grecaptcha.ready(function() {
                grecaptcha.execute( "' . esc_html( $recaptcha_key ) . '", { action: "pr_form" } ).then( function( token ) {
                document.querySelector(\'[data-id="data[ca_code]"]\').value = token;
                });
            });
            }
            var pr_form = document.getElementById( "pr_form" );
            pr_form.addEventListener( "submit", function() {
                recaptcha_request();
            });
            recaptcha_request();
        </script>';
        }
        
        return $markup;
    }

    public static function twostepsauth( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'code'      => [ 'type' => 'text', 'placeholder' => t( 'Authorization code' ), 'after_label' => '<i class="fas fa-lock-open"></i>', 'classes' => 't', 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Verify' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'form-action', [ 'form' => 'confirm-login' ] );

        $markup = '<form id="twostepsauth_form" class="form other_form twostepsauth_form"' . \util\attributes::add_attributes( filters()->do_filter( 'twostepsauth_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }

    public static function email_verification( array $attributes = [] ) {
        $form = new \markup\front_end\form_fields( [
            'code'      => [ 'type' => 'text', 'placeholder' => t( 'Authorization code' ), 'after_label' => '<i class="fas fa-lock-open"></i>', 'classes' => 't', 'required' => 'required' ],
            'button'    => [ 'type' => 'button', 'label' => t( 'Verify' ) ]
        ] );

        $fields = $form->build();
        $attributes['data-ajax'] = ajax()->get_call_url( 'form-action', [ 'form' => 'confirm-email' ] );

        $markup = '<form id="email_verification_form" class="form other_form email_verification_form"' . \util\attributes::add_attributes( filters()->do_filter( 'email_verification_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        return $markup;
    }
  
}
<?php

/** EMPTY ALERT */
ajax()->add_call( 'empty-call', function() {
    if( !me() )
        return cms_json_encode( [ 'redirect' => admin_url( 'login' ), 'timeout' => 0 ] );
    return cms_json_encode( [ 'show_popup' => [ 'title' => '😤 ' . t( 'Oops'), 'content' => '<div class="tc"><h3 class="mb20">' . t( 'Wrong action' ) . '</h3>' . t( 'Contact us if you think this is an error' ) . '</h3></div>', 'remove_prev_all' => true ] ] );
});

/** TERMS OF USE */
ajax()->add_call( 'terms-of-use', function() {
    $option     = get_option_json( 'terms_of_use', [] );
    if( !isset( $option[getUserLanguage('locale_e')] ) || !( ( $pages = pages( (int) $option[getUserLanguage('locale_e')] ) )->getObject() ) )
    return cms_json_encode( [ 'content' => '<div class="msg info mb0">' . t( 'Select a page' ) . '</div>' ] );

    $content    = '<div class="pag-content">';
    $content    .= $pages->getContent();
    $content    .= '</div>';

    return cms_json_encode( [ 'title' => $pages->getTitle(), 'content' => $content ] );
} );

/** VISITOR ACTIONS: login, confirm login, confirm email, register, reset password */
ajax()->add_call( 'form-action', function() {
    if( !isset( $_GET['form'] ) ) {
        die;    
    }

    switch( $_GET['form'] ) {
        case 'login':
            $fa = new \user\visitor_form_actions;
            try {
                $type = $fa->login( $_POST['data'] );
                $opts = [ 'status' => 'success', 'msg' => t( "Successfully authenticated!" ), 'redirect' => admin_url(), 'timeout' => 1000 ];

                return cms_json_encode( $opts );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] );
            }
        break;

        case 'confirm-login':
            try {
                me()->actions()->verifyCode( $_POST['data'] );
                me()->actions()->confirmSession();
                $opts = [ 'status' => 'success', 'msg' => t( "Authentication confirmed!" ), 'redirect' => admin_url(), 'timeout' => 1000 ];

                return cms_json_encode( $opts );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] );
            }
        break;

        case 'confirm-email':
            try {
                me()->actions()->verifyCode( $_POST['data'], 2 );
                me()->actions()->confirmEmail();
                $opts = [ 'status' => 'success', 'msg' => t( "Email confirmed!" ), 'redirect' => admin_url(), 'timeout' => 1000 ];

                return cms_json_encode( $opts );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] );
            }
        break;

        case 'register':
            $fa = new \user\visitor_form_actions;
            try {
                $type = $fa->register( $_POST['data'] );
                $opts = [ 'status' => 'success', 'msg' => t( "Successfully registered!" ), 'redirect' => admin_url(), 'timeout' => 1000 ];

                return cms_json_encode( $opts );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] );
            }
        break;

        case 'reset-password':
            $fa = new \user\visitor_form_actions;
            try {
                $fa->reset_password( $_POST['data'] );
                $opts = [ 'status' => 'success', 'msg' => t( "Password changed!" ), 'redirect' => admin_url(), 'timeout' => 1000 ];
        
                return cms_json_encode( $opts );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] );
            }
        break;
    }
});

/** LOGOUT */
ajax()->add_call( 'logout', function() {
    if( !me() ) {
        return ;
    }

    $content = '<div class="tc"><h3 class="mb20">' . t( 'Are you leaving already? 😌' ) . '</h3>';
    $content .= '<a href="#" class="btn" data-ajax="logout2">' . t( 'Yes, end the session!' ) . '</a></div>';

    return cms_json_encode( [ 'title' => t( 'Logout' ), 'content' => $content ] );
});

ajax()->add_call( 'logout2', function() {
    try {
        me()->actions()->logout();
        return cms_json_encode( [ 'redirect' => admin_url(), 'timeout' => 0 ] );
    }
    catch( Exception $e ) { }
});

/** SWITCH LANGUAGE & COUNTRY */
ajax()->add_call( 'switch-language', function() {
    $langs  = getLanguages();
    $ajax   = isset( $_GET['type'] ) && $_GET['type'] == 'ajax';

    if( !isset( $_GET['lang'] ) || !isset( $langs[$_GET['lang']] ) ) {
        if( !$ajax )
            header( 'Location:' . site_url() );
        else
            return cms_json_encode( [ 'error' => true ] );
    }

    if( me() ) {
        if( !me()->actions()->switch_language( $_GET['lang'] ) ) {
            if( !$ajax )
                header( 'Location:' . site_url() );
            else
                return cms_json_encode( [ 'error' => true ] );
        }
    } else {
        $setLang = setcookie( 'site_lang', $_GET['lang'], strtotime( '+1 year' ) );
        if( !$setLang ) {
            if( !$ajax )
                header( 'Location:' . site_url() );
            else
                return cms_json_encode( [ 'error' => true ] );
        }
    }

    if( !$ajax )
        header( 'Location:' . site_url() . ( isset( $_GET['path'] ) ? '/' . esc_html( $_GET['path'] ) : '' ) );
    else
        return cms_json_encode( [ 'error' => false ] );
});

/** SURVEY ACTIONS */
ajax()->add_call( 'survey', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['collector'] ) || !( $collector = paidSurveys() )->getObject( $_GET['collector'] ) || !$collector->isVisible() || ( $survey = $collector->getSurveyObject() )->getStatus() != 4 ) {
        return ;
    }

    switch( $_GET['action2'] ) {
        case 'start':
            // Something is wrong, the visitor is not sending any data
            if( !isset( $_POST['data'] ) ) {
                return ;
            // A response is already going on
            } else if( ( $response = $collector->getResponse() ) ) {
                return cms_json_encode( [ 'redirect' => esc_url( $collector->getCollectorPermalink() ), 'timeout' => 0 ] );
            // Let's try to start the survey
            } else {
                try {
                    ( $fa = new \user\visitor_form_actions )->start_survey( $_POST['data'], $collector, $survey );
                    return cms_json_encode( [ 'redirect' => esc_url( $collector->getCollectorPermalink() ), 'timeout' => 0 ] );
                }
                catch( Exception $e ) {
                    return cms_json_encode( [ 'show_popup' => [ 'content' => '<div class="msg error big mb0">' . $e->getMessage() . '</div>' ] ] );
                }
            }
        break;

        case 'restart':
            if( ( $response = $collector->getResponse() ) ) {
                $response   = new \survey\response( $response );
                $response   ->restartResponse();
            }
            return cms_json_encode( [ 'redirect' => esc_url( $collector->getCollectorPermalink() ), 'timeout' => 0 ] );
        break;
    }
});

 // Send data
ajax()->add_call( 'send-survey', function() {
    if( !isset( $_POST['results'] ) ) {
        return cms_json_encode( [] );
    }

    $results    = new \query\survey\results( (int) $_POST['results'] );
    $surveys    = NULL;
    $neverExp   = false;

    // Self response
    if( isset( $_POST['isSelf'] ) ) {

        // Get the response
        if( !( $response = $results->getObject() ) ) {
            return cms_json_encode( [] );
        }

        $surveys    = $results->getSurvey();
        $neverExp   = true;
        

    // User/visitor response
    } else {

        // Get the response
        if( !( $response = $results->getResponse() ) )
        return cms_json_encode( [] );

    }

    $response = new \survey\response( $response, $surveys, $neverExp );

    if( isset( $_POST['data'] ) )
    $response->setData( $_POST['data'] );

    return cms_json_encode( $response->checkStep() );
});

 // Previous page
 ajax()->add_call( 'prev-page-survey', function() {
    if( !isset( $_POST['results'] ) ) {
        return cms_json_encode( [] );
    }

    $results    = new \query\survey\results( (int) $_POST['results'] );
    $surveys    = NULL;
    $neverExp   = false;

    // Self response
    if( isset( $_POST['isSelf'] ) ) {

        // Get the response
        if( !( $response = $results->getObject() ) ) {
            return cms_json_encode( [] );
        }

        $surveys    = $results->getSurvey();
        $neverExp   = true;
        

    // User/visitor response
    } else {

        // Get the response
        if( !( $response = $results->getResponse() ) ) {
            return cms_json_encode( [] );
        }

    }

    $response = new \survey\response( $response, $surveys, $neverExp );

    if( $response->goToPrevStep() ) {
        return cms_json_encode( [ 'reload' => true, 'timeout' => 0 ] );
    }

    return cms_json_encode( [] );
});
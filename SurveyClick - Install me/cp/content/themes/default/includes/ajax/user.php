<?php

// Not user
if( !me() )
    return ;

// Session requires confirmation
else if( !me()->loginConfirmed )
    return ;

// Require email confirmation
else if( !me()->hasEmailVerified() && (bool) get_option( 'femail_verify', false ) )
    return ;  

// Banned
else if( me()->isBanned() )
    return ;  

/** CREATE ORDER */
ajax()->add_call( 'create-order', function( ) {
    try {
        $url = me()->form_actions()->create_order( [ 'method' => 'paypal' ] );
        return cms_json_encode( [ 'redirect' => $url ] );
    }

    catch( \Exception $e ) { }
});

/** DEPOSIT */
ajax()->add_call( 'deposit', function() {
    $content = me()->forms()->deposit();
    $content .= '<hr />';
    $content .= '<a href="#" data-sh="> .avf">' . t( 'Do you have a voucher?' ) . '</a>';
    $content .= '<div class="avf mt20 hidden">';
    $content .= me()->forms()->add_user_voucher();
    $content .= '</div>';
    
    $my_vouchers = me()->getVouchers()->setType( 0 );
    if( $my_vouchers->count() ) {
        $content .= '<hr />';
        $content .= '<div class="form_lines">';
        $vouchers = new \query\vouchers;
        foreach( $my_vouchers->fetch() as $voucher ) {
            $my_vouchers->setObject( $voucher );
            $vouchers   ->setObject( $voucher );
            $content    .= '<div class="form_line form_dropdown">
                <div>
                    <span class="c2">' . $vouchers->getTitle() . '</span> <i class="fas fa-angle-down"></i>
                </div>
                <div>
                    <div class="form_lines">
                        <div class="form_line">
                            <label>' . t( 'Voucher code' ) . '</label>
                            <div>' . esc_html( $vouchers->getCode() ) . '</div>
                        </div>
                        <div class="form_line">
                            <label>' . t( 'Expiration date' ) . '</label>
                            <div>' . ( $vouchers->getExpiration() ? custom_time( $vouchers->getExpiration(), 2 ) : '-' ) . '</div>
                        </div>
                    </div>
                    <div>
                        <a href="#" class="btn mt20" data-ajax="user-options3" data-data=\'' . cms_json_encode( [ 'action' => 'apply-voucher', 'id' => $my_vouchers->getId() ] ) . '\'>' . t( 'Reedem' ) . '</a>
                    </div>
                </div>
            </div>';
        }
        $content .= '</div>';
    }
    
    return cms_json_encode( [ 'title' => t( 'Deposit' ), 'content' => $content ] );
});

ajax()->add_call( 'deposit2', function() {
    try {
        $URL = me()->form_actions()->deposit( $_POST['data']  );
        return cms_json_encode( [ 'status' => 'success', 'msg' => sprintf( t( '%s if you are not redirect automaticaly.' ), '<a href="' . esc_url( $URL ) . '">' . t( 'Click here' ) . '</a>' ), 'callback' => '{
            "callback": "openLink",
            "URL": "' . esc_html( $URL ) . '"
        }' ] );
    }
    catch( Exception $e ) { 
        return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
    }
});

/** UPGRADE */
ajax()->add_call( 'upgrade', function() {
    if( empty( $_GET['planId'] ) || !( $plans = pricingPlans( (int) $_GET['planId'] ) )->getObject() ) return ;

    if( me()->myLimits()->isFree() || ( me()->myLimits()->getPlanId() !== $plans->getId() && me()->myLimits()->getPrice() <= $plans->getPrice() ) )
        return cms_json_encode( [ 'title' => sprintf( t( 'Upgrade to %s' ), esc_html( $plans->getName() ) ), 'content' => me()->forms()->upgrade( $plans ) ] );
    else if( me()->myLimits()->getPlanId() == $plans->getId() )
        return cms_json_encode( [ 'title' => sprintf( t( 'Extend subscription' ), esc_html( $plans->getName() ) ), 'content' => me()->forms()->extend_subscription( $plans ) ] );
    else if( me()->myLimits()->getPrice() > $plans->getPrice() )
        return cms_json_encode( [ 'title' => sprintf( t( 'Downgrade to %s' ), esc_html( $plans->getName() ) ), 'content' => me()->forms()->downgrade( $plans ) ] );
});

ajax()->add_call( 'upgrade2', function() {
    if( empty( $_GET['planId'] ) || !( $plans = pricingPlans( (int) $_GET['planId'] ) )->getObject() ) return ;

    if( !me()->myLimits()->isFree() ) {
        try {
            me()->form_actions()->cancel_subscription( me()->myLimits()->getSubscriptionId(), ( $_POST['data'] ?? [] ) );
        }
        catch( Exception $e ) {
            return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
        }
    }

    try {
        $return = me()->form_actions()->upgrade( $plans, $_POST['data']  );
        switch( $return['type'] ) {
            case 'redirect':
                return cms_json_encode( [ 'status' => 'success', 'msg' => sprintf( t( '%s if you are not redirect automaticaly.' ), '<a href="' . esc_url( $return['URL'] ) . '">' . t( 'Click here' ) . '</a>' ), 'callback' => '{
                    "callback": "openLink",
                    "URL": "' . esc_url( $return['URL'] ) . '"
                }' ] );
            break;

            case 'reload':
                return cms_json_encode( [ 'status' => 'success', 'redirect' => admin_url(), 'timeout' => 0 ] );
            break;
        }
    }
    catch( Exception $e ) { 
        return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
    }
});

ajax()->add_call( 'extend-subscription', function() {
    if( empty( $_GET['planId'] ) || me()->myLimits()->isFree() || !( $plans = pricingPlans( (int) $_GET['planId'] ) )->getObject() || $plans->getId() !== me()->myLimits()->getPlanId() ) return ;

    try {
        $return = me()->form_actions()->extend_subscription( $plans, $_POST['data']  );
        switch( $return['type'] ) {
            case 'redirect':
                return cms_json_encode( [ 'status' => 'success', 'msg' => sprintf( t( '%s if you are not redirect automaticaly.' ), '<a href="' . esc_url( $return['URL'] ) . '">' . t( 'Click here' ) . '</a>' ), 'callback' => '{
                    "callback": "openLink",
                    "URL": "' . esc_url( $return['URL'] ) . '"
                }' ] );
            break;

            case 'reload':
                return cms_json_encode( [ 'status' => 'success', 'redirect' => admin_url(), 'timeout' => 0 ] );
            break;
        }
    }
    catch( Exception $e ) { 
        return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
    }
});


/** WITHDRAW */
ajax()->add_call( 'withdraw', function() {
    return cms_json_encode( [ 'title' => t( 'Method' ), 'content' => me()->forms()->withdraw() ] );
});

ajax()->add_call( 'withdraw2', function() {
    try {
        me()->form_actions()->withdraw( $_POST['data']  );
        return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Your request has been sent' ) ] );
    }
    catch( Exception $e ) { 
        return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
    }
});

/** USER OPTIONS */
ajax()->add_call( 'user-options', function() {
    if( !isset( $_POST['options']['action'] ) ) return ;

    switch( $_POST['options']['action'] ) {
        case 'edit-profile':
            return cms_json_encode( [ 'title' => t( 'Edit profile' ), 'content' => me()->forms()->edit_profile() ] );
        break;
        
        case 'change-password':
            return cms_json_encode( [ 'title' => t( 'Change password' ), 'content' => me()->forms()->change_password() ] );
        break;

        case 'become-surveyor':
            return cms_json_encode( [ 'title' => t( 'Become a surveyor' ), 'content' => me()->forms()->become_surveyor() ] );
        break;

        case 'preferences':
            return cms_json_encode( [ 'title' => t( 'Preferences' ), 'content' => me()->forms()->edit_preferences() ] );
        break;

        case 'payout-options':
            return cms_json_encode( [ 'title' => t( 'Payout options' ), 'content' => me()->forms()->payout_options() ] );
        break;

        case 'privacy-options':
            return cms_json_encode( [ 'title' => t( 'Privacy options' ), 'content' => me()->forms()->privacy_options() ] );
        break;

        case 'security-options':
            return cms_json_encode( [ 'title' => t( 'Security options' ), 'content' => me()->forms()->security_options() ] );
        break;

        case 'teams':
            $classes    = [ 'np' ];
            $content    = '
            <div class="table ns nb mb0">';

            $membership = new \query\team\user_teams;
            $membership ->setUserId( me()->getId() );

            $markup     = '';

            foreach( $membership->fetch( -1 ) as $m ) {
                $membership ->setObject( $m );
                $team       = $membership->getTeam();

                $markup .= '
                <div class="td">
                    <div>' . esc_html( $team->getName() ) . '</div>
                    <div class="df">';
                    if( !$membership->isApproved() ) {
                        $markup .= '
                        <div class="mla">
                            <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'approve-invitation', 'team' => $team->getId() ] ) ) . '\'>' . t( 'Approve' ) . '</a>
                        </div>';
                    } else if( me()->getTeamId() == $team->getId() ) {
                        $markup .= '
                        <div class="mla">
                            <a href="#" class="btn disabled">' . t( 'Change' ) . '</a>
                        </div>';
                    } else {
                        $markup .= '
                        <div class="mla">
                            <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'change-team', 'team' => $team->getId() ] ) ) . '\'>' . t( 'Change' ) . '</a>
                        </div>';
                    }
                    $markup .= '
                    </div>
                </div>';
            }

            $content .= $markup;
            $content .= '
            </div>';

            if( $markup == '' ) {
                $content    = '<div class="msg info mb0">' . t( "You have no team or invites" ) . '</div>';
                $classes    = [];
            }

            return cms_json_encode( [ 'title' => t( 'My teams' ), 'content' => $content, 'classes' => $classes ] );
        break;

        case 'invite-a-friend':
            $content = '<strong class="df mb20">' . t( 'Invite your friends using this URL:' ) . '</strong>';
            $content .= '
            <div class="form_group mb40">
                <div class="form_line">
                    <input type="text" name="data[link]" value="' . site_url( '?ref=' . me()->getId() ) . '" readonly>
                </div>
                <div class="form_line wa mta">
                    <a href="#" class="btn">' . t( 'Copy' ) . '</a>
                </div>
            </div>';

            $programs = get_option_json( 'ref_system' );

            $content .= '<h3 class="mb40 tc">' . t( "You'll get loyalty stars!" ) . '</h3>';
            $content .= '<div class="rws">';

            if( !empty( $programs['reg'][1] ) ) {
                $content .= '<div class="rw">';
                $content .= '<h2>' . (int) $programs['reg'][1] . '</h2>';
                $content .= t( "For every verified friend you invite" );
                if( isset( $programs['reg'][2] ) ) {
                    $content .= '<div class="tl mt20 mb20"><strong>' . t( 'Earn more' ) . '</strong></div>';
                    array_shift( $programs['reg'] );
                    $content .= '
                    <div class="table t2 sm">
                    <div class="tr">
                        <div>' . t( 'Level' ) . '</div>
                        <div>' . t( 'Stars' ) . '</div>
                    </div>';

                    $lvl = 2;
                    array_walk( $programs['reg'], function( $v, $k ) use ( &$content, &$lvl ) {
                        $content .= '
                        <div class="td">
                            <div>' . $lvl++ . '</div>
                            <div>' . $v . '</div>
                        </div>';
                    });

                    $content .= '</div>';
                }
                $content .= '</div>';
            }

            if( !empty( $programs['eachupgrade'][1] ) ) {
                $content .= '<div class="rw">';
                $content .= '<h2>' . (int) $programs['eachupgrade'][1] . '</h2>';
                $content .= t( "For each upgrade of your friend" );
                if( isset( $programs['eachupgrade'][2] ) ) {
                    $content .= '<div class="tl mt20 mb20"><strong>' . t( 'Earn more' ) . '</strong></div>';
                    array_shift( $programs['eachupgrade'] );
                    $content .= '
                    <div class="table t2 sm">
                    <div class="tr">
                        <div>' . t( 'Level' ) . '</div>
                        <div>' . t( 'Stars' ) . '</div>
                    </div>';

                    $lvl = 2;
                    array_walk( $programs['eachupgrade'], function( $v, $k ) use ( &$content, &$lvl ) {
                        $content .= '
                        <div class="td">
                            <div>' . $lvl++ . '</div>
                            <div>' . $v . '</div>
                        </div>';
                    });

                    $content .= '</div>';
                }
                $content .= '</div>';
            }

            $content .= '</div>';

            return cms_json_encode( [ 'title' => t( 'Invite a friend' ), 'content' => $content ] );
        break;

        case 'view-report':
            if( !isset( $_POST['options']['report'] ) ) return ;
            
            $sReport    = new \query\survey\shared_reports;
            $uReport    = $sReport->infoReportUser( (int) $_POST['options']['report'], me()->getId() );
            if( !$uReport ) return ;
            
            $sReport    ->setObject( $uReport );
            $report     = $sReport->getReport();
            if( !$report->getObject() ) return ;

            $survey     = surveys( (int) $report->getSurveyId() );
            if( !$survey->getObject() ) return ;

            $smarkup    = $survey->reportMarkup( $report->getId() );
            $options    = $report->getOptions();

            if( !$smarkup ) return cms_json_encode( [ 'content' => '' ] );

            $markup = '<div class="table ns mb0 report_window">';

            foreach( $smarkup->questions() as $question ) {
                if( $question )
                $markup .= '<div class="td">' . $question . '</div>';
            }

            $markup .= '</div>';

            $result                 = [];
            $result['title']        = esc_html( $survey->getName() );
            $result['content']      = $markup;
            $result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
                "callback": "init_survey_chart2",
                "container": ".table.report_window",
                "placeholders": ' . cms_json_encode( $smarkup->getPlaceholders() ) . ',
                "data": ' . cms_json_encode( $smarkup->getData() ) . '
            }' ];
            $result['classes']      = [ 's2' ];

            return cms_json_encode( $result );
        break;

        case 'my-subscription':
            if( me()->myLimits()->isFree() )
            return ;

            $form   = new \markup\front_end\form_fields( [
                [ 'type' => 'custom2', 'callback' => function() {
                    return '
                    <div class="table t2 oa dfc wa ns mb0 form_line">
                        <div class="oa w100p">        
                            <div class="td"><div>' . t( 'Plan' ) . '</div><div class="wa">' . esc_html( me()->myLimits()->getPlanName() ) . '</div></div>
                            <div class="td"><div>' . t( 'Expiration' ) . '</div><div class="wa">' . custom_time( me()->myLimits()->expiration(), 2 ) . ' <a href="#" data-popup="' . ajax()->get_call_url( 'upgrade', [ 'planId' => me()->myLimits()->getPlanId() ] ) . '" class="ml20">' . t( 'Extend' ) . '</a></div></div>
                            <div class="td"><div>' . t( 'Auto-renewal' ) . '</div>
                                <div class="wa">
                                    <div class="chbxes">
                                        <div>
                                            <input type="checkbox" name="data[auto-renew]" id="data[auto-renew]"' . ( me()->limits()->autorenew() ? ' checked' : '' ) . '>
                                            <label for="data[auto-renew]"><span>' . t( 'Activate' ) . '</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
                } ],
                [  'type' => 'inline-group', 'fields' => [
                    'buttonx'    => [ 'type' => 'custom', 'callback' => function() {
                        return '<a href="' . admin_url( 'upgrade' ) . '">' . t( 'view plans' ) . '</a> | <a href="#" data-popup="user-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'cancel-subscription' ] ) ) . '\'>' . t( 'cancel subscription' ) . '</a>';
                    } ],
                    'button'    => [ 'type' => 'button', 'label' => t( 'Save' ), 'when' => [ '=', 'data[auto-renew]', !me()->myLimits()->autorenew() ], 'classes' => 'wa mla' ]
                ] ]
            ] );
    
            $form->setValues( [ 'auto-renew' => me()->myLimits()->autorenew() ] );

            $fields                     = $form->build();
            $attributes                 = [];
            $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'my-subscription' ] );
    
            $content = '<form id="my_subscription_form" class="form edit_my_subscription_form"' . \util\attributes::add_attributes( filters()->do_filter( 'edit_my_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => t( 'My subscription' ), 'content' => $content ] );
        break;

        case 'cancel-subscription':
            if( me()->myLimits()->isFree() ) return ;

            $form   = new \markup\front_end\form_fields( [
                [ 'type' => 'custom2', 'callback' => function() {
                    return '
                    <div class="msg alert">' . sprintf( t( "Your subscription is active and expires on %s. If you cancel it, all benefits will be lost. In case you don't want to continue the subscription, you can disable 'auto-renewal' and your subscription will be active until it expires." ), custom_time( me()->myLimits()->expiration(), 2 ) ) . '</div>';
                } ],
                'agree'     => [ 'type' => 'checkbox', 'title' => t( 'Cancel subscription now' ) ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Cancel subscription' ), 'when' => [ '=', 'data[agree]', true ] ]
            ] );
    
            $fields                     = $form->build();
            $attributes                 = [];
            $attributes['data-ajax']    = ajax()->get_call_url( 'user-options2', [ 'action2' => 'cancel-subscription' ] );

            $content = '<form id="cancel_subscription_form" class="form cancel_subscription_form"' . \util\attributes::add_attributes( filters()->do_filter( 'cancel_subscription_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => t( 'Cancel subscription' ), 'content' => $content, 'remove_prev' => true ] );
        break;

        case 'create-team':
            if( !me()->myLimits()->teamMembers() ) {
                $content = '<div class="msg info">' . t( "You don't have a plan for teams" ) . '</div>';
                $content .= '<div class="tc"><a href="' . admin_url( 'upgrade' ) . '" class="btn">' . t( 'view plans' ) . '</a></div>';
            } else
                $content = me()->forms()->create_team();

            return cms_json_encode( [ 'title' => t( 'Create a team' ), 'content' => $content ] );
        break;

        case 'identity-verification':
            if( me()->intents(1)->count() )
                $content = '<div class="msg alert mb0">' . t( 'Your request is pending' ) . '</div>';
            else
                $content = me()->forms()->identity_verification();
            return cms_json_encode( [ 'title' => t( 'Verify your identity' ), 'content' => $content ] );
        break;
    }
});

ajax()->add_call( 'user-options2', function() {
    if( !isset( $_GET['action2'] ) ) return ;

    switch( $_GET['action2'] ) {
        case 'edit-profile':
            try {
                me()->form_actions()->edit_profile( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Profile edited' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
        
        case 'change-password':
            try {
                me()->form_actions()->change_password( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Password changed successfully' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'become-surveyor':
            try {
                me()->form_actions()->become_surveyor( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'redirect' => admin_url( '?viewAs=4' ), 'timeout' => 0 ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'preferences':
            try {
                me()->form_actions()->preferences( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'payout-options':
            try {
                me()->form_actions()->payout_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'privacy-options':
            try {
                me()->form_actions()->privacy_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'security-options':
            try {
                me()->form_actions()->security_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-survey':
            try {
                $id = me()->form_actions()->add_survey( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'add-survey-step2', 'data' => [ 'survey' => $id ], 'remove_prev' => true ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-voucher':
            try {
                $id = me()->form_actions()->add_user_voucher( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'deposit', 'data' => [ 'survey' => $id ], 'remove_prev' => true ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'my-subscription':
            if( me()->myLimits()->isFree() )
            return ;

            try {
                me()->form_actions()->edit_subscription( me()->myLimits()->getSubscriptionId(), ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'cancel-subscription':
            if( me()->myLimits()->isFree() )
            return ;

            try {
                me()->form_actions()->cancel_subscription( me()->myLimits()->getSubscriptionId(), ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'redirect' => admin_url(), 'timeout' => 0 ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'create-team':
            if( !me()->myLimits()->teamMembers() )
            return ;

            try {
                me()->form_actions()->create_team( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'redirect' => admin_url(), 'timeout' => 0 ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'identity-verification':
            try {
                me()->form_actions()->identity_verification( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [
                    'content' => showMessage( t( 'Your request has been sent' ), t( 'We will review your request as soon as possible' ), '<i class="fas fa-check"></i>' ), 
                    'remove_prev_all' => true ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'user-options3', function() {
    if( !isset( $_POST['action'] ) ) return ;

    switch( $_POST['action'] ) {
        case 'apply-voucher':
            if( !isset( $_POST['id'] ) ) return ;

            if( me()->actions()->applyVoucher( (int) $_POST['id'], 0 ) ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'deposit', 'data' => [ 'survey' => 2 ], 'remove_prev' => true ] ] );
            }
            return cms_json_encode( [ ] ); 
        break;

        case 'approve-invitation':
            if( !isset( $_POST['team'] ) ) return ;

            $teams = new \query\team\teams( (int) $_POST['team'] );
            if( !( $membership = $teams->userIsMember( me()->getId() ) ) || $membership->approved !== 0 ) return ;

            try {
                me()->actions()->approveTeamInvitation( (int) $_POST['team'] );

                $location = $_POST['location'] ?? '';

                switch( $location ) {
                    case 'alerts':
                        $res                = [];
                        $res['callback']    = '{
                            "callback": "markup_replace_closest",
                            "el": ".lnks",
                            "text": "' . t( 'Invitation accepted' ) . '"
                        }';
                        if( !me()->getTeamId() ) {
                            if( me()->actions()->changeTeam( $teams->getId() ) )
                            $res['redirect']    = admin_url( 'myteam' );
                            $res['timeout']     = 2000;
                        }
                        return cms_json_encode( $res );
                    break;

                    default:
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_changer",
                            "html": "' . base64_encode( '<a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'change-team', 'team' => $teams->getId() ] ) ) . '\'>' . t( 'Change' ) . '</a>' ) . '"
                        }' ] );
                }
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'reject-invitation':
            if( !isset( $_POST['team'] ) ) return ;

            $teams = new \query\team\teams( (int) $_POST['team'] );
            if( !( $membership = $teams->userIsMember( me()->getId() ) ) || $membership->approved !== 0 ) return ;

            try {
                me()->actions()->rejectTeamInvitation( (int) $_POST['team'] );

                $location = $_POST['location'] ?? '';

                switch( $location ) {
                    case 'alerts':
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_replace_closest",
                            "el": ".lnks",
                            "text": "' . t( 'Invitation rejected' ) . '"
                        }' ] );
                    break;

                    default:
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_changer",
                            "html": "' . base64_encode( '<a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'change-team', 'team' => $teams->getId() ] ) ) . '\'>' . t( 'Change' ) . '</a>' ) . '"
                        }' ] );
                }

            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'change-team':
            if( !isset( $_POST['team'] ) ) return ;

            $teams = me()->myTeam( (int) $_POST['team'] );
            if( !$teams ) return ;

            try {
                me()->actions()->changeTeam( $teams->getId() );
                return cms_json_encode( [ 'redirect' => admin_url( 'myteam' ), 'timeout' => 0 ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'approve-response':
            if( !isset( $_POST['response'] ) ) return ;

            $res_owner = new \query\survey\response_owner( (int) $_POST['response'] );
            if( !$res_owner->getObject() ) return ;
            
            $response = $res_owner->getResultObject();

            // Check if the survey is shared and has enough permissions
            if( $res_owner->isTeamSurvey() ) {
                $teams = me()->myTeam( $res_owner->getTeamId() );
                if( !$teams || !me()->manageTeam( 'approve-response' ) ) return ;
            // Check if the user owns the survey
            } else if( $res_owner->getOwnerId() != me()->getId() ) return ;

            $response = new \survey\response( $response->getObject() );
            
            try {
                $response->validateResponse();

                $location = $_POST['location'] ?? '';

                switch( $location ) {
                    case 'table':
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_delete_this"
                        }' ] );
                    break;

                    case 'table-pending':
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_delete_table_td"
                        }' ] );
                    break;
                    
                    default:
                    return cms_json_encode( [ 'callback' => '{
                        "callback": "markup_replace_closest",
                        "el": ".lnks",
                        "text": "' . t( 'Approved' ) . '"
                    }' ] );
                }
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'reject-response':
            if( !isset( $_POST['response'] ) ) return ;

            $res_owner = new \query\survey\response_owner( (int) $_POST['response'] );
            if( !$res_owner->getObject() ) return ;
            
            $response = $res_owner->getResultObject();

            // Check if the survey is shared and has enough permissions
            if( $res_owner->isTeamSurvey() ) {
                $teams = me()->myTeam( $res_owner->getTeamId() );
                if( !$teams || !me()->manageTeam( 'reject-response' ) ) return ;
            // Check if the user owns the survey
            } else if( $res_owner->getOwnerId() != me()->getId() ) return ;

            try {
                me()->actions()->rejectResponse( $response->getId() );

                $location = $_POST['location'] ?? '';

                switch( $location ) {
                    case 'table':
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_delete_this"
                        }' ] );
                    break;

                    case 'table-pending':
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_delete_table_td"
                        }' ] );
                    break;

                    default:
                        return cms_json_encode( [ 'callback' => '{
                            "callback": "markup_replace_closest",
                            "el": ".lnks",
                            "text": "' . t( 'Rejected' ) . '"
                        }' ] );
                }
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;
    }
});

/** WEBSITE OPTIONS */
ajax()->add_call( 'website-options', function() {
    if( !me()->isAdmin() || !isset( $_POST['options']['action'] ) ) {
        return ;
    }

    switch( $_POST['options']['action'] ) {
        case 'general':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'General' ), 'content' => $forms->general_settings() ] );
        break;

        case 'paypal':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Paypal' ), 'content' => $forms->paypal_settings() ] );
        break;

        case 'stripe':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Stripe' ), 'content' => $forms->stripe_settings() ] );
        break;

        case 'email':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Email settings' ), 'content' => $forms->email_settings() ] );
        break;

        case 'prices':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Prices' ), 'content' => $forms->prices_settings() ] );
        break;

        case 'invoicing':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Invoicing' ), 'content' => $forms->invoicing_settings() ] );
        break;

        case 'kyc':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Know Your Customers' ), 'content' => $forms->kyc_settings() ] );
        break;

        case 'tos':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Terms of use pages' ), 'content' => $forms->tos_settings() ] );
        break;

        case 'plans':
            if( !me()->isOwner() ) return;
            $content    = '';
            $plans      = new \query\plans\plans;
            $plans      ->setVisible( 1, '>=' )
                        ->orderBy( 'price' );
            $limits     = new \query\user_limits;
            $limits     ->setFreeSubscription();
            $content    .= '
            <div class="form_lines">
            <div class="form_line form_dropdown">
                <div>
                    <span class="c2">' . t( 'Default' ) . '</span>
                    <span>' . esc_html( $limits->getPlanName() ) . '</span><i class="fas fa-angle-down"></i>
                </div>
                <div>
                    <ul class="df">
                        <li><a href="#" class="btn" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit-plan', 'plan' => 0 ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    </ul>
                </div>
            </div>';

            foreach( $plans->fetch( -1 ) as $plan ) {
                $plans->setObject( $plan );
                $content .= '
                <div class="form_line form_dropdown">
                    <div>
                        <span class="c2">' . ( $plans->getId() == 0 ? t( 'Default' ) : $plans->getPriceF() ) . '</span>
                        <span>' . ( $plans->isVisible() != 2 ? '<i class="fas fa-eye-slash"></i> ' : '' ) . esc_html( $plans->getName() ) . '</span><i class="fas fa-angle-down"></i>
                    </div>
                    <div>
                        <ul class="mr df">
                            <li><a href="#" class="btn" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit-plan', 'plan' => $plans->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                            <li><a href="#" class="btn" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-plan-offer', 'plan' => $plans->getId() ] ) ) . '\'>' . t( 'New offer' ) . '</a></li>
                            <li class="mla"><a href="#" class="btn" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'hide-plan', 'plan' => $plans->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';

                $offers = $plans->activeOffers();

                if( $offers->count() ) {
                    $content .= '
                    <div>
                    <div class="table sm mb0">
                    <div class="tr">
                        <div></div>
                        <div>' . t( 'Starts' ) . '</div>
                        <div>' . t( 'Expires' ) . '</div>
                        <div class="wa"></div>
                        <div class="wa"></div>
                    </div>';

                    $offers->orderBy( 'price' );

                    foreach( $offers->fetch( -1 ) as $offer ) {
                        $offers->setObject( $offer );
                        $content .= '
                        <div class="td">
                            <div class="tl">
                                <strong>' . $offers->getPriceF() . '</strong>
                            </div>                            
                            <div>' . custom_time( $offers->getStartDate(), 2 ) . '</div>
                            <div>' . ( $offers->getEndDate() ? custom_time( $offers->getEndDate(), 2 ) : t( 'Never expires' ) ) . '</div>
                            <div class="wa">
                                <a href="#" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit-plan-offer', 'offer' => $offers->getId() ] ) ) . '\'><i class="fas fa-edit"></i></a>
                                <a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-plan-offer', 'offer' => $offers->getId() ] ) ) . '\'><i class="fas fa-times"></i></a>
                            </div>
                        </div>';
                    }

                    $content .= '
                    </div>
                    </div>';
                }

                $content .= '
                </div>';
            }

            $content .= '
            </div>';

            $title = '
            <ul class="btnset mla">
                <li><a href="#" class="btn" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-plan' ] ) ) . '\'>' . t( 'New plan' ) . '</a></li>
                <li>
                    <a href="#"><i class="fas fa-chevron-down aic"></i></a>
                    <ul class="btnset top">
                        <li><a href="#" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'trash-plans' ] ) ) . '\'>Trash</a></li>
                    </ul>
                </li>
            </ul>';

            return cms_json_encode( [
                'title'         => t( 'Payment plans' ), 
                'title2'        => $title, 
                'content'       => $content ] );
        break;

        case 'trash-plans':
            if( !me()->isOwner() ) return;
            $content = '
            <div class="form_lines">
            <div class="form_line form_dropdown s">
            <div>
                <span>' . t( 'Deleted plans' ) . '</span><i class="fas fa-angle-down"></i>
            </div>';

            $plans      = new \query\plans\plans;
            $plans      ->orderBy( 'price' )
                        ->setVisible( 0 );
            $list       = $plans->fetch( -1 );

            if( $plans->count() ) {
                $content .= '<div>';
                foreach( $plans->fetch( -1 ) as $plan ) {
                    $plans->setObject( $plan );
                    $content .= '
                    <div class="form_line form_dropdown">
                        <div>
                            <span class="c2">' . ( $plans->getId() == 0 ? t( 'Default' ) : $plans->getPriceF() ) . '</span>
                            <span>' . esc_html( $plans->getName() ) . '</span><i class="fas fa-angle-down"></i>
                        </div>
                        <div>
                            <ul class="df">
                                <li><a href="#" class="btn" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'unhide-plan', 'plan' => $plans->getId() ] ) ) . '\'>' . t( 'Restore' ) . '</a></li>
                                <li class="mla"><a href="#" class="btn" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-plan', 'plan' => $plans->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                            </ul>
                        </div>
                    </div>';
                }
                $content .= '</div>';
            } else {
                $content .= '
                <div>
                    <div class="msg info mb0">' . t( 'There are no deleted plans' ) . '</div>
                </div>';
            }

            $content .= '
            </div>
            </div>';

            return cms_json_encode( [ 
                'title'         => t( 'Trash' ), 
                'content'       => $content ] );
        break;

        case 'referral':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Referral' ), 'content' => $forms->referral_settings() ] );
        break;

        case 'seo':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'SEO Settings' ), 'content' => $forms->seo_settings() ] );
        break;

        case 'add-plan':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add plan' ), 'content' => $forms->add_plan() ] );
        break;

        case 'edit-plan':
            if( !me()->isOwner() || !isset( $_POST['options']['plan'] ) ) return ;
            $planId = (int) $_POST['options']['plan'];
            $plans  = new \query\plans\plans( $planId );
            if( !$plans->getObject() && $planId !== 0 )
            return ;

            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Edit plan' ), 'content' => $forms->edit_plan( $plans ) ] );
        break;

        case 'add-plan-offer':
            if( !me()->isOwner() || !isset( $_POST['options']['plan'] ) ) return ;
            $planId = (int) $_POST['options']['plan'];
            $plans  = new \query\plans\plans( $planId );
            if( !$plans->getObject() && $planId !== 0 )
            return ;

            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => sprintf( t( 'New offer for %s' ), esc_html( $plans->getName() ) ), 'content' => $forms->add_plan_offer( $plans ) ] );
        break;

        case 'edit-plan-offer':
            if( !me()->isOwner() || !isset( $_POST['options']['offer'] ) ) return ;
            $offerId = (int) $_POST['options']['offer'];
            $offers  = new \query\plans\offers( $offerId );
            if( !$offers->getObject() && $offerId !== 0 )
            return ;

            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Edit offer' ), 'content' => $forms->edit_plan_offer( $offers ) ] );
        break;

        case 'security':
            if( !me()->isOwner() ) return;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Security' ), 'content' => $forms->security_settings() ] );
        break;

        case 'add-item-menu':
            $form   = new \markup\front_end\form_fields( [
                'type'  => [ 'type' => 'select', 'title' => t( 'Link type' ), 'options' => array_map( function( $v ) {
                    return $v['name'];
                }, menus()->getLinkTypes() ) ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Add' ) ]
            ] );
    
            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'add-item-menu' ] );
    
            $content = '<form id="add-item-menu" class="form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_item_menu_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => t( 'Reset menu' ), 'content' => $content ] );
        break;

        case 'reset-menu':
            if( !isset( $_POST['options']['menu'] ) || !isset( $_POST['options']['lang'] ) ) return ;

            $form   = new \markup\front_end\form_fields( [
                'agree'     => [ 'type' => 'checkbox', 'title' => t( 'Are you sure?' ), 'required' => 'required' ],
                'menu'      => [ 'type' => 'hidden', 'value' => $_POST['options']['menu'] ],
                'lang'      => [ 'type' => 'hidden', 'value' => $_POST['options']['lang'] ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Reset' ), 'when' => [ '=', 'data[agree]', true ] ]
            ] );
    
            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'website-options2', [ 'action2' => 'reset-menu' ] );
    
            $content = '<form id="reset_menu_form" class="form reset_menu_form"' . \util\attributes::add_attributes( filters()->do_filter( 'reset_menu_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => t( 'Reset menu' ), 'content' => $content ] );
        break;
    }
});

ajax()->add_call( 'website-options2', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) ) {
        return ;
    }

    switch( $_GET['action2'] ) {
        case 'general':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_general_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'paypal':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_paypal_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'stripe':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_stripe_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'email':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_email_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-email-template':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->edit_email_template( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'prices':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_prices_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'invoicing':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_invoicing_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'kyc':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_kyc_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'tos':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_tos_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-plan':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->add_plan( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Plan added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-plan':
            if( !me()->isOwner() || !isset( $_GET['plan'] ) ) return ;
            $planId = (int) $_GET['plan'];
            $plans  = new \query\plans\plans( $planId );
            if( !$plans->getObject() && $planId !== 0 ) return ;

            try {
                me()->form_actions()->edit_plan( $plans, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Plan edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-plan-offer':
            if( !me()->isOwner() || !isset( $_GET['plan'] ) ) return ;
            $planId = (int) $_GET['plan'];
            $plans  = new \query\plans\plans( $planId );
            if( !$plans->getObject() && $planId !== 0 ) return ;

            try {
                me()->form_actions()->add_plan_offer( $plans, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Offer added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-plan-offer':
            if( !me()->isOwner() || !isset( $_GET['offer'] ) ) return ;
            $offerId= (int) $_GET['offer'];
            $offers = new \query\plans\offers( $offerId );
            if( !$offers->getObject() && $offerId !== 0 ) return ;

            try {
                me()->form_actions()->edit_plan_offer( $offers, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Offer edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'referral':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_referral_levels( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'seo':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_seo_settings( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'security':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->website_security_options( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'clean-website':
            if( !me()->isOwner() ) return;
            try {
                me()->form_actions()->clean_website( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Website cleaned' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-item-menu':
            $type   = $_POST['data']['type'] ?? NULL;
            if( !$type ) return ;

            $types  = menus()->getLinkTypes();
            if( !isset( $types[$type] ) ) return;

            $form   = new \markup\front_end\form_fields;
            $id     = call_user_func( $types[$type]['form'], $form );

            $markup = '
            <li class="form_line form_dropdown" id="' . $id . '">
                <div>
                    <span></span>
                    <i class="fas fa-angle-down"></i>
                </div>
                <div>';
                    $fields = $form->build();            
                    $markup .= $fields;
                    $markup .= '
                    <div class="df mt25">
                        <a href="#" class="mla btn delcli">' . t( 'Delete' ) . '</a>
                    </div>
                </div>
                <ul class="sortable">' . ( !empty( $link['childs'] ) ? $this->menuLinksEdit( $link['childs'] ): '' ) . '</ul>
            </li>';
            
            try {
                return cms_json_encode( [ 'callback' => '{
                    "callback": "init_add_item_menu",
                    "markup": "' . base64_encode( $markup ) . '"
                }' ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'reset-menu':
            $menu = $_POST['data']['menu'] ?? NULL;
            $lang = $_POST['data']['lang'] ?? NULL;

            $r = [ 'callback' => '{
                "callback": "close_popup"
            }' ];

            if( $menu && $lang ) {
                me()->website_options()->deleteOption( $menu . ':' . $lang );
                $r['goto'] = [ 'path' => [ 'menus' ], 'options' => [ 'menu' => $menu, 'lang' => $lang ] ];
            }

            try {
                return cms_json_encode( $r );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'notif-subscribers':
            try {
                me()->form_actions()->notif_subscribers( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Notifications sent' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'remove-expired-subscriptions':
            me()->form_actions()->remove_expired_subscriptions( ( $_POST['data'] ?? [] ) );
            return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Expired subscriptions removed' ) ] );
        break;

        case 'autorenew-subscriptions':
            me()->form_actions()->autorenew_subscriptions( ( $_POST['data'] ?? [] ) );
            return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Subscriptions have been automatically renewed' ) ] );
        break;
    }
});

ajax()->add_call( 'website-actions', function() {
    if( !me()->isAdmin() || !isset( $_POST['data']['action'] ) ) {
        return ;
    }

    switch( $_POST['data']['action'] ) {
        case 'install-theme':
            if( !me()->isOwner() ) return ;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Install theme' ), 'content' => $forms->install_theme() ] );
        break;
        
        case 'install-plugin':
            if( !me()->isOwner() ) return ;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Install plugin' ), 'content' => $forms->install_plugin() ] );
        break;

        case 'theme-info':
            if( !me()->isOwner() ) return ;
            $themes = new \site\themes;
            if( !isset( $_POST['data']['theme'] ) || !$themes->getThemes()->readTheme( $_POST['data']['theme'] ) ) return '';

            if( !$themes->hasJsonFile() ) {
                $content = '<div class="msg alert mb0">' . t( 'No information' ) . '</div>';
            } else {
                $content = '
                <div class="form_lines mb0">';

                if( $themes->getURL() ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . t( 'Website' ) . '</label>
                        <div><a href="' . esc_html( $themes->getURL() ) . '">' . esc_html( $themes->getURL() ) . '</a></div>
                    </div>';
                }

                if( $themes->getAuthor() ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . t( 'Author' ) . '</label>
                        <div>' . esc_html( $themes->getAuthor() ) . '</div>
                    </div>';
                }

                if( $themes->getVersion() ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . t( 'Version' ) . '</label>
                        <div>' . esc_html( $themes->getVersion() ) . '</div>
                    </div>';
                }

                if( $themes->getRequiresPHPVersion() ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . t( 'Requires PHP Version' ) . '</label>
                        <div>' . esc_html( $themes->getRequiresPHPVersion() ) . '</div>
                    </div>';
                }

                if( $themes->getDescription() ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . t( 'Description' ) . '</label>
                        <div>' . esc_html( $themes->getDescription() ) . '</div>
                    </div>';
                }

                $content .= '
                </div>';
            }

            return cms_json_encode( [ 'title' => sprintf( t( 'Info: %s' ), esc_html( $themes->getName() ) ), 'content' => $content ] );
        break;
        
        case 'plugin-info':
            if( !me()->isOwner() ) return ;
            $plugins = new \site\plugins;
            if( !isset( $_POST['data']['plugin'] ) || !$plugins->getPlugin( $_POST['data']['plugin'] ) ) return ;

            $content = '
            <div class="form_lines mb0">';

            if( $plugins->getAuthor() ) {
                $content .= '
                <div class="form_line">
                    <label>' . t( 'Author' ) . '</label>
                    <div>' . esc_html( $plugins->getAuthor() ) . '</div>
                </div>';
            }

            if( $plugins->getAuthorURL() ) {
                $content .= '
                <div class="form_line">
                    <label>' . t( 'Author' ) . '</label>
                    <div>' . esc_html( $plugins->getAuthorURL() ) . '</div>
                </div>';
            }

            if( $plugins->getVersion() ) {
                $content .= '
                <div class="form_line">
                    <label>' . t( 'Version' ) . '</label>
                    <div>' . esc_html( $plugins->getVersion() ) . '</div>
                </div>';
            }

            if( $plugins->getRequiresPHPVersion() ) {
                $content .= '
                <div class="form_line">
                    <label>' . t( 'Requires PHP Version' ) . '</label>
                    <div>' . esc_html( $plugins->getRequiresPHPVersion() ) . '</div>
                </div>';
            }

            if( $plugins->getDescription() ) {
                $content .= '
                <div class="form_line">
                    <label>' . t( 'Description' ) . '</label>
                    <div>' . esc_html( $plugins->getDescription() ) . '</div>
                </div>';
            }

            $content .= '
            </div>';

            return cms_json_encode( [ 'title' => sprintf( t( 'Info: %s' ), esc_html( $plugins->getName() ) ), 'content' => $content ] );
        break;

        case 'delete-theme':
            if( !me()->isOwner() || !isset( $_POST['data']['theme'] ) || !themes()->getTheme( $_POST['data']['theme'] ) ) return '';

            $content = '<div class="msg info">' . t( 'All files inside the directory will be permanently deleted.' ) . '</div>';

            $form   = new \markup\front_end\form_fields( [
                'agree'     => [ 'type' => 'checkbox', 'title' => t( 'I understand' ), 'required' => 'required' ],
                'theme'     => [ 'type' => 'hidden', 'value' => themes()->getId() ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Delete' ), 'when' => [ '=', 'data[agree]', true ] ]
            ] );
    
            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'delete-theme' ] );
    
            $content .= '<form id="delete_theme_form" class="form delete_theme_form"' . \util\attributes::add_attributes( filters()->do_filter( 'delete_theme_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => sprintf( t( 'Delete: %s' ), esc_html( themes()->getName() ) ), 'content' => $content ] );
        break;

        case 'delete-plugin':
            if( !me()->isOwner() || !isset( $_POST['data']['plugin'] ) || !plugins()->getPlugin( $_POST['data']['plugin'] ) ) return '';

            $content = '<div class="msg info">' . t( 'All files inside the directory will be permanently deleted.' ) . '</div>';

            $form   = new \markup\front_end\form_fields( [
                'agree'     => [ 'type' => 'checkbox', 'title' => t( 'I understand' ), 'required' => 'required' ],
                'plugin'    => [ 'type' => 'hidden', 'value' => plugins()->getId() ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Delete' ), 'when' => [ '=', 'data[agree]', true ] ]
            ] );
    
            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'delete-plugin' ] );
    
            $content .= '<form id="delete_plugin_form" class="form delete_plugin_form"' . \util\attributes::add_attributes( filters()->do_filter( 'delete_plugin_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => sprintf( t( 'Delete: %s' ), esc_html( plugins()->getName() ) ), 'content' => $content ] );
        break;

        case 'theme-options':
            if( !isset( $_POST['data']['label'] ) ) return;

            $label      = $_POST['data']['label'];
            $options    = themes()->getOptions( $label );

            if( $options === NULL ) return ;

            $c_options  = get_theme_options();

            $form   = new \markup\front_end\form_fields( ( $options + [
                'label'     => [ 'type' => 'hidden' ],
                'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
            ] ) );

            $form   ->setValues( array_replace( $c_options, [
                'label'     => $label
            ] ) );

            $fields                     = $form->build();
            $attributes['data-ajax']    = ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'theme-options' ] );
    
            $content = '<form id="theme_options_form" class="form theme_options_form"' . \util\attributes::add_attributes( filters()->do_filter( 'theme_options_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
            $content .= $fields;
            $content .= '</form>';

            return cms_json_encode( [ 'title' => esc_html( $_POST['data']['label'] ), 'content' => $content ] );
        break;

        case 'add-category':
            if( !isset( $_POST['data']['type'] ) ) return ;
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add category' ), 'content' => $forms->add_category( $_POST['data']['type'] ) ] );
        break;

        case 'edit-category':
            if( !isset( $_POST['data']['category'] ) || !( $category = categories( $_POST['data']['category'] ) )->getObject() ) return '';
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $category->getName() ) ), 'content' => $forms->edit_category( $category ) ] );
        break;

        case 'add-voucher':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add voucher' ), 'content' => $forms->add_voucher() ] );
        break;

        case 'edit-voucher':
            if( !isset( $_POST['data']['voucher'] ) || !( $voucher = vouchers( $_POST['data']['voucher'] ) )->getObject() ) return '';
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Edit voucher' ), 'content' => $forms->edit_voucher( $voucher ) ] );
        break;

        case 'add-user':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add user' ), 'content' => $forms->add_user() ] );
        break;

        case 'add-subscription':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add subscription' ), 'content' => $forms->add_owner_subscription() ] );
        break;

        case 'edit-subscription':
            if( !isset( $_POST['data']['subscription'] ) || !( $subscription = subscriptions( $_POST['data']['subscription'] ) )->getObject() ) return '';
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Edit subscription' ), 'content' => $forms->edit_owner_subscription( $subscription ) ] );
        break;

        case 'add-country':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add country' ), 'content' => $forms->add_country() ] );
        break;

        case 'edit-country':
            if( !isset( $_POST['data']['country'] ) || !( $country = new \query\countries( $_POST['data']['country'] ) )->getObject() ) return '';
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Edit country' ), 'content' => $forms->edit_country( $country ) ] );
        break;

        case 'subscription-actions':
            if( !isset( $_POST['data']['type'] ) ) return '';
            $forms = new \user\website_forms;
            switch( $_POST['data']['type'] ) {
                case 'will_expire':
                    return cms_json_encode( [ 'title' => t( 'Options' ), 'content' => $forms->notif_subscribers() ] );
                break;

                case 'expired':
                    return cms_json_encode( [ 'title' => t( 'Options' ), 'content' => $forms->notif_subscribers( 2 ) ] );
                break;
            }
            return '';
        break;

        case 'remove-subscriptions':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Remove expired subscriptions' ), 'content' => $forms->remove_expired_subscriptions() ] );
        break;

        case 'autorenew-subscriptions':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Auto-renew subscriptions' ), 'content' => $forms->autorenew_subscriptions() ] );
        break;
    }
});

ajax()->add_call( 'website-actions2', function() {
    if( !me()->isOwner() || !isset( $_POST['action'] ) ) {
        return ;
    }

    switch( $_POST['action'] ) {
        case 'activate-theme':
            $themes = clone themes();
            
            if( !isset( $_POST['theme'] ) || !themes()->getTheme( $_POST['theme'] ) || !themes()->activate() )
            return ;     

            $themes->deactivate();

            return cms_json_encode( [ 'callback' => '{
                "callback": "markup_activate_theme"
            }' ] );
        break;

        case 'activate-plugin':
            if( !isset( $_POST['plugin'] ) || !plugins()->getPlugin( $_POST['plugin'] ) || !plugins()->activate() )
            return ;

            return cms_json_encode( [ 'callback' => '{
                "callback": "markup_switch_markup",
                "new_markup": "' . base64_encode( '<a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'deactivate-plugin', 'plugin' => plugins()->getId() ] ) ) . '\'>' . t( 'Deactivate' ) . '</a>' ) . '"
            }' ] );
        break;

        case 'deactivate-plugin':
            if( !isset( $_POST['plugin'] ) || !plugins()->getPlugin( $_POST['plugin'] ) || !plugins()->deactivate() )
            return ;  

            return cms_json_encode( [ 'callback' => '{
                "callback": "markup_switch_markup",
                "new_markup": "' . base64_encode( '<a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'activate-plugin', 'plugin' => plugins()->getId() ] ) ) . '\'>' . t( 'Activate' ) . '</a>' ) . '"
            }' ] );
        break;

        case 'delete-category':
            if( !isset( $_POST['category'] ) || !( $categories = categories( (int) $_POST['category'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_category( $categories );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-page':
            if( !isset( $_POST['page'] ) || !( $pages = pages( (int) $_POST['page'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_page( $pages );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-voucher':
            if( !isset( $_POST['voucher'] ) || !( $vouchers = vouchers( (int) $_POST['voucher'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_voucher( $vouchers );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-country':
            if( !isset( $_POST['country'] ) || !( $country = new \query\countries( (int) $_POST['country'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_country( $country );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'hide-plan':
            if( !isset( $_POST['plan'] ) || !( $plans = new \query\plans\plans( (int) $_POST['plan'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->hide_plan( $plans );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'unhide-plan':
            if( !isset( $_POST['plan'] ) || !( $plans = new \query\plans\plans( (int) $_POST['plan'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->unhide_plan( $plans );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-plan':
            if( !isset( $_POST['plan'] ) || !( $plans = new \query\plans\plans( (int) $_POST['plan'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_plan( $plans );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-plan-offer':
            if( !isset( $_POST['offer'] ) || !( $offers = new \query\plans\offers( (int) $_POST['offer'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_plan_offer( $offers );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'delete-alert':
            if( !isset( $_POST['alert'] ) || !( $alerts = alerts( (int) $_POST['alert'] ) )->getObject() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );     
            }

            try { 
                me()->actions()->delete_alert( $alerts );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;
    }
});

ajax()->add_call( 'website-form-actions', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) ) {
        return ;
    }

    switch( $_GET['action2'] ) {
        case 'add-category':
            try {
                me()->form_actions()->add_category( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Category added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-category':
            if( !isset( $_GET['category'] ) ) {
                return ;
            }
        
            $categories = new \query\categories( (int) $_GET['category'] );
            if( !$categories->getObject() ) {
                return ;
            }
            
            try {
                me()->form_actions()->edit_category( $categories, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Category edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-page':
            try {
                me()->form_actions()->add_page( ( $_POST['data'] ?? [] ), ( $_GET['type'] ?? NULL ), ( $_POST['page'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Page added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-page':
            if( !isset( $_GET['page'] ) )
            return ;
        
            $pages = new \query\pages( (int) $_GET['page'] );
            if( !$pages->getObject() )
            return ;

            try {
                me()->form_actions()->edit_page( $pages, ( $_POST['data'] ?? [] ), ( $_POST['page'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Page edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-voucher':
            if( !me()->isOwner() ) return ;
            try {
                me()->form_actions()->add_voucher( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Voucher added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-voucher':
            if( !isset( $_GET['voucher'] ) || !me()->isOwner() )
            return ;
        
            $vouchers = new \query\vouchers( (int) $_GET['voucher'] );
            if( !$vouchers->getObject() ) {
                return ;
            }
            
            try {
                $id = me()->form_actions()->edit_voucher( $vouchers, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Voucher edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'install-theme':
            if( !me()->isOwner() ) return ;
            try {
                themes()->install();
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'themes' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'install-plugin':
            if( !me()->isOwner() ) return ;
            try {
                plugins()->install();
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'plugins' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete-theme':
            if( !isset( $_POST['data']['theme'] ) || !me()->isOwner() || !themes()->getTheme( $_POST['data']['theme'] ) )
            return ;

            try {
                themes()->delete();
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'themes' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete-plugin':
            if( !isset( $_POST['data']['plugin'] ) || !me()->isOwner() || !plugins()->getPlugin( $_POST['data']['plugin'] ) )
            return ;

            try {
                plugins()->delete();
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'plugins' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'theme-options':
            if( !isset( $_POST['data']['label'] ) ) return;

            $label      = $_POST['data']['label'];
            $options    = themes()->getOptions( $label );

            if( $options === NULL ) return ;

            $c_options  = get_theme_options();
            $form       = new \markup\front_end\form_fields( $options );
            $form       ->setValues( $c_options );
            $form       ->build();

            try {
                $media  = $form->uploadFiles( $_POST['data'] );
                me()->website_options()->saveThemeOptions( array_intersect_key( $form->getValuesArray(), $options ) );

                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-subscription':
            try {
                me()->form_actions()->add_owner_subscription( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Subscription added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-subscription':
            if( !isset( $_GET['subscription'] ) )
            return ;

            $subscription = new \query\subscriptions( (int) $_GET['subscription'] );
            if( !$subscription->getObject() )
            return ;

            try {
                $id = me()->form_actions()->edit_owner_subscription( $subscription, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Subscription edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-country':
            try {
                me()->form_actions()->add_country( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Country added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-country':
            if( !isset( $_GET['country'] ) )
            return ;

            $country = new \query\countries( (int) $_GET['country'] );
            if( !$country->getObject() )
            return ;

            try {
                $id = me()->form_actions()->edit_country( $country, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Country edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'save-menu':
            try {
                $id = me()->form_actions()->save_menu( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});


ajax()->add_call( 'user-actions', function() {
    if( !isset( $_POST['data']['action'] ) ) {
        return ;
    }

    switch( $_POST['data']['action'] ) {
        case 'withdraw-info':
            $transactions = new \query\transactions;
            if( !( isset( $_POST['data']['withdraw'] ) && $transactions->setId( $_POST['data']['withdraw'] )->getObject() && ( $transactions->getUserId() == me()->getId() || me()->isAdmin() ) ) ) return ;

            $info = $transactions->getDetailsJD();

            if( empty( $info ) ) {
                $content = '<div class="msg alert mb0">' . t( 'No information' ) . '</div>';
            } else {

                $content = '
                <div class="form_lines mb0">';

                foreach( $info as $key => $value ) {
                    $content .= '
                    <div class="form_line">
                        <label>' . ucfirst( esc_html( $key ) ) . '</label>
                        <div>' . ( is_array( $value ) || is_object( $value ) ? esc_html( cms_json_encode( $value ) ) : ( !empty( $value ) ? esc_html( $value ) : '-' ) ) . '</div>
                    </div>';
                }

                $content .= '
                </div>';
            }

            return cms_json_encode( [ 'title' => t( 'Transaction info' ), 'content' => $content ] );
        break;
        

        case 'add-user':
            $forms = new \user\website_forms;
            return cms_json_encode( [ 'title' => t( 'Add user' ), 'content' => $forms->add_user() ] );
        break;
    }
});

ajax()->add_call( 'user-actions2', function() {
    if( !isset( $_POST['action'] ) ) {
        return ;
    }

    switch( $_POST['action'] ) {
        case 'approve-withdraw':
            if( !isset( $_POST['withdraw'] ) || !( $transaction = transactions( (int) $_POST['withdraw'] ) )->getObject() )
            return ;

            try { 
                me()->actions()->approve_withdraw( $transaction );
            }

            catch( \Exception $e ) {
                return ;
            }

            return cms_json_encode( [ 'callback' => '{
                "callback": "markup_delete_this"
            }' ] );
        break;

        case 'cancel-withdraw':
            if( !isset( $_POST['withdraw'] ) || !( $transaction = transactions( (int) $_POST['withdraw'] ) )->getObject() )
            return ;

            try { 
                me()->actions()->cancel_withdraw( $transaction );
            }

            catch( \Exception $e ) {
                return ;  
            }

            return cms_json_encode( [ 'callback' => '{
                "callback": "markup_delete_this"
            }' ] );
        break;
    }
});


/** MANAGE SURVEY */
ajax()->add_call( 'manage-survey', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['survey'] ) ) {
        return ;
    }

    if( !me()->manageSurvey( 'view', (int) $_POST['options']['survey'] ) )
    return ;

    $surveys    = me()->getSelectedSurvey();
    $isOwner    = me()->manageSurvey( 'delete-survey' );

    switch( $_POST['options']['action'] ) {
        case 'edit':
            // Permisions
            if( !me()->manageSurvey( 'edit-survey' ) ) return ;

            $canEditSettings = $isOwner || me()->manageSurvey( 'edit-settings' );

            $title = '';

            if( $canEditSettings ) {
                $title = '
                <ul class="btnset mla">';
                    if( $isOwner || me()->manageSurvey( 'edit-settings' ) )
                    $title .= '<li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'settings', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Settings' ) . '</a></li>';
                    $title .= '
                    <li>
                        <a href="#"><i class="fas fa-chevron-down aic"></i></a>
                        <ul class="btnset top">';
                            if( $isOwner || me()->isOwner() )
                            $title .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'advanced', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Advanced' ) . '</a></li>';
                            if( $canEditSettings ) {
                            $title .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'terms-of-use', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Terms of use' ) . '</a></li>
                            <li class="label lab_switch">
                                <div class="labelt">' . t( 'Personalize' ) . '</div>
                            </li>
                            <li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'logo', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Logo' ) . '</a></li>
                            <li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'meta-tags', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Meta tags' ) . '</a></li>';
                            }
                        $title .= '
                        </ul>
                    </li>
                </ul>';
            }

            return cms_json_encode( [ 'title' => t( 'Edit survey' ), 'title2' => $title, 'content' => me()->forms()->edit_survey( $surveys ) ] );
        break;
        
        case 'budget':
            // Permisions
            if( !( $isOwner || me()->isOwner() ) )
            return cms_json_encode( [ 'title' => t( 'Budget' ), 'content' => '<div class="msg alert mb0">' . t( 'This option is available for survey owner only' ) . '</div>' ] );

            $content = me()->forms()->update_survey_budget( $surveys );
            return cms_json_encode( [ 'title' => t( 'Budget' ), 'content' => $content ] );
        break;

        case 'questions':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            $c1         = $surveys->getQuestions()->setVisible( 2 )->count();
            $content    = '<div class="form_lines">';
            $steps      = $surveys->getSteps();

            foreach( $steps->fetch( -1 ) as $sid => $step ) {
                $steps->setObject( $step );
                $content .= '
                <div class="form_line form_dropdown s">
                    <div>
                        <span class="c2">' . t( 'Step' ) . ':</span> ' . esc_html( $steps->getName() ) . '<i class="fas fa-angle-down"></i>
                    </div>
                    <div>
                        <ul class="df mr">
                            <li><a href="#" class="btn" data-popup="manage-step" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit', 'step' => $steps->getId() ] ) ) . '\'>' . t( 'Settings' ) . '</a></li>
                            <li class="bgr"><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId(), 'step' => $steps->getId(), 'pos' => 'first' ] ) ) . '\'>' . t( 'Add question' ) . ' <i class="fas fa-arrow-up"></i></a></li>
                            <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId(), 'step' => $steps->getId(), 'pos' => 'last' ] ) ) . '\'><i class="fas fa-arrow-down"></i></a></li>';
                            if( !$steps->isMain() )
                            $content .= '
                            <li class="mla"><a href="#" class="btn" data-popup="manage-step" data-options=\'' . ( cms_json_encode( [ 'action' => 'delete', 'step' => $steps->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>';
                        $content .= '
                        </ul>
                    </div>';

                $questions  = $steps->getQuestions();
                $questions  ->setVisible( 2 );

                if( $questions->count() ) {
                    $content .= '<div>';
                    foreach( $questions->fetch( -1 ) as $q ) {
                        $questions->setObject( $q );
                        $content .= '
                        <div class="form_line form_dropdown">
                            <div>
                                <span class="c2">' . survey_types( $questions->getType() )->getName() . ':</span> ' . esc_html( $questions->getTitle() ) . '<i class="fas fa-angle-down"></i>
                            </div>
                            <div>
                                <ul class="df mr">
                                    <li><a href="#" class="btn" data-popup="manage-question" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit', 'question' => $questions->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                                    <li class="bgr"><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId(), 'question' => $questions->getId(), 'step' => $steps->getId(), 'pos' => 'before' ] ) ) . '\'>' . t( 'Add question' ) . ' <i class="fas fa-arrow-up"></i></a></li>
                                    <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId(), 'question' => $questions->getId(), 'step' => $steps->getId(), 'pos' => 'after' ] ) ) . '\'><i class="fas fa-arrow-down"></i></a></li>
                                    <li class="mla"><a href="#" class="btn" data-ajax="manage-question3" data-data=\'' . ( cms_json_encode( [ 'action' => 'add-trash', 'question' => $questions->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                                </ul>
                            </div>
                        </div>';
                    }
                    $content .= '</div>';
                }
    
                $content .= '
                </div>';
            }

            $content .= '<ul class="df mr mt20">';
            if( !$c1 )
            $content .= '
            <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'templates', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Use a template' ) . '</a></li>';

            $content .= '
            <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-step', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'New step' ) . '</a></li>
            </ul>';

            $content .= '
            <div class="form_line mt40">
                <h4>' . t( 'More settings' ) . '</h2>
            </div>';

            $content .= '
            <div class="form_line form_dropdown">
                <div>
                    <span class="c2"></span>
                    ' . t( 'Before finish' ) . '
                    <i class="fas fa-angle-down"></i>
                </div>
                <div>
                    <a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'actions-before', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Settings' ) . '</a>
                </div>
            </div>';

            foreach( getSurveyPages() as $k => $page ) {
                $content .= '
                <div class="form_line form_dropdown">
                    <div>
                        <span class="c2">' . t( 'Page' ) . ':</span> ' . $page['title'] . '<i class="fas fa-angle-down"></i>
                    </div>
                    <div>
                        <a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit-page', 'survey' => $surveys->getId(), 'page' => $k ] ) ) . '\'>' . t( 'Custom' ) . '</a>
                    </div>
                </div>';
            }

            $content .= '
            <div class="form_line form_dropdown">
                <div>
                    <span class="c2"></span>
                    ' . t( 'After finish' ) . '
                    <i class="fas fa-angle-down"></i>
                </div>
                <div>
                    <a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'actions-after', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Settings' ) . '</a>
                </div>
            </div>';

            $content .= '</div>';

            $title = '
            <ul class="btnset mla">
                <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Add question' ) . '</a></li>
                <li>
                    <a href="#"><i class="fas fa-chevron-down aic"></i></a>
                    <ul class="btnset top">
                        <li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'trash', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Trash' ) . '</a></li>
                    </ul>
                </li>
            </ul>';

            return cms_json_encode( [ 
                'title'         => t( 'Questions' ), 
                'title2'        => $title, 
                'content'       => $content, 
                'classes'       => [ 's2' ], 
                'remove_prev'   => true ] );
        break;
        
        case 'trash':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            $content = '
            <div class="form_lines">
            <div class="form_line form_dropdown s">
            <div>
                <span>' . t( 'Deleted questions' ) . '</span><i class="fas fa-angle-down"></i>
            </div>';

            $questions  = questions();
            $questions  ->setSurveyId( $surveys->getId() )
                        ->setVisible( 0 );

            if( $questions->count() ) {
                $content .= '<div>';
                foreach( $questions->fetch( -1 ) as $q ) {
                    $questions->setObject( $q );
                    $content .= '
                    <div class="form_line form_dropdown">
                        <div>
                            <span class="c2">' . survey_types( $questions->getType() )->getName() . ':</span> ' . esc_html( $questions->getTitle() ) . '<i class="fas fa-angle-down"></i>
                        </div>
                        <div>
                            <ul class="df mr">
                                <li><a href="#" class="btn" data-popup="manage-question" data-options=\'' . ( cms_json_encode( [ 'action' => 'restore', 'question' => $questions->getId() ] ) ) . '\'>' . t( 'Restore' ) . '</a></li>
                                <li class="mla"><a href="#" class="btn" data-popup="manage-question" data-options=\'' . ( cms_json_encode( [ 'action' => 'delete-permanently', 'question' => $questions->getId() ] ) ) . '\'>' . t( 'Delete permanently' ) . '</a></li>
                            </ul>
                        </div>
                    </div>';
                }
                $content .= '</div>';
            } else {
                $content .= '
                <div>
                    <div class="msg info mb0">' . t( 'There are no deleted questions' ) . '</div>
                </div>';
            }

            $content .= '
            </div>
            </div>';

            return cms_json_encode( [ 
                'title'         => t( 'Trash' ), 
                'content'       => $content, 
                'classes'       => [ 's2' ], 
                'remove_prev'   => true ] );
        break;

        case 'add-step':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Add step' ), 'content' => me()->forms()->add_step( $surveys ) ] );
        break;

        case 'add-question':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Add question' ), 'content' => me()->forms()->add_question( $surveys ) ] );
        break;

        case 'collectors':
            // Permisions
            if( !me()->manageSurvey( 'manage-collector' ) ) return ;

            $content    = '';
            $collectors = $surveys->getCollectors();

            if( !$collectors->count() ) {
                $content .= '<div class="msg alert mb0">' . t( 'There are no collectors for this survey') . '</div>';
            } else {
                $content .= '<div class="form_lines">';
                foreach( $collectors->fetch( -1 ) as $collector ) {
                    $collectors->setObject( $collector );
                    $content .= '
                    <div class="form_line form_dropdown">
                        <div>
                            <span class="c2">Link:</span> ' . esc_html( $collectors->getName() ) . '<i class="fas fa-angle-down"></i>
                        </div>
                        <div>
                            <ul class="df">
                                <li><a href="#" class="btn" data-popup="manage-collector" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit', 'collector' => $collectors->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                                <li class="mla"><a href="#" class="btn" data-ajax="manage-collector3" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete', 'collector' => $collectors->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                            </ul>
                        </div>
                    </div>';
                }
                $content .= '</div>';
            }

            $title = '
            <ul class="btnset mla">
                <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-collector', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'New collector' ) . '</a></li>
            </ul>';

            return cms_json_encode( [
                'title'         => t( 'Collectors' ), 
                'title2'        => $title, 
                'content'       => $content, 
                'remove_prev'   => true ] );
        break;

        case 'add-collector':
            // Permisions
            if( !me()->manageSurvey( 'manage-collector' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Add collector' ), 'content' => me()->forms()->add_collector( $surveys ) ] );
        break;

        case 'collaborators':
            // Permisions
            $myteam = me()->myTeam();

            if( !$myteam )
            return cms_json_encode( [ 'title' => t( 'Join or create a team' ), 'content' => '<div class="msg info mb0">' . t( 'You must be part of a team to share this survey' ) . '</div>' ] );
        
            if( me()->getTeamMemberPermissions() < 2 ) return ;

            $users      = $surveys->getUsers_Users();
            $content    = '
            <div class="table t2 ns mb0">';
            foreach( $users->fetch( -1 ) as $userList ) {
                $add_date   = custom_time( $userList->us_date );
                $user       = $users->getUserObject( $userList );
                $content    .= '
                <div class="td">
                    <div class="sav wa sav4">' . $user->getAvatarMarkup() . '</div>
                    <div>' . esc_html( $user->getDisplayName() ) . '</div>
                    <div class="tar"><span title="' . $add_date[0] . '">' . $add_date[1] . '</span></div>
                </div>';
            }

            if( $isOwner ) {
                $content    .= '
                <div class="td">
                    <div class="wa"><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'collaborators2', 'survey' => $surveys->getId() ] ) ) . '\' class="btn">' . t( 'Manage collaborators' ) . '</a></div>';
                $content .= '
                </div>';
            }

            $content .= '
            </div>';

            return cms_json_encode( [ 'title' => t( 'Collaborators' ), 'content' => $content, 'remove_prev_all' => true ] );
        break;

        case 'collaborators2':
            // Permisions
            $team = me()->myTeam();
            if( me()->getTeamMemberPermissions() < 2 ) return ;

            $members    = $team->members();
            $users      = $surveys->getUsers_Users()
                        ->fetch( -1 );
            $content    = '
            <div class="table t2 ns mb0">';
            foreach( $members->fetch( -1 ) as $user ) {
                $user       = $members->getUserObject( $user );
                $content    .= '
                <div class="td">
                    <div class="sav wa sav4">' . $user->getAvatarMarkup() . '</div>
                    <div>' . esc_html( $user->getDisplayName() ) . '</div>';
                    $content .= '<h3 class="wa mb0">';
                    if( $user->getId() === me()->getId() ) {
                    } else if( isset( $users[$user->getID()] ) )
                    $content .= '<a href="#" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'manage-collaborator', 'survey' => $surveys->getId(), 'user' => $user->getId(), 'type' => 'remove' ] ) ) . '\' class="cl9"><i class="fas fa-check"></i></a>';
                    else
                    $content .= '<a href="#" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'manage-collaborator', 'survey' => $surveys->getId(), 'user' => $user->getId(), 'type' => 'add' ] ) ) . '\'><i class="fas fa-check"></i></a>';
                    $content .= '
                    </h2>
                </div>';
            }

            $content .= '
            </div>';

            return cms_json_encode( [ 'title' => t( 'Manage collaborators' ), 'content' => $content, 'remove_prev_all' => true ] );
        break;

        case 'settings':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Settings' ), 'content' => me()->forms()->settings_survey( $surveys ) ] );
        break;

        case 'logo':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Logo' ), 'content' => me()->forms()->edit_logo_survey( $surveys ) ] );
        break;

        case 'meta-tags':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Meta tags' ), 'content' => me()->forms()->meta_tags_survey( $surveys ) ] );
        break;

        case 'texts':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Texts & messages' ), 'content' => me()->forms()->texts_survey( $surveys ) ] );
        break;

        case 'terms-of-use':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Terms of use' ), 'content' => me()->forms()->terms_of_use_survey( $surveys ) ] );
        break;

        case 'advanced':
            // Permisions
            if( !( $isOwner || me()->isOwner() ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Advanced' ), 'content' => me()->forms()->advanced_settings_survey( $surveys ) ] );
        break;

        case 'edit-page':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            if( !isset( $_POST['options']['page'] ) ) return ;
            if( !( $page = getSurveyPage( $surveys->getId(), $_POST['options']['page'] ) ) ) return ;
            return cms_json_encode( [ 'title' => sprintf( t( '[page] %s' ), esc_html( $page['title'] ) ), 'content' => me()->forms()->edit_survey_page( $surveys, $_POST['options']['page'], $page ) ] );
        break;
        
        case 'actions-before':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Actions before finish' ), 'content' => me()->forms()->survey_before_actions( $surveys ) ] );
        break;

        case 'actions-after':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Actions after finish' ), 'content' => me()->forms()->survey_after_actions( $surveys ) ] );
        break;

        case 'results-filter':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            parse_str( $_POST['data'], $values );
            $content    = me()->forms()->advanced_responses( $surveys, $values );
            return cms_json_encode( [ 'title' => t( 'Advanced' ), 'content' => $content ] );
        break;

        case 'select-questions':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $content    = me()->forms()->survey_filter_questions( $surveys, ( $_POST['data'] ?? [] ) );
            return cms_json_encode( [ 'title' => t( 'Questions' ), 'content' => $content ] );
        break;

        case 'add-label':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Add label' ), 'content' => me()->forms()->add_survey_label( $surveys ) ] );
        break;

        case 'labels':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $content    = '';
            $labels     = $surveys->getLabels();

            if( !$labels->count() ) {
                $content .= '<div class="msg alert mb0">' . t( 'There are no labels for this survey') . '</div>';
            } else {
                $content .= '<div class="form_lines">';
                foreach( $labels->fetch( -1 ) as $label ) {
                    $labels->setObject( $label );
                    $content .= '
                    <div class="form_line form_dropdown">
                        <div>
                            <span>' . esc_html( $labels->getName() ) . '</span><i class="fas fa-angle-down"></i>
                        </div>
                        <div>
                            <ul class="df">
                                <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit-label', 'survey' => $surveys->getId(), 'label' => $labels->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                                <li class="mla"><a href="#" class="btn" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-label', 'survey' => $surveys->getId(), 'label' => $labels->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                            </ul>
                        </div>
                    </div>';
                }
                $content .= '</div>';
            }

            $title = '
            <ul class="btnset mla">
                <li><a href="#" class="btn" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-label', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'New label' ) . '</a></li>
            </ul>';

            return cms_json_encode( [
                'title'         => t( 'Labels' ), 
                'title2'        => $title, 
                'content'       => $content, 
                'remove_prev'   => true ] );
        break;

        case 'edit-label':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['label'] ) ) return ;
            $labels = $surveys->getLabels()
                    ->setId( (int) $_POST['options']['label'] );
            if( !$labels->getObject() || $surveys->getId() !== $labels->getSurveyId() ) return;
            return cms_json_encode( [ 'title' => t( 'Edit label' ), 'content' => me()->forms()->edit_survey_label( $labels ) ] );
        break;

        case 'add-response-comment':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['response'] ) ) return ;
            $responses  = $surveys->getResults()
                        ->setId( (int) $_POST['options']['response'] );
            if( !$responses->getObject() || $surveys->getId() !== $responses->getSurveyId() ) return;
            return cms_json_encode( [ 'title' => t( 'Response comment' ), 'content' => me()->forms()->edit_response_comment( $surveys, $responses ) ] );
        break;

        case 'edit-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['report'] ) ) return ;
            $reports    = $surveys->reports( (int) $_POST['options']['report'] );
            if( !$reports->getObject() ) return;
            return cms_json_encode( [ 'title' => t( 'Edit report' ), 'content' => me()->forms()->edit_report( $surveys, $reports ) ] );
        break;

        case 'delete-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['report'] ) ) return ;
            $reports    = $surveys->reports( (int) $_POST['options']['report'] );
            if( !$reports->getObject() ) return;
            $location   = $_POST['options']['loc'] ?? '';
            return cms_json_encode( [ 'title' => t( 'Delete report' ), 'content' => me()->forms()->delete_report( $surveys, $reports, $location ) ] );
        break;

        case 'share-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['report'] ) ) return ;
            $reports    = $surveys->reports( (int) $_POST['options']['report'] );
            if( !$reports->getObject() ) return;
            return cms_json_encode( [ 'title' => t( 'Share report' ), 'content' => me()->forms()->share_report( $surveys, $reports ) ] );
        break;

        case 'export-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['options']['report'] ) ) return ;
            $reports    = $surveys->reports( (int) $_POST['options']['report'] );
            if( !$reports->getObject() ) return;
            return cms_json_encode( [ 'title' => t( 'Export report' ), 'content' => me()->forms()->export_report( $surveys, $reports ) ] );
        break;

        case 'add-response':
            // Permisions
            if( !me()->manageSurvey( 'add-response' ) ) return ;

            return cms_json_encode( [ 'title' => t( 'Add response' ), 'content' => me()->forms()->add_response( $surveys ) ] );
        break;

        case 'customize':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $questions  = $surveys
                        ->getQuestions()
                        ->setVisible( 2 )
                        ->setFilter( 'report' )
                        ->fetch( -1 );

            $dashboard  = $surveys
                        ->dashboard()
                        ->fetch( -1 );
            
            $qsmap      = [];

            $form       = new \markup\front_end\form_fields( [
                0 => [ 'type' => 'checkboxes', 'options' => [ 1 => t( 'Statistics' ) ] ],
                1 => [ 'type' => 'checkboxes', 'label' => t( 'Questions' ), 'options' => array_map( function( $q ) {
                    return esc_html( $q->title );
                }, $questions ) ],
                [ 'type' => 'button', 'label' => t( 'Save' ) ]
            ] );

            $values     = [];

            if( !isset( $values[0] ) )
            $values[0]= [];

            array_map( function( $v ) use ( &$values ) {
                $values[$v->type][$v->type_id] = $v->type_id;
            }, $dashboard );

            $form       ->setValues( $values );
    
            $fields     = $form->build();
            $content    = '<form class="form" data-ajax="' . ajax()->get_call_url( 'manage-survey2', [ 'action2' => 'customize', 'survey' => $surveys->getId() ] ) . '"'. $form->formAttributes() . '>';
            $content    .= $fields;
            $content    .= '</form>';

            return cms_json_encode( [
                'title'         => t( 'Customize your dashboard' ), 
                'content'       => $content 
            ] );
        break;

        case 'templates':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            $content    = '<div class="form_lines">';
            $templates  = site()->templates->getTemplates( getUserLanguage( 'locale_e' ) );

            foreach( $templates as $t_id => $template ) {
                $content .= '
                <div class="form_line form_dropdown s">
                    <div>
                        <span>' . esc_html( $template['title'] ) . '</span> <i class="fas fa-angle-down"></i>
                    </div>
                    <div>
                        <a href="#" class="btn" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'import-template', 'survey' => $surveys->getId(), 'template' => esc_html( $t_id )] ) ) . '\'>' . t( 'Use this template' ) . '</a>
                    </div>';
                    if( isset( $template['description'] ) ) {
                        $content .= '
                        <div>
                            ' . esc_html( $template['description'] ) . '
                        </div>';
                    }
                    $content .= '
                </div>';
            }

            $content    .= '</div>';

            return cms_json_encode( [ 'title' => t( 'Templates' ), 'content' => $content ] );
        break;
    }
});

ajax()->add_call( 'manage-survey2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['survey'] ) )
    return ;

    if( !me()->manageSurvey( 'view', (int) $_GET['survey'] ) )
    return ;

    $surveys    = me()->getSelectedSurvey();
    $isOwner    = me()->manageSurvey( 'delete-survey' );

    switch( $_GET['action2'] ) {
        case 'edit':
            // Permisions
            if( !me()->manageSurvey( 'edit-survey' ) ) return ;

            try {
                $id = me()->form_actions()->edit_survey( $surveys, ( $_POST['data'] ?? [] ), $_FILES );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Survey edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'budget':
            // Permisions
            if( !$isOwner ) return ;

            try {
                $id = me()->form_actions()->update_survey_budget( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Budget updated' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-step':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            try {
                $id = me()->form_actions()->add_step( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'manage-step', 'options' => [ 'action' => 'edit', 'step' => $id ], 'remove_prev_all' => true ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-question':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            try {
                $id = me()->form_actions()->add_question( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{ "callback": "popup_on_close_from_form", "functions": ["popup_reload_prev"] }' ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-collector':
            // Permisions
            if( !me()->manageSurvey( 'manage-collector' ) ) return ;

            try {
                $id = me()->form_actions()->add_collector( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'manage-collector', 'options' => [ 'action' => 'edit', 'collector' => $id ], 'remove_prev' => true ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-page':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;

            try {
                $id = me()->form_actions()->edit_survey_page( $surveys, $_GET['page'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'settings':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;
            else if( $surveys->getStatus() < 0 )
            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'This survey is pending deletion' ) ] );

            $data       = $_POST['data'];
            $restart    = $surveys->meta()->save( 'restart', ( isset( $data['restart'] ) ?: 0 ), 0, true );
            $rtime      = (int) $data['rtime'] ?? RESPONSE_TIME_LIMIT;

            if( $rtime < 1 || $rtime > ( RESPONSE_TIME_LIMIT * 3 ) ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'Response time is invalid. Please set a reasonable time in minutes for a respondent to answer this survey' ) ] ); 
            }

            $surveys->meta()->save( 'rtime', $rtime, RESPONSE_TIME_LIMIT, true );

            return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
        break;

        case 'logo':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            try {
                $id = me()->form_actions()->edit_logo_survey( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'meta-tags':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            try {
                $id = me()->form_actions()->edit_meta_tags_survey( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'texts':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;

            try {
                $id = me()->form_actions()->texts_survey( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'terms-of-use':
            // Permisions
            if( !me()->manageSurvey( 'edit-settings' ) ) return ;
            else if( $surveys->getStatus() < 0 )
            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'This survey is pending deletion' ) ] ); 

            $data   = $_POST['data'];

            if( !empty( $data['content'] ) ) {
                array_pop( $data['content'] );

                $meta   = $surveys->meta();
                $sh     = new \site\shortcodes;
                $text   = $sh->toShortcodeFromArray( $data['content'] );

                if( $meta->save( 'tou', $text, true ) ) {
                    return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
                } else {
                    return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'Error' ) ] ); 
                }
            }

            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'Error' ) ] ); 
        break;

        case 'before-actions':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;
            else if( $surveys->getStatus() < 0 )
            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'This survey is pending deletion' ) ] ); 

            $surveys->meta()->save( 'minPts', ( isset( $_POST['data']['require'] ) && isset( $_POST['data']['points'] ) ? (int) $_POST['data']['points'] : 0 ), 0, true );
            return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
        break;

        case 'after-actions':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) ) return ;
            else if( $surveys->getStatus() < 0 )
            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'This survey is pending deletion' ) ] ); 

            $surveys->meta()->save( 'Webhook', ( !empty( $_POST['data']['use-wh'] ) && isset( $_POST['data']['URL'] ) && filter_var( $_POST['data']['URL'], FILTER_VALIDATE_URL ) ? $_POST['data']['URL'] : '' ) );
            return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Options saved' ) ] );
        break;

        case 'generate-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $data       = $_POST['data'];
            $report     = surveyResults();
            $report     ->setSurveyId( $surveys->getId() );
            $report     ->filtersFromArray( $data );
            $results    = $report->results();

            if( empty( $results ) )
            return cms_json_encode( [ 'status' => 'alert', 'msg' => t( 'No records could be generated' ) ] ); 

            // Store report
            if( !empty( $data['save'] ) )
            $reportId = $report->saveHistory( $data['name'] );

            // Temporary position
            $tempPos = $data['tpos'] ?? 1;

            // Validate temporary position
            $tempPos = $tempPos > 0 ? $tempPos : 1;

            // Temporary report
            if( !isset( $reportId ) )
            $reportId = $report->saveHistory( $data['name'], $tempPos, !empty( $data['newReport'] ) );
            
            return cms_json_encode( [ 'callback' => '{
                "callback": "init_survey_result",
                "report": "' . $reportId . '",
                "pos": "' . $tempPos . '"
            }' ] );
        break;

        case 'view-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $data       = $_POST['data'];
            $reportId   = (int) $data['report'];

            // Position
            $position = $data['position'] ?? 1;

            return cms_json_encode( [ 'href' => admin_url( 'survey/' . $surveys->getId() . '/report/' . $reportId ), 'callback' => '{
                "callback": "init_survey_result",
                "report": "' . $reportId . '",
                "pos": "' . $position . '"
            }' ] );
        break;

        case 'results-filter':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $data = $_POST['data'] ?? [];
            if( isset( $data['tracking_ids'] ) ) array_pop( $data['tracking_ids'] );
            if( isset( $data['variables'] ) ) array_pop( $data['variables'] );
            return cms_json_encode( [ 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{ "callback": "popup_on_close_from_form", "functions": ["popup_results_filter"], "data": "' . http_build_query( $data ) . '" }' ] ] );
        break;

        case 'select-questions':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            $squestions = $_POST['data']['q'] ?? [];
            $questions  = $surveys->getQuestions();
            $fquestions = $questions->fetch( -1 );
            $questions  ->markupView( 'filters' );
            $qlist      = [];
            foreach( $squestions as $q ) {
                if( !isset( $fquestions[$q] ) ) continue;
                $questions  ->setObject( $fquestions[$q] );
                $qlist[$q]  = $questions->markup();
            }
            return cms_json_encode( [ 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{ "callback": "popup_on_close_from_form", "functions": ["popup_questions_filter"], "data": "' . base64_encode( cms_json_encode( $qlist ) ) . '" }' ] ] );
        break;

        case 'add-label':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( $surveys->getLabels()->count() >= 10 )
            return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'You have reached the limit of 10 labels per survey' ) ] ); 
            try {
                $id = me()->form_actions()->add_label( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Added' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-label':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_GET['label'] ) ) return ;
            $labels = $surveys->getLabels()
                    ->setId( (int) $_GET['label'] );
            if( !$labels->getObject() || $surveys->getId() !== $labels->getSurveyId() ) return;

            try {
                $id = me()->form_actions()->edit_survey_label( $labels, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-response':
            // Permisions
            if( !me()->manageSurvey( 'add-response' ) ) return ;

            $data = $_POST['data'] ?? [];
            try {
                $ids        = (array) me()->form_actions()->add_response( $surveys, $data );
                $ids_count  = count( $ids );
                $response   = [ 'status' => 'success', 'msg' => sprintf( t( '%s new blank response(s) added' ), $ids_count ) ];

                if( isset( $data['action'] ) && $data['action'] == 'go' && $ids_count == 1 ) {
                    $response['redirect'] = site_url( 's/' . current( $ids ) );
                }

                return cms_json_encode( $response );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-response-comment':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_GET['response'] ) ) return ;
            $responses  = $surveys->getResults()
                        ->setId( (int) $_GET['response'] );
            if( !$responses->getObject() || $surveys->getId() !== $responses->getSurveyId() ) return;

            try {
                me()->form_actions()->edit_response_comment( $surveys, $responses, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_GET['report'] ) ) return ;
            $reports = $surveys->reports( (int) $_GET['report'] );
            if( !$reports->getObject() ) return;

            try {
                me()->form_actions()->edit_report( $reports, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_GET['report'] ) ) return ;
            $reports = $surveys->reports( (int) $_GET['report'] );
            if( !$reports->getObject() ) return;

            $data   = $_POST['data'] ?? [];
            $loc    = $data['location'] ?? '';
            try {
                me()->form_actions()->delete_report( $reports, $data );
                switch( $loc ) {
                    case 'dashboard':
                        return cms_json_encode( [ 'href' => admin_url( 'survey/' . $surveys->getId() ), 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{
                            "callback": "init_survey_new_report",
                            "report": "' . $reports->getId() . '"
                        }' ] ] );
                    break;

                    default:
                    return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Deleted' ) ] );
                }
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'share-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_GET['report'] ) ) return ;
            $reports = $surveys->reports( (int) $_GET['report'] );
            if( !$reports->getObject() ) return;

            try {
                me()->form_actions()->share_report( $reports, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Report sent' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'customize':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            try {
                me()->form_actions()->customize_survey_dashboard( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'clear-collectors':
            // Permisions
            if( !( $isOwner || me()->isOwner() ) ) return ;

            try {
                $id = me()->form_actions()->clear_collectors( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Responses deleted' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete-survey':
            // Permisions
            if( !( $isOwner || me()->isOwner() ) ) return ;

            try {
                $id = me()->form_actions()->delete_survey( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'surveys' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'transfer-survey':
            // Permisions
            if( !( $isOwner || me()->isOwner() ) ) return ;

            try {
                $id = me()->form_actions()->transfer_survey( $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "close_popup"
                }', 'goto' => [ 'path' => [ 'surveys' ] ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'manage-survey3', function() {
    if( !isset( $_POST['action'] ) || !isset( $_POST['survey'] ) )
    return ;

    if( !me()->manageSurvey( 'view', (int) $_POST['survey'] ) )
    return ;

    $surveys    = me()->getSelectedSurvey();
    $isOwner    = me()->manageSurvey( 'delete-survey' );

    switch( $_POST['action'] ) {
        case 'delete-label':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['label'] ) ) return ;
            $labels = $surveys->getLabels()
                    ->setId( (int) $_POST['label'] );
            if( !$labels->getObject() || $surveys->getId() !== $labels->getSurveyId() ) return;

            try {
                me()->form_actions()->delete_survey_label( $labels, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete-response-comment':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;

            if( !isset( $_POST['response'] ) ) return ;
            $responses  = $surveys->getResults()
                        ->setId( (int) $_POST['response'] );
            if( !$responses->getObject() || $surveys->getId() !== $responses->getSurveyId() ) return;

            try {
                me()->form_actions()->edit_response_comment( $surveys, $responses );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'save-report':
            // Permisions
            if( !me()->manageSurvey( 'view-result' ) ) return ;
                        
            if( !isset( $_POST['report'] ) ) return ;
            $reports = $surveys->reports( (int) $_POST['report'] );
            if( !$reports->getObject() ) return;

            try {
                me()->actions()->save_survey_report( $reports );

                $options = '
                <li><a href="' . admin_url( 'survey/' . $surveys->getId() . '/responses/report/' . $reports->getId() ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'responses', 'id' => $surveys->getId(), 'report' => $reports->getId() ] ) . '\'>' . t( 'View responses' ) . '</a></li>
                <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'export-report', 'survey' => $surveys->getId(), 'report' => $reports->getId() ] ) . '\'>' . t( 'Export' ) . '</a></li>
                <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'edit-report', 'survey' => $surveys->getId(), 'report' => $reports->getId() ] ) . '\'>' . t( 'Edit' ) . '</a></li>
                <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'share-report', 'survey' => $surveys->getId(), 'report' => $reports->getId() ] ) . '\'>' . t( 'Share' ) . '</a></li>
                <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'delete-report', 'survey' => $surveys->getId(), 'report' => $reports->getId(), 'loc' => 'dashboard' ] ) . '\'>' . t( 'Delete' ) . '</a></li>';

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_change_btnset",
                    "markup": "' . base64_encode( $options ) . '"
                }' ] );
            }

            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'import-template':
            // Permisions
            if( !me()->manageSurvey( 'manage-question' ) || !isset( $_POST['template'] ) ) return ;

            try {
                $import = $surveys->importFromTemplate( $_POST['template'] );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'manage-survey', 'options' => [ 'action' => 'questions', 'survey' => $surveys->getId() ], 'remove_prev_all' => true ] ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [
                    'content' => showMessage( $e->getMessage(), '', '<i class="fas fa-times"></i>', 'error' ), 
                    'remove_prev' => true ] ] );
            }
        break;

        case 'manage-collaborator':
            // Permisions
            if( !$isOwner || !isset( $_POST['user'] ) || !isset( $_POST['type'] ) ) return ;
            $user   = (int) $_POST['user'];
            $r      = [];
            
            switch( $_POST['type'] ) {
                case 'add':
                    try {
                        me()->team_form_actions()->add_collaborator( $user, $surveys );

                        $r['callback'] = '{
                            "callback": "markup_changer",
                            "html": "' . base64_encode( '<a href="#" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'manage-collaborator', 'survey' => $surveys->getId(), 'user' => $user, 'type' => 'remove' ] ) ) . '\' class="cl9"><i class="fas fa-check"></i></a>' ) . '"
                        }';
                    }
                    
                    catch( \Exception $e ) {}
                break;

                case 'remove':
                    try {
                        me()->team_form_actions()->remove_collaborator( $user, $surveys );
            
                        $r['callback'] = '{
                            "callback": "markup_changer",
                            "html": "' . base64_encode( '<a href="#" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'manage-collaborator', 'survey' => $surveys->getId(), 'user' => $user, 'type' => 'add' ] ) ) . '\'><i class="fas fa-check"></i></a>' ) . '"
                        }';
                    }
                    
                    catch( \Exception $e ) {}
                break;
            }
        
            return cms_json_encode( $r );
        break;
    }
});

/** MANAGE COLLECTOR */
ajax()->add_call( 'manage-collector', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['collector'] ) ) return ;

    $collectors = new \query\collectors( (int) $_POST['options']['collector'] );
    if( !$collectors->getObject() )  return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-collector', $collectors->getSurveyId() ) ) return ;

    switch( $_POST['options']['action'] ) {
        case 'edit':
            return cms_json_encode( [ 'title' => t( 'Edit collector' ), 'content' => me()->forms()->edit_collector( $collectors ) ] );
        break;

        case 'crlink':
            return cms_json_encode( [ 'title' => t( 'Create an encrypted key' ), 'content' => me()->forms()->crlink_collector( $collectors ) ] );
        break;
    }
});

ajax()->add_call( 'manage-collector2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['collector'] ) ) return ;

    $collectors = new \query\collectors( (int) $_GET['collector'] );
    if( !$collectors->getObject() ) return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-collector', $collectors->getSurveyId() ) ) return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            try {
                $id = me()->form_actions()->edit_collector( $collectors, $collectors->getSurveyId(), ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Collector edited' ) ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'crlink':
            $data = $_POST['data'] ?? [];

            if( empty( $data['enckey'] ) || empty( $data['trackId'] ) ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => t( 'Error' ) ] ); 
            }

            return cms_json_encode( [ 'callback' => '{
                "callback": "crlink_collector",
                "link": "' . esc_html( $collectors->getPermalink() ) . '?key=' . md5( $data['enckey'] . $data['trackId'] ) . '&trackId=' . esc_html( $data['trackId'] ) . '"
            }' ] );
        break;

        case 'generate-key':
            return cms_json_encode( [ 'callback' => '{
                "callback": "genkey_collector",
                "key": "' . md5( uniqid() ) . '"
            }' ] );
        break;
    }
});

ajax()->add_call( 'manage-collector3', function() {
    if( !isset( $_POST['action'] ) ) return ;

    $collectors = new \query\collectors( (int) $_POST['collector'] );
    if( !$collectors->getObject() ) return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-collector', $collectors->getSurveyId() ) ) return ;

    switch( $_POST['action'] ) {
        case 'delete':
            if( $collectors->getResults()->count() ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'user-options', 'options' => [ 'action' => 'edit-profile' ] ] ] );    
            }

            try {
                $id = me()->form_actions()->delete_collector( $collectors, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_form_line"
                }' ] );
            }

            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

/** MANAGE STEP */
ajax()->add_call( 'manage-step', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['step'] ) ) return ;

    $steps = steps( (int) $_POST['options']['step'] );
    if( !$steps->getObject() )  return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-question', $steps->getSurveyId() ) ) return ;

    switch( $_POST['options']['action'] ) {
        case 'edit':
            return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $steps->getName() ) ), 'content' => me()->forms()->edit_step( $steps ) ] );
        break;

        case 'delete':
            return cms_json_encode( [ 'title' => sprintf( t( '[delete] %s' ), esc_html( $steps->getName() ) ), 'content' => me()->forms()->delete_step( $steps ) ] );
        break;
    }
});

ajax()->add_call( 'manage-step2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['step'] ) )  return ;

    $steps = steps( (int) $_GET['step'] );
    if( !$steps->getObject() ) return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-question', $steps->getSurveyId() ) ) return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            try {
                $question = me()->form_actions()->edit_step( $steps, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Step edited' ), 'callback' => '{ "callback": "popup_on_close_from_form", "functions": ["popup_reload_prev"] }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
        
        case 'delete':
            try {
                $question = me()->form_actions()->delete_step( $steps, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'manage-survey', 'options' => [ 'action' => 'questions', 'survey' => $steps->getSurveyId() ], 'remove_prev_all' => true ] ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

/** MANAGE QUESTION */
ajax()->add_call( 'manage-question', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['question'] ) ) return ;

    $questions  = questions( (int) $_POST['options']['question'] );
    if( !$questions->getObject() ) return ;

    // Get survey
    $surveys    = me()->selectSurvey( $questions->getSurveyId() );
    if( !$surveys ) return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-question' ) ) return ;

    switch( $_POST['options']['action'] ) {

        case 'edit':
            return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $questions->getTitle() ) ), 'content' => me()->forms()->edit_question( $questions ) ] );
        break;

        case 'restore':
            return cms_json_encode( [ 'title' => sprintf( t( '[restore] %s' ), esc_html( $questions->getTitle() ) ), 'content' => me()->forms()->restore_question( $questions ) ] );
        break;

        case 'delete-permanently':
            return cms_json_encode( [ 'title' => sprintf( t( '[delete] %s' ), esc_html( $questions->getTitle() ) ), 'content' => me()->forms()->delete_question( $questions ) ] );
        break;

    }
});

ajax()->add_call( 'manage-question2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['question'] ) ) return ;

    $questions = questions( (int) $_GET['question'] );
    if( !$questions->getObject() ) return ;

    // Get survey
    $surveys    = me()->selectSurvey( $questions->getSurveyId() );
    if( !$surveys ) return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-question', $surveys->getId() ) ) return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            try {
                $question = me()->form_actions()->edit_question( $questions, $surveys, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Question edited' ), 'callback' => '{
                    "callback": "popup_on_close_from_form",
                    "functions": [ "popup_reload_prev" ]
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'restore':
            try {
                me()->form_actions()->restore_question( $questions, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{ "callback": "popup_on_close_from_form", "functions": ["popup_reload_prev"] }' ] ] );
            }

            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'delete':
            try {
                me()->form_actions()->delete_question( $questions, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callbacks' => [ '{ "callback": "popup_close_from_form" }', '{ "callback": "popup_on_close_from_form", "functions": ["popup_reload_prev"] }' ] ] );
            }

            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'manage-question3', function() {
    if( !isset( $_POST['action'] ) || !isset( $_POST['question'] ) ) return ;

    $questions = questions( (int) $_POST['question'] );
    if( !$questions->getObject() )  return ;

    // Permisions
    if( !me()->manageSurvey( 'manage-question', $questions->getSurveyId() ) ) return ;

    switch( $_POST['action'] ) {
        case 'add-trash':
            try {
                me()->form_actions()->trash_question( $questions );
                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_closest_fline"
                }' ] );
            }

            catch( Exception $e ) { }
        break;
    }
});

/** MANAGE TEAM */
ajax()->add_call( 'manage-team', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['team'] ) )  return ;

    $teams = me()->myTeam( (int) $_POST['options']['team'] );
    if( !$teams ) return ;

    switch( $_POST['options']['action'] ) {
        case 'edit':
            if( !me()->manageTeam( 'edit-team', true ) ) return ;
            return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $teams->getName() ) ), 'content' => me()->team_forms()->edit_team( $teams ) ] );
        break;

        case 'invite':
            if( !me()->manageTeam( 'send-invitation' ) ) return ;
            return cms_json_encode( [ 'title' => t( 'Invite' ), 'content' => me()->team_forms()->invite( $teams ) ] );
        break;

        case 'members':
            $classes    = [ 's2' ];
            $content    = '
            <div class="table ns nb mb0">
            <div class="tr">
                <div class="wa">
                    <div class="w40"></div>
                </div>
                <div></div>
                <div>' . t( 'Last seen' ) . '</div>
                <div></div>
            </div>';

            $members    = $teams->members();
            $members    ->excludeUserId( me()->getId() )
                        ->setTeamId( $teams->getId() )
                        ->setApproved();

            $markup     = '';
            $cri        = me()->manageTeam( 'cancel-invitation' );

            foreach( $members->fetch( -1 ) as $member ) {
                $members    ->setObject( $member );
                $user       = $members->getUserObject();
                $last_seen  = custom_time( $user->getLastAction() );
                $btns       = [];

                if( $cri ) {
                    $btns[] = '<li><a href="#" data-ajax="manage-team3" data-data=\'' . cms_json_encode( [ 'action' => 'remove-member', 'team' => $teams->getId(), 'member' => $members->getUserId() ] ) . '\'><i class="fas fa-ban"></i></a></li>';
                }

                if( $teams->isOwner() ) {
                    $btns[] = '<li><a href="#" data-popup="manage-team" data-options=\'' . ( cms_json_encode( [ 'action' => 'member-permissions', 'team' => $teams->getId(), 'member' => $members->getUserId() ] ) ) . '\'><i class="fas fa-pencil-alt"></i></a></li>';
                }

                $markup .= '
                <div class="td">
                    <div class="sav wa sav4">' . $user->getAvatarMarkup() . '</div>
                    <div>' . esc_html( $user->getDisplayName() ) . '</div>
                    <div><span title="' . $last_seen[0] . '">' . $last_seen[1] . '</span></div>
                    <div class="df">';
                    if( !empty( $btns ) ) {
                        $markup .= '<ul class="btnset top mla">' . implode( "\n", $btns ) . '</ul>';
                    }
                $markup .= '
                    </div>
                </div>';
            }
            
            $content .= $markup;

            $content .= '
            </div>';

            if( $markup == '' ) {
                $content    = '<div class="msg info mb0">' . t( "Your team doesn't have any members yet" ) . '</div>';
                $classes    = [];
            }

            return cms_json_encode( [ 'title' => t( 'Members' ), 'content' => $content, 'classes' => $classes ] );
        break;

        case 'invitations':
            $members    = $teams->members();
            $members    ->setTeamId( $teams->getId() )
                        ->setApproved( 0 );

            if( $members->count() ) {

                $classes    = [ 's2' ];
                $content    = '
                <div class="table ns nb mb0">
                <div class="tr">
                    <div class="wa">
                        <div class="w40"></div>
                    </div>
                    <div></div>
                    <div>' . t( 'Invited by' ) . '</div>
                    <div class="wa">
                        <div class="w40"></div>
                    </div>
                    <div>' . t( 'Sent' ) . '</div>
                    <div></div>
                </div>';

                foreach( $members->fetch( -1 ) as $member ) {
                    $members    ->setObject( $member );
                    $user       = $members->getUserObject();
                    $sent_date  = custom_time( $members->getDate() );
                    $inviter    = $members->getInviter();
                    $inviter_av = $inviter_na = '';

                    if( $inviter->getObject() ) {
                        $inviter_av = $inviter->getAvatarMarkup();
                        $inviter_na = esc_html( $inviter->getDisplayName() );
                    }

                    $content    .= '
                    <div class="td">
                        <div class="sav wa sav4">' . $user->getAvatarMarkup() . '</div>
                        <div>' . esc_html( $user->getDisplayName() ) . '</div>
                        <div class="sav wa sav4">' . $inviter_av . '</div>
                        <div>' . $inviter_na . '</div>
                        <div><span title="' . $sent_date[0] . '">' . $sent_date[1] . '</span></div>
                        <div class="df">
                            <ul class="btnset top mla">
                                <li>
                                    <a href="#" data-ajax="manage-team3" data-data=\'' . cms_json_encode( [ 'action' => 'cancel-invitation', 'team' => $teams->getId(), 'member' => $user->getId() ] ) . '\'>
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>';
                }

                $content .= '
                </div>';

            } else {

                $content    = '<div class="msg mb0 info">' . t( 'No invitations sent and not approved' ) . '</div>';
                $classes    = [];

            }

            return cms_json_encode( [ 'title' => t( 'Invitations sent' ), 'content' => $content, 'classes' => $classes ] );
        break;

        case 'member-permissions':
            // Member ID is not set or current user is not the owner
            if( !isset( $_POST['options']['member'] ) || !$teams->isOwner() ) return '';
            
            $memberId = (int) $_POST['options']['member'];

            // This user does not exist
            if( !( $user = users( $memberId ) )->getObject() ) return ;

            return cms_json_encode( [ 'title' => sprintf( t( "Manage %s's permissions" ), esc_html( $user->getDisplayName() ) ), 'content' => me()->team_forms()->edit_member_permissions( $teams, $user ) ] );
        break;
    }
});

ajax()->add_call( 'manage-team2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['team'] ) ) return ;

    $teams = me()->myTeam( (int) $_GET['team'] );
    if( !$teams ) return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            if( !me()->manageTeam( 'edit-team' ) ) return ;

            try { 
                me()->team_form_actions()->edit_team( $teams, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Team edited' ) ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'invite':
            if( !me()->manageTeam( 'send-invitation' ) ) return ;

            try { 
                me()->team_form_actions()->invite( $teams, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Invitation sent' ) ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'member-permissions':
            if( empty( $_GET['member'] ) || !$teams->isOwner() || !( $user = users( (int) $_GET['member'] ) )->getObject() ) return ;

            try { 
                me()->team_form_actions()->edit_member_permissions( $teams, $user, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Permissions saved' ) ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'manage-team3', function() {
    if( !isset( $_POST['action'] ) || !isset( $_POST['team'] ) ) return ;

    $teams = me()->myTeam( (int) $_POST['team'] );
    if( !$teams ) return ;

    switch( $_POST['action'] ) {
        case 'cancel-invitation':
            if( !isset( $_POST['member'] ) || !me()->manageTeam( 'cancel-invitation' ) || !( $membership = $teams->userIsMember( (int) $_POST['member'] ) ) || $membership->approved !== 0 ) return ;

            try {
                me()->team_form_actions()->remove_member( $teams, (int) $_POST['member'], ( $_POST['data'] ?? [] ) );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;

        case 'remove-member':
            if( !isset( $_POST['member'] ) || !me()->getTeamMemberPermissions() || !( $membership = $teams->userIsMember( (int) $_POST['member'] ) ) || ( $membership->perm > 0 && !$teams->isOwner() ) ) return ;

            try {
                me()->team_form_actions()->remove_member( $teams, (int) $_POST['member'], ( $_POST['data'] ?? [] ) );

                if( ( $user = users( (int) $_POST['member'] ) )->getObject() && $user->getTeamId() == $teams->getId() ) {
                    $user->actions()->changeTeam( 0 );
                }

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }

            catch( \Exception $e ) {
                return cms_json_encode( [ 'show_popup' => [ 'action' => 'show-errors', 'options' => [ 'action' => 'unknown' ] ] ] );    
            }
        break;
    }
});

/** MANAGE RESULT */
ajax()->add_call( 'manage-result', function() {
    if( !isset( $_POST['options']['action'] ) || !isset( $_POST['options']['result'] ) ) 
    return ;

    $results = results( (int) $_POST['options']['result'] );
    if( !$results->getObject() )
    return ;
   
    if( !me()->manageSurvey( 'view-result', $results->getSurveyId() ) )
    return ;

    $surveys = me()->getSelectedSurvey();

    switch( $_POST['options']['action'] ) {
        case 'view':
            $the_res    = $results->getResults();
            $the_steps  = $the_res['st'] ?? [];
            $questions  = $surveys->getQuestions();
            $questions  ->markupView( 'results' );
            $date       = custom_time( $results->getDate() );

            $markup     = '<div class="result">';
            $markup     .= '
            <div class="form_line comment">
                <div>';
                if( $results->getComment() ) {
                    $markup .= '
                    <div>' . esc_html( $results->getComment() ) . '</div>
                    <div class="lnks lnks2">
                        <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-response-comment', 'survey' => $surveys->getId(), 'response' => $results->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a>
                        <a href="#" data-ajax="manage-survey3" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-response-comment', 'survey' => $surveys->getId(), 'response' => $results->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a>
                    </div>';
                } else {
                    $markup .= '<a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-response-comment', 'survey' => $surveys->getId(), 'response' => $results->getId() ] ) ) . '\'>' . t( 'Add a comment' ) . '</a>';
                }
                $markup .= '
                </div>
            </div>';

            $markup .= '<h2 class="green"><span>' . sprintf( t( 'Starts %s'), $date[0] ) . '</span></h2>';

            foreach( $the_steps as $id => $values ) {
                $steps  = steps( $id );
                if( !$steps->getObject() || $steps->getSurveyId() != $surveys->getId() )
                continue;
                $markup     .= '<h2 class="blue"><span>' . esc_html( $steps->getName() ) . '</span></h2>';
                $questions  ->setStepId( $steps->getId() );
                foreach( $questions->fetch( -1 ) as $question ) {
                    $questions  ->setObject( $question );
                    $markup     .= $questions->markup( ( $values['vl'][$questions->getId()] ?? [] ) );
                }
            }

            $trackIds   = $results->answerSelect( 'value_str' )->getAnswer( 8, $results->getId() );
            $vars       = $results->answerSelect( 'value_str' )->getAnswer( 9, $results->getId() );

            if( !empty( $trackIds ) ) {
                $markup .= '<h2 class="purple"><span>' . t( 'Tracking Id' ) . '</span></h2>';
                $markup .= '<div class="question">';
                if( isset( $trackIds->value_str ) )
                $trackIds = [ $trackIds ];
                foreach( $trackIds as $value ) {
                    $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                    $markup .= '<div>' . esc_html( current( $v ) ) . '</div>';   
                }
                $markup .= '</div>';
            }

            if( !empty( $vars ) ) {
                $markup .= '<h2 class="purple"><span>' . t( 'Variables' ) . '</span></h2>';
                $markup .= '<div class="question">';
                if( isset( $vars->value_str ) )
                $vars = [ $vars ];
                foreach( $vars as $value ) {
                    $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                    $markup .= '<h2><span>' . esc_html( key( $v ) ) . '</span></h2>';
                    $markup .= '<div>' . esc_html( current( $v ) ) . '</div>';
                }
                $markup .= '</div>';
            }

            $points = $results->answerSelect( 'value' )->getAnswer( 5, $results->getId() );
            $markup .= '<h2 class="purple"><span>' . t( 'Points' ) . '</span></h2>';
            $markup .= '<div class="question"><div>' . ( $points->value ?? 0 ) . '</div></div>';

            switch( $results->getStatus() ) {
                case 0:
                    $markup .= '<h2 class="red"><span>' . sprintf( t( 'Disqualified %s'), custom_time( $results->getDate(), 2 ) ) . '</span></h2>';
                break;

                case 1:
                    $markup .= '<h2 class="yellow"><span><i class="fas fa-circle-notch fa-spin"></i> ' . sprintf( t( 'In progress (%s)'), $date[1] ) . '</span></h2>';
                break;

                case 2:
                    $markup .= '<h2 class="green"><span>' . sprintf( t( 'Ends %s'), custom_time( $results->getFinishDate(), 2 ) ) . '</span></h2>';
                    $markup .= '<h2 class="yellow mb0"><span>' . t( 'Action required' ) . '</span></h2>';
                    $markup .= '
                    <div class="act df mt20">
                        <a href="#" class="btn">' . t( 'Approve' ) . '</a>
                        <a href="#" class="btn">' . t( 'Disqualify' ) . '</a>
                    </div>';
                break;

                case 3:
                    $markup .= '<h2 class="green"><span>' . sprintf( t( 'Ends %s'), custom_time( $results->getFinishDate(), 2 ) ) . '</span></h2>';
                break;
            }

            $markup .= '</div>';

            return cms_json_encode( [ 'title' => t( 'View result' ), 'content' => $markup, 'classes' => [ 's2' ] ] );
        break;

        case 'labels':
            $labels = surveys( $results->getSurveyId() )->getLabels()->fetch( -1 );
            $cLabels= $results->getLabels()->select( [ 'l.id' ] )->fetch( -1 );

            $markup = '<ul class="slb">';

            foreach( $labels as $label ) {
                $markup .= '
                <li>
                    <label class="sav">
                        <input type="checkbox" name="data[label][' . $label->id . ']" data-color="' . esc_html( $label->color ) . '" id="' . $label->id . '"' . ( isset( $cLabels[$label->id] ) ? ' checked' : '' ) . ' value="' . $label->id . '" />
                        <i class="avt-' . esc_html( $label->color ) . '"></i>
                    </label>
                    <label for="' . $label->id . '">
                        ' . esc_html( $label->name ) . '
                    </label>
                </li>';
            }

            $markup .= '</ul>';

            return cms_json_encode( [ 'title' => t( 'Labels' ), 'content' => $markup, 'callbacks' => [ '{ "callback": "result_change_labels", "data": { "result": "' . $results->getId() . '" } }' ], 'classes' => [ 'auto' ] ] );
        break;

        case 'export':
            return cms_json_encode( [ 'title' => t( 'Export response' ), 'content' => me()->forms()->export_response( $surveys, $results ) ] );
        break;

        case 'delete':
            return cms_json_encode( [ 'title' => t( 'Delete response' ), 'content' => me()->forms()->delete_response( $surveys, $results ) ] );
        break;
    }
});

ajax()->add_call( 'manage-result2', function() {
    if( !isset( $_GET['action2'] ) || !isset( $_GET['result'] ) ) return ;

    $results = results( (int) $_GET['result'] );
    if( !$results->getObject() )  return ;
    
    if( !me()->manageSurvey( 'view-result', $results->getSurveyId() ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'add-label':
            try {
                me()->form_actions()->add_label_item( $results, ( $_POST['data']['label'] ?? [] ) );
                return cms_json_encode( [] );
            }

            catch( \Exception $e ) {}
        break;

        case 'add-label-item':
            me()->form_actions()->add_label_checked_item( $results, ( $_POST['data']['results'] ?? [] ) );
            return cms_json_encode( [] );
        break;

        case 'delete':
            try {
                $id = me()->form_actions()->delete_response( $results, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'callback' => [ '{ "callback": "popup_close_from_form" }' ] ] );
            }
            catch( Exception $e ) {
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

/** NEW SURVEY */
ajax()->add_call( 'add-survey', function() {
    return cms_json_encode( [ 'title' => t( 'New survey' ), 'content' => me()->forms()->add_survey() ] );
});

ajax()->add_call( 'add-survey-step2', function() {
    $survey     = isset( $_GET['survey'] ) ? (int) $_GET['survey'] : ( isset( $_POST['data']['survey'] ) ? (int) $_POST['data']['survey'] : false );

    if( !$survey || !me()->manageSurvey( 'add-survey', $survey ) )
    return ;

    $surveys    = me()->getSelectedSurvey();
    $c1         = $surveys->getQuestions()->setVisible( 2 )->count();

    $content    = '
    <div class="formboxes">

    <a href="#" data-popup="manage-survey"' . ( $c1 ? ' class="dis"' : '' ) . ' data-options=\'' . ( cms_json_encode( [ 'action' => 'questions', 'survey' => $surveys->getId() ] ) ) . '\'>
        <section class="formbox">
            <h2>' . ( $c1 ? '<i class="fas fa-check"></i> ' : '<i class="fas fa-hourglass-start"></i> ' ) . t( 'Add questions' ) . '</h2>
            <div>' . t( 'Before you can publish this survey, you must add questions first' ) . '</div>
        </section>
    </a>';

    if( !$c1 ) {
        $content .= '
        <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'templates', 'survey' => $surveys->getId() ] ) ) . '\'>
            <section class="formbox">
                <h2>' . ( $c1 ? '<i class="fas fa-check"></i> ' : '<i class="fas fa-hourglass-start"></i> ' ) . t( 'Templates' ) . '</h2>
                <div>' . t( 'Start with a premade form' ) . '</div>
            </section>
        </a>';
    }

    if( $surveys->getType() < 2 ) {
        $content .= '
        <a href="#" data-popup="manage-survey"' . ( ( $c2 = ( ( $surveys->getType() == 0 && $surveys->getBudget() >= (double) get_option( 'min_cpa_self' ) ) || ( $surveys->getType() == 1 && $surveys->getBudget() >= (double) get_option( 'min_cpa' ) ) ) ) ? ' class="dis"' : '' ) . ' data-options=\'' . ( cms_json_encode( [ 'action' => 'budget', 'survey' => $surveys->getId() ] ) ) . '\'>
            <section class="formbox">
                <h2>' . ( $c2 ? '<i class="fas fa-check"></i> ' : '<i class="fas fa-hourglass-start"></i> ' ) .  t( 'Set the budget' ) . '</h2>
                <div>' . t( "If you choose to buy targeted responses, you need to set a budget for this survey" ) . '</div>
            </section>
        </a>';
    }

    $content .= '
    <a href="#" data-popup="manage-survey"' . ( ( $c3 = $surveys->getCollectors()->count() ) ? ' class="dis"' : '' ) . ' data-options=\'' . ( cms_json_encode( [ 'action' => 'collectors', 'survey' => $surveys->getId() ] ) ) . '\'>
        <section class="formbox">
            <h2>' . ( $c3 ? '<i class="fas fa-check"></i> ' : '<i class="fas fa-hourglass-start"></i> ' ) .  t( 'Collectors' ) . '</h2>
            <div>' . t( "Add at least one collector to make sure respondents will find your survey" ) . '</div>
        </section>
    </a>';

    $content .= '</div>';

    return cms_json_encode( [ 'title' => t( 'Setup' ), 'content' => $content ] );
});

/** CHAT */
ajax()->add_call( 'chat', function() {
    if( isset( $_GET['type'] ) ) {
        switch( $_GET['type'] ) {
            case 'check-new-messages':
                return cms_json_encode( [ 'new_messages' => ( me()->viewAs == 'surveyor' ? teamActionUpdated() : false ) ] );
            break;

            case 'new-messages':
                $markup     = '';
                $myTeam     = me()->myTeam();
                $shortcodes = new \site\shortcodes;

                $lastAction = me()->getTeamChatLastAction();
                $newMessages= $myTeam->chat()
                            ->setFromDate( $lastAction );
                me()->updateTeamLastAction();

                foreach( $newMessages->fetch( -1 ) as $message ) {
                    $user       = users( $message->user );
                    $user       ->getObject();
                    $shortcodes ->setInlineContent( esc_html( $message->text ) );
                    $markup     .= '
                    <div class="td">
                        <div class="w100p">
                            <div class="sav sav4">' . $user->getAvatarMarkup() . '</div>
                            <div>
                                <div>
                                    <div>' . ( me()->getId() === $user->getId() ? t( '<strong>You</strong> said' ) . ':' : sprintf( t( '<strong>%s</strong> says:' ), esc_html( $user->getDisplayName() ) ) ) . '</div>
                                    <span>' . custom_time( $message->date, 2 ) . '</span>
                                </div>
                                <div>' . $shortcodes->inlineMarkup() . '</div>
                            </div>
                        </div>
                    </div>';
                }
            
                return cms_json_encode( [ 'messages' => $markup ] );
            break;

            case 'load-messages':
                $markup     = '';
                $myTeam     = me()->myTeam();
                $shortcodes = new \site\shortcodes;

                $lastAction = me()->getTeamChatLastAction();
                $newMessages= $myTeam->chat();

                if( !empty( $_POST['last_msg'] ) ) {
                    $newMessages->setDateUntil( $_POST['last_msg'] );
                }

                me()->updateTeamLastAction();

                $the_limit  = 50;
                $messages   = 0;
                $last_msg   = NULL;

                foreach( $newMessages->fetch( $the_limit ) as $message ) {
                    $user       = users( $message->user );
                    $user       ->getObject();
                    $shortcodes ->setInlineContent( esc_html( $message->text ) );
                    $messages   ++;
                    $last_msg   = $message->date;
                    $markup     .= '
                    <div class="td">
                        <div class="w100p">
                            <div class="sav sav4">' . $user->getAvatarMarkup() . '</div>
                            <div>
                                <div>
                                    <div>' . ( me()->getId() === $user->getId() ? t( '<strong>You</strong> said' ) . ':' : sprintf( t( '<strong>%s</strong> says:' ), esc_html( $user->getDisplayName() ) ) ) . '</div>
                                    <span>' . custom_time( $message->date, 2 ) . '</span>
                                </div>
                                <div>' . $shortcodes->inlineMarkup() . '</div>
                            </div>
                        </div>
                    </div>';
                }
            
                return cms_json_encode( [ 'messages' => $markup, 'next_page' => ( $the_limit == $messages ), 'last_message' => $last_msg ] );
            break;

            case 'send-message':
                if( empty( $_POST['message'] ) ) return ;

                $message    = trim( $_POST['message'] );
                $shortcodes = new \site\shortcodes;
                $shortcodes ->setInlineContent( esc_html( $message ) );
            
                if( !isset( $_POST['team_id'] ) || !me()->actions()->add_chat_message( $message, (int) $_POST['team_id'] ) )
                return ;
            
                $markup = '
                <div class="td">
                    <div class="w100p">
                        <div class="sav sav4">' . me()->getAvatarMarkup() . '</div>
                        <div>
                            <div>
                                <div>' . t( '<strong>You</strong> said' ) . ':</div>
                                <span>' . custom_time( NULL, 2 ) . '</span>
                            </div>
                            <div>' . $shortcodes->inlineMarkup() . '</div>
                        </div>
                    </div>
                </div>';
            
                return cms_json_encode( [ 'message' => $markup ] );
            break;
        }
    }
});


/** CONFIRM LOGIN */
ajax()->add_call( 'confirm', function() {
    if( !me_logged() ) {
        return ;
    }

    return filters()->do_filter( 'confirm', '' );
});

ajax()->add_call( 'confirm2', function() {
    try {
        me_logged()->actions()->confirm( $_POST['data']  );

        global $me;
        $me = me_logged();

        return cms_json_encode( [ 'status' => 'success', 'msg' => "Confirmed!", 'callback' => '{
            "callback": "part_markup_changer",
            "elements": {
                ".nav.user.side-box": "' . base64_encode( user_nav() ) . '",
                ".head-user-els": "' . base64_encode( user_head() ) . '"
            },
            "is_logged": 1
        }' ] );
    }
    catch( Exception $e ) { 
        return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
    }
});

ajax()->add_call( 'survey-rparts', function() {
    switch( $_POST['action'] ) {
        case 'r_newReport':
            $survey     = surveys( (int) $_POST['survey'] );
            $survey     ->getObject();
            $content    = admin\markup\survey::resultForm( $survey, ( $_POST['pos'] ?? 1 ) );
            return cms_json_encode( [ 'content' => $content ] );
        break;

        case 'r_reportView':
            $reportId   = (int) $_POST['reportId'] ?? 0;
            $survey     = surveys( (int) $_POST['survey'] );
            $smarkup    = $survey->reportMarkup( $reportId );

            if( !$smarkup ) return cms_json_encode( [ 'content' => '' ] );
            $markup = '
            <div class="tr">
                <h3 class="df w100p mb0">
                    <span>' . esc_html( $smarkup->getTitle() ) . '</span>
                    <span class="mla asc">
                        <ul class="btnset s2">
                            <li>
                                <a href="#"><i class="fas fa-bars"></i></a>
                                <ul class="btnset">
                                <li><a href="' . admin_url( 'survey/' . $survey->getId() . '/responses/report/' . $reportId ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'responses', 'id' => $survey->getId(), 'report' => $reportId ] ) . '\'>' . t( 'View responses' ) . '</a></li>
                                <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'export-report', 'survey' => $survey->getId(), 'report' => $reportId ] ) . '\'>' . t( 'Export' ) . '</a></li>';
                                    if( !$smarkup->getPosition() ) {
                                        $markup .= '
                                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'edit-report', 'survey' => $survey->getId(), 'report' => $reportId ] ) . '\'>' . t( 'Edit' ) . '</a></li>
                                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'share-report', 'survey' => $survey->getId(), 'report' => $reportId ] ) . '\'>' . t( 'Share' ) . '</a></li>
                                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'delete-report', 'survey' => $survey->getId(), 'report' => $reportId, 'loc' => 'dashboard' ] ) . '\'>' . t( 'Delete' ) . '</a></li>';
                                    } else {
                                        $markup .= '
                                        <li><a href="#" data-ajax="manage-survey3" data-data=\'' . cms_json_encode( [ 'action' => 'save-report', 'survey' => $survey->getId(), 'report' => $reportId ] ) . '\'>' . t( 'Save' ) . '</a></li>';
                                    }
                                $markup .= '
                                </ul>
                            </li>
                        </ul>
                    </span>
                </h3>
            </div>';

            foreach( $smarkup->questions() as $question ) {
                if( $question )
                $markup .= '<div class="td">' . $question . '</div>';
            }

            $result                 = [];
            $result['content']      = $markup;
            $result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
                "callback": "init_survey_chart2",
                "container": ".table.report-' . $smarkup->getId() . '",
                "placeholders": ' . cms_json_encode( $smarkup->getPlaceholders() ) . ',
                "data": ' . cms_json_encode( $smarkup->getData() ) . '
            }' ];

            return cms_json_encode( $result );
        break;
    }
});

ajax()->add_call( 'add-favorite', function() {
    if( !me() )
    return ;

    $r  = [];
    $s  = $_POST['id'] ?? NULL;

    if( $s ) {
        try {
            me()->actions()->addFavorite( (int) $s );

            $r['callback'] = '{
                "callback": "markup_changer",
                "html": "' . base64_encode( '<a href="#" data-ajax="remove-favorite" data-data=\'' . cms_json_encode( [ 'id' => (int) $s ] ) . '\'><i class="fas fa-heart"></i></a>' ) . '"
            }';
        }
        
        catch( \Exception $e ) {}
    }

    return cms_json_encode( $r );
});

ajax()->add_call( 'remove-favorite', function() {
    if( !me() )
    return ;
    
    $r  = [];
    $s  = $_POST['id'] ?? NULL;

    if( $s ) {
        try {
            me()->actions()->deleteFavorite( (int) $s );

            $r['callback'] = '{
                "callback": "markup_changer",
                "html": "' . base64_encode( '<a href="#" data-ajax="add-favorite" data-data=\'' . cms_json_encode( [ 'id' => (int) $s ] ) . '\'><i class="far fa-heart"></i></a>' ) . '"
            }';
        }
        
        catch( \Exception $e ) {}
    }

    return cms_json_encode( $r );
});

ajax()->add_call( 'add-saved', function() {
    if( !me() )
    return ;

    $r  = [];
    $s  = $_POST['id'] ?? NULL;
    $id = $_POST['survey'] ?? NULL;

    if( $s ) {
        try {
            me()->actions()->addSaved( (int) $s );

            $r['callback'] = '{
                "callback": "markup_changer",
                "html": "' . base64_encode( '<a href="#" data-ajax="remove-saved" data-data=\'' . cms_json_encode( [ 'id' => (int) $s, 'survey' => (int) $id ] ) . '\'><i class="fas fa-calendar-check"></i></a>' ) . '"
            }';
        }
        
        catch( \Exception $e ) {}
    }

    return cms_json_encode( $r );
});

ajax()->add_call( 'remove-saved', function() {
    if( !me() )
    return ;
    
    $r  = [];
    $s  = $_POST['id'] ?? NULL;
    $id = $_POST['survey'] ?? NULL;

    if( $s ) {
        try {
            me()->actions()->deleteSaved( (int) $id );

            $r['callback'] = '{
                "callback": "markup_changer",
                "html": "' . base64_encode( '<a href="#" class="slnk" data-ajax="add-saved" data-data=\'' . cms_json_encode( [ 'id' => (int) $s, 'survey' => $id ] ) . '\'><i class="fas fa-calendar"></i></a>' ) . '"
            }';
        }
        
        catch( \Exception $e ) {}
    }

    return cms_json_encode( $r );
});
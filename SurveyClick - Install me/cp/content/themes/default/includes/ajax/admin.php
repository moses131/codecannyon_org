<?php

// Not user
if( !me() )
    return ;

// Banned
else if( me()->isBanned() )
    return ;  

// Not an admin
else if( !me()->isModerator() )
    return ;

/** NEW ENTRIES */
ajax()->add_call( 'manage-new', function() {
    if( !me()->isAdmin() || !isset( $_POST['options']['action'] ) )
    return ;

    switch( $_POST['options']['action'] ) {
        case 'add-user':
            try {
                $form = me()->admin_forms()->add_user();
                return cms_json_encode( [ 'title' => t( 'Add user' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;
    }
});

ajax()->add_call( 'manage-new2', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'add-user':
            try {
                me()->admin_form_actions()->add_user( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Added' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'manage-new3', function() {
    if( !me()->isAdmin() || !isset( $_POST['action'] ) )
    return ;

    switch( $_POST['action'] ) {
        case 'remove-admin-action':
            if( !isset( $_POST['id'] ) || !me()->isOwner() ) return ;

            try {
                me()->admin_actions()->remove_admin_action( (int) $_POST['id'] );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

/** MANAGE WEBSITE */
ajax()->add_call( 'manage-website', function() {
    if( !me()->isAdmin() || !isset( $_POST['options']['action'] ) )
    return ;

    switch( $_POST['options']['action'] ) {
        case 'clean':
            try {
                $form = me()->admin_forms()->clean_website();
                return cms_json_encode( [ 'title' => t( 'Clear data' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;
    }
});

/** MANAGE USERS */
ajax()->add_call( 'manage-users', function() {
    if( !me()->isAdmin() || !isset( $_POST['data']['action'] ) || !isset( $_POST['data']['id'] ) )
    return ;

    $users = new \query\users( (int) $_POST['data']['id'] );
    if( !$users->getObject() )
    return ;

    switch( $_POST['data']['action'] ) {
        case 'edit':
            try {
                $form = me()->admin_forms()->edit_user( $users );
                return cms_json_encode( [ 'title' => t( 'Edit user' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'send-alert':
            try {
                $form = me()->admin_forms()->send_alert( $users );
                return cms_json_encode( [ 'title' => t( 'Send alert' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'ban':
            try {
                $form = me()->admin_forms()->ban_user( $users );
                return cms_json_encode( [ 'title' => t( 'Ban user' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'change-password':
            try {
                $form = me()->admin_forms()->change_user_password( $users );
                return cms_json_encode( [ 'title' => t( 'Change password' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'user-balance':
            try {
                $form = me()->admin_forms()->user_balance( $users );
                return cms_json_encode( [ 'title' => t( 'User balance' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'info-user':
            $markup = '<div class="table mb0">';
            $tds    = [];

            $ref = '
            <div class="td">
                <div>' . t( 'Referred by' ) . '</div>
                <div>';
                if( $users->getRefId() ) {
                    $refUser = users( $users->getRefId() );
                    if( !$refUser->getObject() )
                        $ref .= t( '(deleted)' );
                    else
                        $ref .= esc_html( $refUser->getDisplayName() );
                } else {
                    $ref .= t( '(not set)' );
                }
                $ref .= '
                </div>
            </div>';
            
            $tds['ref'] = $ref;

            $refed = '
            <div class="td">
                <div>' . t( 'Referred' ) . '</div>
                <div>';
                $rusers = users()
                        ->setRefId( $users->getId() )
                        ->count();

                if( $rusers ) {
                    $refed .= sprintf( t( '%s member', 'main', $rusers, '%s members' ), $rusers );
                } else {
                    $refed .= sprintf( t( '%s members' ), 0 );
                }
                $refed .= '
                </div>
            </div>';

            $tds['refed'] = $refed;

            $lastact = custom_time( $users->getLastAction() );
            $refed = '
            <div class="td">
                <div>' . t( 'Last action' ) . '</div>
                <div title="' . $lastact[0] . '">' . $lastact[1] . '</div>
            </div>';

            $tds['lastact'] = $refed;

            $aplan = '
            <div class="td">
                <div>' . t( 'Active plan' ) . '</div>
                <div>';
                if( !$users->myLimits()->isFree() )
                    $aplan .= esc_html( $users->myLimits()->getPlanName() );
                else $aplan .= '-';
                $aplan .= '
                </div>
            </div>';

            $tds['actplan'] = $aplan;

            $markup .= implode( "\n", $tds );
            $markup .= '</div>';

            return cms_json_encode( [ 'content' => $markup ] );
        break;
    }
});

ajax()->add_call( 'manage-users2', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->edit_user( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Profile edited' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
        
        case 'send-alert':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->send_alert( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Alert sent' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'ban':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->ban_user( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Banned' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'change-password':
            if( !isset( $_GET['id'] ) ) 
            return ;

            try {
                me()->admin_form_actions()->change_password( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Password changed successfully' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'user-balance':
            if( !isset( $_GET['id'] ) ) 
            return ;

            try {
                me()->admin_form_actions()->user_balance( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Updated' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});


/** MANAGE TEAMS */
ajax()->add_call( 'manage-teams', function() {
    if( !me()->isAdmin() || !isset( $_POST['data']['action'] ) || !isset( $_POST['data']['id'] ) )
    return ;

    $teams = new \query\team\teams( (int) $_POST['data']['id'] );
    if( !$teams->getObject() )
    return ;

    switch( $_POST['data']['action'] ) {
        case 'edit':
            try {
                $form = me()->admin_forms()->edit_team( $teams );
                return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $teams->getName() ) ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;
    }
});

ajax()->add_call( 'manage-teams2', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'edit':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->edit_team( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Team edited' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

/** MANAGE USERS */
ajax()->add_call( 'admin-manage-surveys', function() {
    if( !me()->isModerator() || !isset( $_POST['data']['action'] ) || !isset( $_POST['data']['id'] ) )
    return ;

    $surveys = surveys( (int) $_POST['data']['id'] );
    if( !$surveys->getObject() )
    return ;

    switch( $_POST['data']['action'] ) {
        case 'change-status':
            try {
                $form = me()->admin_forms()->edit_survey_status( $surveys );
                return cms_json_encode( [ 'title' => t( 'Change status' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;
    }
});

ajax()->add_call( 'admin-manage-surveys2', function() {
    if( !me()->isModerator() || !isset( $_GET['action2'] ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'change-status':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->edit_survey_status( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});

ajax()->add_call( 'admin-manage-surveys3', function() {
    if( !isset( $_POST['action'] ) )
    return ;

    switch( $_POST['action'] ) {
        case 'approve':
            if( !me()->isModerator() || !isset( $_POST['id'] ) ) return ;

            try {
                me()->admin_form_actions()->edit_survey_status( (int) $_POST['id'], [ 'status' => 3 ] );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_this"
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'reject':
            if( !me()->isModerator() || !isset( $_POST['id'] ) ) return ;

            try {
                me()->admin_form_actions()->edit_survey_status( (int) $_POST['id'], [ 'status' => 0 ] );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_this"
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
} );

/** MANAGE KYC */
ajax()->add_call( 'manage-kyc', function() {
    if( !me()->isModerator() || !isset( $_POST['data']['action'] ) || !isset( $_POST['data']['id'] ) )
    return ;

    $intents    = new \query\user_intents;
    $intents    ->setId( (int) $_POST['data']['id']  );
    if( !$intents->getObject() || $intents->getTypeId() != 1 )
    return ;

    switch( $_POST['data']['action'] ) {
        case 'view':
            if( !isset( $_POST['data']['id'] ) )
            return ;

            $user       = $intents->getUser();
            $summary    = $intents->getTextJson();
            $content    = '';

            if( !empty( $summary['doc'] ) && ( $imageURL = mediaLinks( $summary['doc'] )->getItemURL() ) ) {
                $content    .= '<h2>' . t( 'Document' ) . '</h2>';
                $content    .= '<div class="mb10"><img class="mw100p" src="' . esc_url( $imageURL ) . '" /></div>';
                $content    .= '<div class="mb40"><a href="' . esc_url( $imageURL ) . '" target="_blank" class="btn">' . t( 'Open in a new tab' ) . '</a></div>';
            }

            if( !empty( $summary['self'] ) && ( $imageURL = mediaLinks( $summary['self'] )->getItemURL() ) ) {
                $content    .= '<h2>' . t( 'Selfie' ) . '</h2>';
                $content    .= '<div class="mb10"><img class="mw100p" src="' . esc_url( $imageURL ) . '" /></div>';
                $content    .= '<div class="mb40"><a href="' . esc_url( $imageURL ) . '" target="_blank" class="btn">' . t( 'Open in a new tab' ) . '</a></div>';
            }

            if( $user->getObject() ) {
                $content .= '<h2>' . esc_html( $user->getDisplayName() ) . '</h2>';
                $content .= '
                <div class="table t2 mb0">';

                $info = [];

                $info['country'] = '
                <div class="td">
                    <div class="tl w150p">' . t( 'Country' ) . '</div>
                    <div class="tc">' . $user->getCurrentCountry()->name . '</div>
                </div>';

                $info['address'] = '
                <div class="td">
                    <div class="tl w150p">' . t( 'Address' ) . '</div>
                    <div class="tc">' . nl2br( esc_html( $user->getAddress() ) ) . '</div>
                </div>';

                array_map( function( $v ) use ( &$content ) {
                    $content .= $v;
                }, filters()->do_filter( 'kyc-request-user-info-list', $info, $user ) );

                $content .= '
                </div>';
            }

            return cms_json_encode( [ 'title' => t( 'Verification request' ), 'content' => $content ] );
        break;
    }
});

ajax()->add_call( 'manage-kyc3', function() {
    if( !me()->isModerator() || !isset( $_POST['action'] ) )
    return ;

    switch( $_POST['action'] ) {
        case 'approve':
            if( !isset( $_POST['id'] ) ) return ;

            try {
                me()->admin_actions()->approve_kyc( (int) $_POST['id'] );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'reject':
            if( !isset( $_POST['id'] ) ) return ;

            try {
                me()->admin_actions()->reject_kyc( (int) $_POST['id'] );

                return cms_json_encode( [ 'callback' => '{
                    "callback": "markup_delete_table_td"
                }' ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
} );

/** MANAGE SHOP */
ajax()->add_call( 'manage-shop', function() {
    if( !me()->isAdmin() || !isset( $_POST['data']['action'] ) )
    return ;

    switch( $_POST['data']['action'] ) {
        case 'add-category':
            try {
                $form = me()->admin_forms()->add_shop_category();
                return cms_json_encode( [ 'title' => t( 'Add category' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'edit-category':
            if( !isset( $_POST['data']['id'] ) )
            return ;

            $categories = new \query\shop\categories( (int) $_POST['data']['id'] );
            if( !$categories->getObject() )
            return ;

            try {
                $form = me()->admin_forms()->edit_shop_category( $categories );
                return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $categories->getName() ) ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'add-item':
            try {
                $form = me()->admin_forms()->add_shop_item();
                return cms_json_encode( [ 'title' => t( 'Add item' ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'edit-item':
            if( !isset( $_POST['data']['id'] ) )
            return ;

            $items = new \query\shop\items( (int) $_POST['data']['id'] );
            if( !$items->getObject() )
            return ;

            try {
                $form = me()->admin_forms()->edit_shop_item( $items );
                return cms_json_encode( [ 'title' => sprintf( t( '[edit] %s' ), esc_html( $items->getName() ) ), 'content' => $form ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'change-order-status':
            if( !isset( $_POST['data']['id'] ) )
            return ;

            $orders = new \query\shop\orders( (int) $_POST['data']['id'] );
            if( !$orders->getObject() )
            return ;

            try {
                $form = me()->admin_forms()->edit_shop_order_status( $orders );
                return cms_json_encode( [ 'title' => t( 'Change status' ), 'content' => $form, 'remove_prev_all' => true ] );
            }

            catch( \Exception $e ) {}
        break;

        case 'view-order':
            if( !isset( $_POST['data']['id'] ) )
            return ;

            $order = new \query\shop\orders( (int) $_POST['data']['id'] );
            if( !$order->getObject() )
            return ;

            $user       = $order->getUser();
            $summary    = $order->getSummaryJson();
            $content    = '<h2>' . t( 'Summary' ) . '</h2>';

            if( !empty( $summary ) ) {
                $content .= '
                <div class="table">

                <div class="tr">
                    <div class="tl w150p">' . t( 'Item' ) . '</div>
                    <div class="tc">' . t( 'Quantity' ) . '</div>
                    <div class="tc">' . t( 'Stars' ) . '</div>
                </div>';

                $total  = 0;

                foreach( $order->getSummaryJson() as $item ) {
                    $content .= '
                    <div class="td">
                        <div class="tl w150p">' . esc_html( $item['name'] ) . '</div>
                        <div class="tc">' . esc_html( $item['qt'] ) . '</div>
                        <div class="tc">' . esc_html( $item['total'] ) . '</div>
                    </div>';
                    $total  += (double) $item['total'];
                }

                $content    .= '
                </div>';

                $content    .= '<h2>' . t( 'Total' ) . '</h2>';
                $content    .= '
                <div class="table ns nb">
                    <div class="td">
                        <div class="tl w150p"></div>
                        <div></div>
                        <div class="tc">' . $total . '</div>
                    </div>
                </div>';
            }

            if( $user->getObject() ) {
                $content .= '<h2>' . esc_html( $user->getDisplayName() ) . '</h2>';
                $content .= '
                <div class="table">';

                $info = [];

                $info['country'] = '
                <div class="td">
                    <div class="tl w150p">' . t( 'Country' ) . '</div>
                    <div class="tc">' . $user->getCurrentCountry()->name . '</div>
                </div>';

                $info['address'] = '
                <div class="td">
                    <div class="tl w150p">' . t( 'Address' ) . '</div>
                    <div class="tc">' . nl2br( esc_html( $user->getAddress() ) ) . '</div>
                </div>';

                $PayPalAddress = $user->getOption( 'pp_address' );

                if( $PayPalAddress->getValue() ) {
                    $info['pp_address'] = '
                    <div class="td">
                        <div class="tl w150p">' . t( 'Paypal address' ) . '</div>
                        <div class="tc">' . esc_html( $PayPalAddress->getValue() ) . '</div>
                    </div>';
                }

                $StripeAddress = $user->getOption( 'stripe_address' );

                if( $StripeAddress->getValue() ) {
                    $info['stripe_address'] = '
                    <div class="td">
                        <div class="tl w150p">' . t( 'Stripe address' ) . '</div>
                        <div class="tc">' . esc_html( $StripeAddress->getValue() ) . '</div>
                    </div>';
                }

                array_map( function( $v ) use ( &$content ) {
                    $content .= $v;
                }, filters()->do_filter( 'order-user-info-list', $info, $order, $user ) );

                $content .= '
                </div>';
            }

            $content .= '<h2>';

            switch( $order->getStatus() ) {
                case 0:
                    $content .= t( 'Canceled' );
                break;

                case 1:
                    $content .= t( 'Pending' );
                break;

                default:
                    $content .= t( 'Approved' );
                break;
            }

            $content .= '</h2>';
            $content .= '<a data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'change-order-status', 'id' => $order->getId() ] ) ) . '\' class="btn">' . t( 'Change status' ) . '</a>';

            return cms_json_encode( [ 'title' => t( 'View order' ), 'classes' => [ 's2' ], 'content' => $content ] );
        break;
    }
});

ajax()->add_call( 'manage-shop2', function() {
    if( !me()->isAdmin() || !isset( $_GET['action2'] ) )
    return ;

    switch( $_GET['action2'] ) {
        case 'add-category':
            try {
                me()->admin_form_actions()->add_shop_category( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Added' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-category':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            $categories = new \query\shop\categories( (int) $_GET['id'] );
            if( !$categories->getObject() )
            return ;

            try {
                me()->admin_form_actions()->edit_shop_category( $categories, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'add-item':
            try {
                me()->admin_form_actions()->add_shop_item( ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Added' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'edit-item':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            $items = new \query\shop\items( (int) $_GET['id'] );
            if( !$items->getObject() )
            return ;

            try {
                me()->admin_form_actions()->edit_shop_item( $items, ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;

        case 'change-order-status':
            if( !isset( $_GET['id'] ) ) 
            return ;
            
            try {
                me()->admin_form_actions()->edit_shop_order_status( (int) $_GET['id'], ( $_POST['data'] ?? [] ) );
                return cms_json_encode( [ 'status' => 'success', 'msg' => t( 'Saved' ) ] );
            }
            catch( Exception $e ) { 
                return cms_json_encode( [ 'status' => 'error', 'msg' => $e->getMessage() ] ); 
            }
        break;
    }
});
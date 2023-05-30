<?php

namespace admin\markup;

class nav extends \markup\front_end\nav {

    function __construct( string $type = '' ) {
        switch( $type ) {
            case 'head':
                $this->{ 'head_nav_' . me()->viewAs }();
                $this->menu = 'head';
            break;

            case 'head-left':
                $this->{ me()->viewAs . '_head_left' }();
                $this->menu = 'head_left';
            break;

            case 'survey':
                $this->nav_survey();
                $this->menu = 'survey';
            break;

            default:
                $this->{ 'nav_' . me()->viewAs }();
                $this->menu = 'main';
        }
    }

    private function nav_respondent() {
        filters()->add_filter( 'respondent_nav', function( $f, $nav ) {
            $nav['dashboard'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'index' ]
            ];

            $nav['zone'] = [ 
                'type'      => 'label', 
                'label'     => t( 'My zone' ), 
                'position'  => 2, 
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['surveys'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'surveys' ), 
                'label'     => t( 'Find surveys' ), 
                'icon'      => '<i class="fas fa-poll-h"></i>', 
                'position'  => 1,
                'parent_id' => 'zone', 
                'attrs'     => [ 'data-to' => 'surveys' ]
            ];

            $respondent_vars    = filters()->do_filter( 'respondent_vars', [] );
            $pending_completion = !empty( $respondent_vars['pending_responses'] ) ? (int) $respondent_vars['pending_responses'] : '';

            $nav['awaiting_completion'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'my-responses/status/1' ), 
                'label'     => t( 'Awaiting completion' ), 
                'after'     => '<span class="a1">' . $pending_completion . '</span>',
                'icon'      => '<i class="fas fa-hourglass-half"></i>', 
                'position'  => 2,
                'parent_id' => 'zone',
                'attrs'     => [ 'data-to' => 'my-responses', 'data-options' => [ 'status' => 1 ] ],
                'list_attr' => [ 'data-pr-count' => $pending_completion ]
            ];

            $nav['saved'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'saved' ), 
                'label'     => t( 'Saved' ), 
                'icon'      => '<i class="fas fa-calendar-check"></i>', 
                'position'  => 3,
                'parent_id' => 'zone', 
                'attrs'     => [ 'data-to' => 'saved' ]
            ];

            $nav['answers'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'my-responses' ), 
                'label'     => t( 'My responses' ), 
                'icon'      => '<i class="fas fa-pencil-alt"></i>', 
                'position'  => 4,
                'parent_id' => 'zone', 
                'attrs'     => [ 'data-to' => 'my-responses' ]
            ];

            $nav['loyalty'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Loyalty stars' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['lp_shop'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Shop' ), 
                'icon'      => '<i class="fas fa-store-alt"></i>', 
                'position'  => 1,
                'parent_id' => 'loyalty'
            ];

            $nav['lp_shop_all'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop' ), 
                'label'     => t( 'All' ), 
                'position'  => 1,
                'parent_id' => 'lp_shop', 
                'attrs'     => [ 'data-to' => 'shop' ]
            ];

            foreach( my_shop()->categories->fetch( -1 ) as $category ) {
                $nav['shop_cat_' . $category->id] = [
                    'type'      => 'link', 
                    'url'       => admin_url( 'shop/category/' . $category->id ), 
                    'label'     => esc_html( $category->name ), 
                    'position'  => 2,
                    'parent_id' => 'lp_shop', 
                    'attrs'     => [ 'data-to' => 'shop', 'data-options' => [ 'category' => $category->id ] ]
                ];
            }

            $nav['orders'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'orders' ), 
                'label'     => t( 'My orders' ), 
                'icon'      => '<i class="fas fa-shopping-bag"></i>', 
                'position'  => 2,
                'parent_id' => 'loyalty', 
                'attrs'     => [ 'data-to' => 'orders' ]
            ];

            $nav['lp_earn'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Earn stars' ), 
                'icon'      => '<i class="fas fa-star cl3"></i>', 
                'position'  => 3,
                'parent_id' => 'loyalty'
            ];

            $nav['ref_friend'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'answers' ), 
                'label'     => t( 'Invite a friend' ), 
                'icon'      => '<i class="fas fa-user-plus"></i>', 
                'position'  => 1,
                'parent_id' => 'lp_earn', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'invite-a-friend' ] ]
            ];

            $nav['all_transactions'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Transactions' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['payouts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'payouts' ), 
                'label'     => t( 'Payouts' ), 
                'icon'      => '<i class="fas fa-comment-dollar"></i>', 
                'position'  => 1,
                'parent_id' => 'all_transactions', 
                'attrs'     => [ 'data-to' => 'payouts' ]
            ];

            $nav['reportings'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'reportings' ), 
                'label'     => t( 'Reportings' ), 
                'icon'      => '<i class="fas fa-chart-line"></i>', 
                'position'  => 2,
                'parent_id' => 'all_transactions', 
                'attrs'     => [ 'data-to' => 'reportings' ]
            ];

            $nav['transactions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'transactions' ), 
                'label'     => t( 'Transactions' ), 
                'icon'      => '<i class="fas fa-search-dollar"></i>', 
                'position'  => 3,
                'parent_id' => 'all_transactions', 
                'attrs'     => [ 'data-to' => 'transactions' ]
            ];

            $nav['settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cog"></i>', 
                'position'  => 10, 
                'parent_id' => false, 
            ];

            $nav['edit_profile'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'settings-general' ), 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['change_p'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Change password' ), 
                'position'  => 2, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'change-password' ] ]
            ];

            $nav['preferences'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Preferences' ), 
                'position'  => 3, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'preferences' ] ]
            ];

            $nav['payout_opts'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payout options' ), 
                'position'  => 4, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'payout-options' ] ]
            ];

            $nav['privacy'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Privacy options' ), 
                'position'  => 5, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'privacy-options' ] ]
            ];

            $nav['security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security options' ), 
                'position'  => 6, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'security-options' ] ]
            ];

            return $nav;
        } );

        $this->nav = filters()->do_filter( 'respondent_nav', [] );
    }

    private function nav_surveyor() {
        filters()->add_filter( 'surveyor_nav', function( $f, $nav ) {
            $nav['dashboard'] = [
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'index' ]
            ];

            $nav['content'] = [ 
                'type'      => 'label', 
                'label'     => t( 'My zone' ), 
                'position'  => 2,
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['surveys'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'surveys' ), 
                'label'     => t( 'My surveys' ), 
                'icon'      => '<i class="fas fa-poll-h"></i>', 
                'position'  => 1,
                'parent_id' => 'content', 
                'attrs'     => [ 'data-to' => 'surveys' ]
            ];

            $pending = me()->getSurveyResponses()->setStatus( 2 )->count();

            $nav['pending_responses'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'pending-responses' ), 
                'label'     => t( 'Pending responses' ), 
                'after'     => '<span class="a1">' . $pending . '</span>',
                'icon'      => '<i class="fas fa-hourglass-half"></i>', 
                'position'  => 1.1, 
                'parent_id' => 'content', 
                'attrs'     => [ 'data-to' => 'pending-responses' ],
                'list_attr' => [ 'data-pr-count' => $pending ]
            ];

            $nav['favorites'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'favorites' ), 
                'label'     => t( 'Favorites' ), 
                'icon'      => '<i class="fas fa-heart"></i>', 
                'position'  => 2,
                'parent_id' => 'content', 
                'attrs'     => [ 'data-to' => 'favorites' ]
            ];

            $nav['nsurvey'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'New survey' ), 
                'icon'      => '<i class="fas fa-plus"></i>', 
                'position'  => 3,
                'parent_id' => 'content', 
                'attrs'     => [ 'data-popup' => 'add-survey' ]
            ];

            $nav['team'] = [ 
                'type'      => 'label', 
                'label'     => t( 'My team' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['transactions_label'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Transactions' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['reportings'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'reportings' ), 
                'label'     => t( 'Responses & commissions' ), 
                'icon'      => '<i class="fas fa-chart-line"></i>', 
                'position'  => 3,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'reportings' ]
            ];

            $nav['transactions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'transactions' ), 
                'label'     => t( 'Transactions' ), 
                'icon'      => '<i class="fas fa-search-dollar"></i>', 
                'position'  => 4,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'transactions' ]
            ];

            $nav['invoicing'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Invoicing' ), 
                'icon'      => '<i class="fas fa-money-bill-wave"></i>', 
                'position'  => 5,
                'parent_id' => 'transactions_label',
            ];

            $nav['invoices'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'invoices' ), 
                'label'     => t( 'Invoices' ), 
                'position'  => 1,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'invoices' ]
            ];

            $nav['receipts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'receipts' ), 
                'label'     => t( 'Receipts' ), 
                'position'  => 2,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'receipts' ]
            ];

            $nav['settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cog"></i>', 
                'position'  => 10, 
                'parent_id' => false, 
            ];

            $nav['edit_profile'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'settings-general' ), 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['change_p'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Change password' ), 
                'position'  => 2, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'change-password' ] ]
            ];

            $nav['preferences'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Preferences' ), 
                'position'  => 3, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'preferences' ] ]
            ];

            $nav['payout_opts'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payout options' ), 
                'position'  => 4, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'payout-options' ] ]
            ];

            $nav['privacy'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Privacy options' ), 
                'position'  => 5, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'privacy-options' ] ]
            ];

            $nav['security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security options' ), 
                'position'  => 6, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'security-options' ] ]
            ];

            if( !( ( $myTeam = me()->myTeam() ) && $myTeam->getObject() ) ) {

                $nav['new_team'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Create a team' ), 
                    'icon'      => '<i class="fas fa-users"></i>', 
                    'position'  => 2,
                    'parent_id' => 'team', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'create-team' ] ]
                ];
        
                $nav['change_team'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'My teams' ), 
                    'icon'      => '<i class="fas fa-random"></i>', 
                    'position'  => 4,
                    'parent_id' => 'team', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'teams' ] ]
                ];
        
            } else {
        
                $nav['team']['label'] = $myTeam->getName();

                $nav['team_room'] = [
                    'type'      => 'link', 
                    'url'       => admin_url( 'myteam' ), 
                    'html_label'=> t( 'Room' ) . '</span><span class="a2"><i class="fas fa-comment-alt"></i>', 
                    'icon'      => '<i class="far fa-comments"></i>', 
                    'class'     => 'chat-alert' . ( !teamActionUpdated() ? ' a2h' : '' ),
                    'position'  => 2,
                    'parent_id' => 'team', 
                    'attrs'     => [ 'data-to' => 'myteam' ]
                ];
        
                $nav['team_members'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Members' ), 
                    'icon'      => '<i class="fas fa-users"></i>', 
                    'position'  => 3,
                    'parent_id' => 'team', 
                    'attrs'     => [ 'data-popup' => 'manage-team', 'data-options' => [ 'action' => 'members', 'team' => $myTeam->getId() ] ]
                ];
        
                if( me()->manageTeam( 'send-invitation', true ) ) {
                    $nav['team_invite'] = [
                        'type'      => 'link', 
                        'url'       => '#', 
                        'label'     => t( 'Invite' ), 
                        'icon'      => '<i class="fas fa-user-plus"></i>', 
                        'position'  => 4,
                        'parent_id' => 'team', 
                        'attrs'     => [ 'data-popup' => 'manage-team', 'data-options' => [ 'action' => 'invite', 'team' => $myTeam->getId() ] ]
                    ];
                }
        
                $nav['teams_more'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Change team & more' ), 
                    'icon'      => '<i class="fas fa-random"></i>', 
                    'position'  => 5,
                    'parent_id' => 'team',
                ];
        
                if( me()->manageTeam( 'edit-team', true ) ) {
                    $nav['edit_team'] = [
                        'type'      => 'link', 
                        'url'       => '#', 
                        'label'     => t( 'Edit team' ), 
                        'icon'      => '<i class="far fa-edit"></i>', 
                        'position'  => 2,
                        'parent_id' => 'teams_more', 
                        'attrs'     => [ 'data-popup' => 'manage-team', 'data-options' => [ 'action' => 'edit', 'team' => $myTeam->getId() ] ]
                    ];
                }
        
                if( me()->manageTeam( 'send-invitation', true ) ) {
                    $nav['team_invites_sent'] = [
                        'type'      => 'link', 
                        'url'       => '#', 
                        'label'     => t( 'Invites sent' ), 
                        'icon'      => '<i class="fas fa-user-clock"></i>', 
                        'position'  => 3,
                        'parent_id' => 'teams_more', 
                        'attrs'     => [ 'data-popup' => 'manage-team', 'data-options' => [ 'action' => 'invitations', 'team' => $myTeam->getId() ] ]
                    ];
                }
        
                $nav['change-team'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Change team' ), 
                    'icon'      => '<i class="fas fa-random"></i>', 
                    'position'  => 4,
                    'parent_id' => 'teams_more', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'teams' ] ]
                ];
        
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'surveyor_nav', [] );
    }

    function nav_moderator() {
        filters()->add_filter( 'moderator_nav', function( $f, $nav ) {
            $nav['dashboard'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'index' ]
            ];

            $nav['website'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Website' ), 
                'position'  => 2,
                'min'       => true,
                'parent_id' => false, 
            ];

            $admin_vars = filters()->do_filter( 'admin_vars', [] );
            $pending    = !empty( $admin_vars['pending_surveys'] ) ? (int) $admin_vars['pending_surveys'] : '';

            $nav['surveys'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'surveys'  . ( $pending ? '/status/2' : '' ) ), 
                'label'     => t( 'Surveys' ), 
                'after'     => '<span class="a1">' . $pending . '</span>',
                'icon'      => '<i class="fas fa-poll-h"></i>', 
                'position'  => 1,
                'parent_id' => 'website', 
                'attrs'     => ( $pending ? [ 'data-to' => 'surveys', 'data-options' => [ 'status' => 2 ] ] : [ 'data-to' => 'surveys' ] )
            ];

            $nav['people'] = [ 
                'type'      => 'label', 
                'label'     => t( 'People' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $kyc_intents_c  = !empty( $admin_vars['kyc_intents'] ) ? (int) $admin_vars['kyc_intents'] : '';

            $nav['users_kyc'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'kyc' ), 
                'label'     => t( 'KYC Verification' ), 
                'after'     => '<span class="a1">' . $kyc_intents_c . '</span>',
                'icon'      => '<i class="fas fa-user-check"></i>', 
                'position'  => 2, 
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'kyc' ]
            ];
            
            $nav['settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cog"></i>', 
                'position'  => 6, 
                'parent_id' => false,
            ];

            $nav['edit_profile'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'settings-general' ), 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['change_p'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Change password' ), 
                'position'  => 2, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'change-password' ] ]
            ];

            $nav['preferences'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Preferences' ), 
                'position'  => 3, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'preferences' ] ]
            ];

            $nav['payout_opts'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payout options' ), 
                'position'  => 4, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'payout-options' ] ]
            ];

            $nav['privacy'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Privacy options' ), 
                'position'  => 5, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'privacy-options' ] ]
            ];

            $nav['security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security options' ), 
                'position'  => 6, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'security-options' ] ]
            ];

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'moderator_nav', [] );
    }

    private function nav_admin() {
        filters()->add_filter( 'admin_nav', function( $f, $nav ) {
            $nav['dashboard'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'index' ]
            ];

            $nav['website'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Website' ), 
                'position'  => 2,
                'min'       => true,
                'parent_id' => false, 
            ];

            $admin_vars = filters()->do_filter( 'admin_vars', [] );
            $pending    = !empty( $admin_vars['pending_surveys'] ) ? (int) $admin_vars['pending_surveys'] : '';

            $nav['surveys'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'surveys'  . ( $pending ? '/status/2' : '' ) ), 
                'label'     => t( 'Surveys' ), 
                'after'     => '<span class="a1">' . $pending . '</span>',
                'icon'      => '<i class="fas fa-poll-h"></i>', 
                'position'  => 1,
                'parent_id' => 'website', 
                'attrs'     => ( $pending ? [ 'data-to' => 'surveys', 'data-options' => [ 'status' => 2 ] ] : [ 'data-to' => 'surveys' ] )
            ];

            $nav['manage_subscriptions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'manage-subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'icon'      => '<i class="fas fa-calendar-alt"></i>', 
                'position'  => 1,
                'parent_id' => 'website', 
                'attrs'     => [ 'data-to' => 'manage-subscriptions' ]
            ];

            $nav['ws_vouchers'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Vouchers' ), 
                'icon'      => '<i class="fas fa-percent"></i>', 
                'position'  => 2, 
                'parent_id' => 'website', 
            ];

            $nav['ws_viewvouchers'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'vouchers' ), 
                'label'     => t( 'View vouchers' ), 
                'position'  => 1, 
                'parent_id' => 'ws_vouchers', 
                'attrs'     => [ 'data-to' => 'vouchers' ]
            ];

            $nav['ws_addvoucher'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Add voucher' ), 
                'position'  => 2, 
                'parent_id' => 'ws_vouchers', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'add-voucher' ] ]
            ];

            $orders    = !empty( $admin_vars['pending_orders'] ) ? (int) $admin_vars['pending_orders'] : '';

            $nav['l_shop'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Loyalty shop' ), 
                'after'     => '<span class="a1">' . $orders . '</span>',
                'icon'      => '<i class="fas fa-shopping-bag"></i>', 
                'position'  => 3,
                'parent_id' => 'website'
            ];

            $nav['ls_pending'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-orders/status/1' ), 
                'label'     => t( 'Pending orders' ), 
                'after'     => '<span class="a1">' . $orders . '</span>',
                'position'  => 1, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-orders', 'data-options' => [ 'status' => 1 ] ],
                'list_attr' => [ 'data-pr-count' => $orders ]
            ];

            $nav['ls_orders'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-orders' ), 
                'label'     => t( 'Orders' ), 
                'position'  => 2, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-orders' ]
            ];

            $nav['ls_categories'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-categories' ), 
                'label'     => t( 'Categories' ), 
                'position'  => 3, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-categories' ]
            ];

            $nav['ls_items'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-items' ), 
                'label'     => t( 'Items' ), 
                'position'  => 4, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-items' ]
            ];

            $nav['ls_add_item'] = [
                'type'      => 'link',
                'url'       => '#' ,
                'label'     => t( 'Add item' ),
                'position'  => 5,
                'parent_id' => 'l_shop',
                'attrs'     => [ 'data-popup' => 'manage-shop', 'data-data' => [ 'action' => 'add-item' ] ]
            ];

            $nav['ws_themes'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Themes' ), 
                'icon'      => '<i class="fas fa-palette"></i>', 
                'position'  => 4, 
                'parent_id' => 'website', 
            ];

            $nav['ws_menus'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'menus' ), 
                'label'     => t( 'Menus' ), 
                'position'  => 4, 
                'parent_id' => 'ws_themes', 
                'attrs'     => [ 'data-to' => 'menus' ]
            ];

            $nav['ws_settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cogs"></i>', 
                'position'  => 10, 
                'parent_id' => 'website', 
            ];

            $nav['ws_countries'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'countries' ), 
                'label'     => t( 'Countries' ), 
                'position'  => 8, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-to' => 'countries' ]
            ];

            $nav['content'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Content' ), 
                'position'  => 3, 
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['ws_categories'] = [ 
                'type'      => 'link', 
                'url'       => '#',  
                'label'     => t( 'Categories' ), 
                'icon'      => '<i class="fas fa-tag"></i>', 
                'position'  => 3,
                'parent_id' => 'content', 
            ];

            $nav['website_cats'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'categories/website' ), 
                'label'     => t( 'View categories' ), 
                'position'  => 1,
                'parent_id' => 'ws_categories', 
                'attrs'     => [ 'data-to' => 'categories', 'data-options' => [ 'type' => 'website' ] ]
            ];

            $nav['ws_addcategory'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Add category' ), 
                'position'  => 2, 
                'parent_id' => 'ws_categories', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'add-category', 'type' => 'website' ] ]
            ];

            $nav['ws_pages'] = [ 
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Pages' ), 
                'icon'      => '<i class="fas fa-scroll"></i>', 
                'position'  => 4,
                'parent_id' => 'content'
            ];

            $nav['website_view'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'pages/website' ), 
                'label'     => t( 'View pages' ), 
                'position'  => 1, 
                'parent_id' => 'ws_pages', 
                'attrs'     => [ 'data-to' => 'pages', 'data-options' => [ 'type' => 'website' ] ]
            ];

            $nav['website_add'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'page/new' ), 
                'label'     => t( 'Add page' ), 
                'position'  => 2, 
                'parent_id' => 'ws_pages', 
                'attrs'     => [ 'data-to' => 'page', 'data-options' => [ 'id' => 'new', 'type' => 'website' ] ]
            ];

            $nav['people'] = [ 
                'type'      => 'label', 
                'label'     => t( 'People' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['users'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'users' ), 
                'label'     => t( 'Users' ), 
                'icon'      => '<i class="fas fa-user-friends"></i>', 
                'position'  => 1,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'users' ]
            ];

            $kyc_intents_c  = !empty( $admin_vars['kyc_intents'] ) ? (int) $admin_vars['kyc_intents'] : '';

            $nav['users_kyc'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'kyc' ), 
                'label'     => t( 'KYC Verification' ), 
                'after'     => '<span class="a1">' . $kyc_intents_c . '</span>',
                'icon'      => '<i class="fas fa-user-check"></i>', 
                'position'  => 2, 
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'kyc' ],
                'list_attr' => [ 'data-pr-count' => $kyc_intents_c ]
            ];

            $nav['nuser'] = [ 
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'New user' ), 
                'icon'      => '<i class="fas fa-user-plus"></i>', 
                'position'  => 3,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-popup' => 'manage-new', 'data-options' => [ 'action' => 'add-user' ] ]
            ];

            $nav['teams'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'teams' ), 
                'label'     => t( 'Teams' ), 
                'icon'      => '<i class="fas fa-users-cog"></i>', 
                'position'  => 4,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'teams' ]
            ];

            $nav['transactions_label'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Transactions' ), 
                'position'  => 5,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['invoicing'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Invoicing' ), 
                'icon'      => '<i class="fas fa-money-bill-wave"></i>', 
                'position'  => 1,
                'parent_id' => 'transactions_label',
            ];

            $nav['invoices'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'invoices' ), 
                'label'     => t( 'Invoices' ), 
                'position'  => 1,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'invoices' ]
            ];

            $nav['receipts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'receipts' ), 
                'label'     => t( 'Receipts' ), 
                'position'  => 2,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'receipts' ]
            ];

            $prequests_c    = !empty( $admin_vars['payout_requests'] ) ? (int) $admin_vars['payout_requests'] : '';

            $nav['payouts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'payouts' . ( $prequests_c ? '/status/1' : '' ) ), 
                'label'     => t( 'Payouts' ), 
                'after'     => '<span class="a1">' . $prequests_c . '</span>',
                'icon'      => '<i class="fas fa-comment-dollar"></i>', 
                'position'  => 2,
                'parent_id' => 'transactions_label', 
                'attrs'     => ( $prequests_c ? [ 'data-to' => 'payouts', 'data-options' => [ 'status' => 1 ] ] : [ 'data-to' => 'payouts' ] )
            ];

            $nav['transactions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'transactions' ), 
                'label'     => t( 'Transactions' ), 
                'icon'      => '<i class="fas fa-search-dollar"></i>', 
                'position'  => 3,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'transactions' ]
            ];

            $nav['subscriptions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'icon'      => '<i class="fas fa-arrows-alt-h"></i>', 
                'position'  => 2,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'subscriptions' ]
            ];

            $nav['reportings'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'reportings' ), 
                'label'     => t( 'Reportings' ), 
                'icon'      => '<i class="fas fa-chart-line"></i>', 
                'position'  => 4,
                'parent_id' => 'transactions_label'
            ];

            $nav['r_surveys'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/surveys' ), 
                'label'     => t( 'Surveys' ), 
                'position'  => 1, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'surveys' ] ]
            ];

            $nav['r_responses'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/responses' ), 
                'label'     => t( 'Responses' ), 
                'position'  => 2, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'responses' ] ]
            ];

            $nav['r_users'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/users' ), 
                'label'     => t( 'Users' ), 
                'position'  => 3, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'users' ] ]
            ];

            $nav['r_commissions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/commissions' ), 
                'label'     => t( 'Commissions' ), 
                'position'  => 4, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'commissions' ] ]
            ];

            $nav['r_w_commissions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/wcommissions' ), 
                'label'     => t( 'Website commissions' ), 
                'position'  => 5, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'wcommissions' ] ]
            ];

            $nav['r_deposits'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/deposits' ), 
                'label'     => t( 'Deposits' ), 
                'position'  => 6, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'deposits' ] ]
            ];

            $nav['r_subscriptions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'position'  => 7, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'subscriptions' ] ]
            ];

            $nav['settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cog"></i>', 
                'position'  => 5, 
                'parent_id' => false,
            ];

            $nav['edit_profile'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'settings-general' ), 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['change_p'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Change password' ), 
                'position'  => 2, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'change-password' ] ]
            ];

            $nav['preferences'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Preferences' ), 
                'position'  => 3, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'preferences' ] ]
            ];

            $nav['payout_opts'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payout options' ), 
                'position'  => 4, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'payout-options' ] ]
            ];

            $nav['privacy'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Privacy options' ), 
                'position'  => 5, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'privacy-options' ] ]
            ];

            $nav['security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security options' ), 
                'position'  => 6, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'security-options' ] ]
            ];

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'admin_nav', [] );
    }

    private function nav_owner() {
        filters()->add_filter( 'owner_nav', function( $f, $nav ) {
            $nav['dashboard'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'index' ]
            ];

            $nav['website'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Website' ), 
                'position'  => 2,
                'min'       => true,
                'parent_id' => false, 
            ];

            $owner_vars = filters()->do_filter( 'owner_vars', [] );
            $pending    = !empty( $owner_vars['pending_surveys'] ) ? (int) $owner_vars['pending_surveys'] : '';

            $nav['surveys'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'surveys'  . ( $pending ? '/status/2' : '' ) ), 
                'label'     => t( 'Surveys' ), 
                'after'     => '<span class="a1">' . $pending . '</span>',
                'icon'      => '<i class="fas fa-poll-h"></i>', 
                'position'  => 1,
                'parent_id' => 'website', 
                'attrs'     => ( $pending ? [ 'data-to' => 'surveys', 'data-options' => [ 'status' => 2 ] ] : [ 'data-to' => 'surveys' ] )
            ];

            $nav['manage_subscriptions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'manage-subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'icon'      => '<i class="fas fa-calendar-alt"></i>', 
                'position'  => 1,
                'parent_id' => 'website', 
                'attrs'     => [ 'data-to' => 'manage-subscriptions' ]
            ];

            $nav['ws_vouchers'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Vouchers' ), 
                'icon'      => '<i class="fas fa-percent"></i>', 
                'position'  => 2, 
                'parent_id' => 'website', 
            ];

            $nav['ws_viewvouchers'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'vouchers' ), 
                'label'     => t( 'View vouchers' ), 
                'position'  => 1, 
                'parent_id' => 'ws_vouchers', 
                'attrs'     => [ 'data-to' => 'vouchers' ]
            ];

            $nav['ws_addvoucher'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Add voucher' ), 
                'position'  => 2, 
                'parent_id' => 'ws_vouchers', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'add-voucher' ] ]
            ];

            $orders    = !empty( $owner_vars['pending_orders'] ) ? (int) $owner_vars['pending_orders'] : '';

            $nav['l_shop'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Loyalty shop' ), 
                'after'     => '<span class="a1">' . $orders . '</span>',
                'icon'      => '<i class="fas fa-shopping-bag"></i>', 
                'position'  => 3,
                'parent_id' => 'website'
            ];

            $nav['ls_pending'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-orders/status/1' ), 
                'label'     => t( 'Pending orders' ), 
                'after'     => '<span class="a1">' . $orders . '</span>',
                'position'  => 1, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-orders', 'data-options' => [ 'status' => 1 ] ],
                'list_attr' => [ 'data-pr-count' => $orders ]
            ];

            $nav['ls_orders'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-orders' ), 
                'label'     => t( 'Orders' ), 
                'position'  => 2, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-orders' ]
            ];

            $nav['ls_categories'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-categories' ), 
                'label'     => t( 'Categories' ), 
                'position'  => 3, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-categories' ]
            ];

            $nav['ls_items'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'shop-items' ), 
                'label'     => t( 'Items' ), 
                'position'  => 4, 
                'parent_id' => 'l_shop', 
                'attrs'     => [ 'data-to' => 'shop-items' ]
            ];

            $nav['ls_add_item'] = [
                'type'      => 'link',
                'url'       => '#' ,
                'label'     => t( 'Add item' ),
                'position'  => 5,
                'parent_id' => 'l_shop',
                'attrs'     => [ 'data-popup' => 'manage-shop', 'data-data' => [ 'action' => 'add-item' ] ]
            ];

            $nav['ws_themes'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Themes' ), 
                'icon'      => '<i class="fas fa-palette"></i>', 
                'position'  => 4, 
                'parent_id' => 'website', 
            ];

            $nav['ws_viewthemes'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'themes' ), 
                'label'     => t( 'View themes' ), 
                'position'  => 1, 
                'parent_id' => 'ws_themes', 
                'attrs'     => [ 'data-to' => 'themes' ]
            ];

            $nav['ws_installtheme'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Install theme' ), 
                'position'  => 2, 
                'parent_id' => 'ws_themes', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'install-theme' ] ]
            ];

            $nav['ws_menus'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'menus' ), 
                'label'     => t( 'Menus' ), 
                'position'  => 4, 
                'parent_id' => 'ws_themes', 
                'attrs'     => [ 'data-to' => 'menus' ]
            ];

            $nav['ws_plugins'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Plugins' ), 
                'icon'      => '<i class="fas fa-plug"></i>', 
                'position'  => 5, 
                'parent_id' => 'website', 
            ];

            $nav['ws_viewplgs'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'plugins' ), 
                'label'     => t( 'View plugins' ), 
                'position'  => 1, 
                'parent_id' => 'ws_plugins', 
                'attrs'     => [ 'data-to' => 'plugins' ]
            ];

            $nav['ws_installplgs'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Install plugin' ), 
                'position'  => 2, 
                'parent_id' => 'ws_plugins', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'install-plugin' ] ]
            ];

            $nav['ws_settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cogs"></i>', 
                'position'  => 10, 
                'parent_id' => 'website', 
            ];

            $nav['ws_general'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'General' ), 
                'position'  => 1, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'general' ] ]
            ];

            $nav['ws_3rd_party'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( '3rd party' ), 
                'position'  => 2,
                'parent_id' => 'ws_settings', 
            ];

            $nav['ws_paypal'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'PayPal' ), 
                'position'  => 3, 
                'parent_id' => 'ws_3rd_party', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'paypal' ] ]
            ];

            $nav['ws_stripe'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Stripe' ), 
                'position'  => 4, 
                'parent_id' => 'ws_3rd_party', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'stripe' ] ]
            ];

            $nav['ws_email'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Email' ), 
                'position'  => 5,
                'parent_id' => 'ws_3rd_party', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'email' ] ]
            ];

            $nav['ws_prices'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Prices' ), 
                'position'  => 3, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'prices' ] ]
            ];

            $nav['ws_security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security' ), 
                'position'  => 4, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'security' ] ]
            ];

            $nav['ws_plans'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payment plans' ), 
                'position'  => 5, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'plans' ] ]
            ];

            $nav['ws_invoicing'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Invoicing' ), 
                'position'  => 6, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'invoicing' ] ]
            ];

            $nav['ws_kyc'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'KYC Verification' ), 
                'position'  => 7, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'kyc' ] ]
            ];

            $nav['ws_tos'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Terms of use pages' ), 
                'position'  => 8, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'tos' ] ]
            ];

            $nav['ws_countries'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'countries' ), 
                'label'     => t( 'Countries' ), 
                'position'  => 9, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-to' => 'countries' ]
            ];

            $nav['ws_referral'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Referral' ), 
                'position'  => 10, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'referral' ] ]
            ];

            $nav['ws_seo'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'SEO' ), 
                'position'  => 11, 
                'parent_id' => 'ws_settings', 
                'attrs'     => [ 'data-popup' => 'website-options', 'data-options' => [ 'action' => 'seo' ] ]
            ];

            $nav['content'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Content' ), 
                'position'  => 3, 
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['ws_categories'] = [ 
                'type'      => 'link', 
                'url'       => '#',  
                'label'     => t( 'Categories' ), 
                'icon'      => '<i class="fas fa-tag"></i>', 
                'position'  => 3,
                'parent_id' => 'content', 
            ];

            $nav['website_cats'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'categories/website' ), 
                'label'     => t( 'View categories' ), 
                'position'  => 1,
                'parent_id' => 'ws_categories', 
                'attrs'     => [ 'data-to' => 'categories', 'data-options' => [ 'type' => 'website' ] ]
            ];

            $nav['ws_addcategory'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Add category' ), 
                'position'  => 2, 
                'parent_id' => 'ws_categories', 
                'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'add-category', 'type' => 'website' ] ]
            ];

            $nav['ws_pages'] = [ 
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Pages' ), 
                'icon'      => '<i class="fas fa-scroll"></i>', 
                'position'  => 4,
                'parent_id' => 'content'
            ];

            $nav['website_view'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'pages/website' ), 
                'label'     => t( 'View pages' ), 
                'position'  => 1, 
                'parent_id' => 'ws_pages', 
                'attrs'     => [ 'data-to' => 'pages', 'data-options' => [ 'type' => 'website' ] ]
            ];

            $nav['website_add'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'page/new' ), 
                'label'     => t( 'Add page' ), 
                'position'  => 2, 
                'parent_id' => 'ws_pages', 
                'attrs'     => [ 'data-to' => 'page', 'data-options' => [ 'id' => 'new', 'type' => 'website' ] ]
            ];

            $nav['people'] = [ 
                'type'      => 'label', 
                'label'     => t( 'People' ), 
                'position'  => 4,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['users'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'users' ), 
                'label'     => t( 'Users' ), 
                'icon'      => '<i class="fas fa-user-friends"></i>', 
                'position'  => 1,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'users' ]
            ];

            $kyc_intents_c  = !empty( $owner_vars['kyc_intents'] ) ? (int) $owner_vars['kyc_intents'] : '';

            $nav['users_kyc'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'kyc' ), 
                'label'     => t( 'KYC Verification' ), 
                'after'     => '<span class="a1">' . $kyc_intents_c . '</span>',
                'icon'      => '<i class="fas fa-user-check"></i>', 
                'position'  => 2, 
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'kyc' ],
                'list_attr' => [ 'data-pr-count' => $kyc_intents_c ]
            ];

            $nav['nuser'] = [ 
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'New user' ), 
                'icon'      => '<i class="fas fa-user-plus"></i>', 
                'position'  => 3,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-popup' => 'manage-new', 'data-options' => [ 'action' => 'add-user' ] ]
            ];

            $nav['teams'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'teams' ), 
                'label'     => t( 'Teams' ), 
                'icon'      => '<i class="fas fa-users-cog"></i>', 
                'position'  => 4,
                'parent_id' => 'people', 
                'attrs'     => [ 'data-to' => 'teams' ]
            ];

            $nav['transactions_label'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Transactions' ), 
                'position'  => 5,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['invoicing'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Invoicing' ), 
                'icon'      => '<i class="fas fa-money-bill-wave"></i>', 
                'position'  => 1,
                'parent_id' => 'transactions_label',
            ];

            $nav['invoices'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'invoices' ), 
                'label'     => t( 'Invoices' ), 
                'position'  => 1,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'invoices' ]
            ];

            $nav['receipts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'receipts' ), 
                'label'     => t( 'Receipts' ), 
                'position'  => 2,
                'parent_id' => 'invoicing',
                'attrs'     => [ 'data-to' => 'receipts' ]
            ];

            $prequests_c    = !empty( $owner_vars['payout_requests'] ) ? (int) $owner_vars['payout_requests'] : '';

            $nav['payouts'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'payouts' . ( $prequests_c ? '/status/1' : '' ) ), 
                'label'     => t( 'Payouts' ), 
                'after'     => '<span class="a1">' . $prequests_c . '</span>',
                'icon'      => '<i class="fas fa-comment-dollar"></i>', 
                'position'  => 2,
                'parent_id' => 'transactions_label', 
                'attrs'     => ( $prequests_c ? [ 'data-to' => 'payouts', 'data-options' => [ 'status' => 1 ] ] : [ 'data-to' => 'payouts' ] )
            ];

            $nav['transactions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'transactions' ), 
                'label'     => t( 'Transactions' ), 
                'icon'      => '<i class="fas fa-search-dollar"></i>', 
                'position'  => 3,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'transactions' ]
            ];

            $nav['subscriptions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'icon'      => '<i class="fas fa-arrows-alt-h"></i>', 
                'position'  => 2,
                'parent_id' => 'transactions_label', 
                'attrs'     => [ 'data-to' => 'subscriptions' ]
            ];

            $nav['reportings'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'reportings' ), 
                'label'     => t( 'Reportings' ), 
                'icon'      => '<i class="fas fa-chart-line"></i>', 
                'position'  => 4,
                'parent_id' => 'transactions_label'
            ];

            $nav['r_surveys'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/surveys' ), 
                'label'     => t( 'Surveys' ), 
                'position'  => 1, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'surveys' ] ]
            ];

            $nav['r_responses'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/responses' ), 
                'label'     => t( 'Responses' ), 
                'position'  => 2, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'responses' ] ]
            ];

            $nav['r_users'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/users' ), 
                'label'     => t( 'Users' ), 
                'position'  => 3, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'users' ] ]
            ];

            $nav['r_commissions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/commissions' ), 
                'label'     => t( 'Commissions' ), 
                'position'  => 4, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'commissions' ] ]
            ];

            $nav['r_w_commissions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/wcommissions' ), 
                'label'     => t( 'Website commissions' ), 
                'position'  => 5, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'wcommissions' ] ]
            ];

            $nav['r_deposits'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/deposits' ), 
                'label'     => t( 'Deposits' ), 
                'position'  => 6, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'deposits' ] ]
            ];

            $nav['r_subscriptions'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'reportings/subscriptions' ), 
                'label'     => t( 'Subscriptions' ), 
                'position'  => 7, 
                'parent_id' => 'reportings', 
                'attrs'     => [ 'data-to' => 'reportings', 'data-options' => [ 'dir' => 'subscriptions' ] ]
            ];

            $nav['logs_actions'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Logs & actions' ), 
                'position'  => 5,
                'min'       => true,
                'parent_id' => false, 
            ];

            $nav['actions'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'actions' ), 
                'label'     => t( 'Latest actions' ), 
                'icon'      => '<i class="fas fa-rss"></i>', 
                'position'  => 1,
                'parent_id' => 'logs_actions', 
                'attrs'     => [ 'data-to' => 'actions' ]
            ];

            $nav['clean_data'] = [ 
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Clear data' ), 
                'icon'      => '<i class="fas fa-trash-alt"></i>', 
                'position'  => 2,
                'parent_id' => 'logs_actions', 
                'attrs'     => [ 'data-popup' => 'manage-website', 'data-options' => [ 'action' => 'clean' ] ]
            ];

            $nav['settings'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Settings' ), 
                'icon'      => '<i class="fas fa-cog"></i>', 
                'position'  => 6, 
                'parent_id' => false,
            ];

            $nav['edit_profile'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'settings-general' ), 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['change_p'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Change password' ), 
                'position'  => 2, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'change-password' ] ]
            ];

            $nav['preferences'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Preferences' ), 
                'position'  => 3, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'preferences' ] ]
            ];

            $nav['payout_opts'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Payout options' ), 
                'position'  => 4, 
                'parent_id' => 'settings',
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'payout-options' ] ]
            ];

            $nav['privacy'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Privacy options' ), 
                'position'  => 5, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'privacy-options' ] ]
            ];

            $nav['security'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Security options' ), 
                'position'  => 6, 
                'parent_id' => 'settings', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'security-options' ] ]
            ];

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'owner_nav', [] );
    }

    private function head_nav_respondent() {
        filters()->add_filter( 'respondent_head_nav', function() {
            $nav = [
                'profile' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => esc_html( me()->getDisplayName() ), 
                    'icon'      => me()->getAvatarMarkup( 160 ),
                    'position'  => 1,
                    'attrs'     => [ 'class' => 'sav sav3' ],
                    'parent_id' => false,
                ],

                'editp' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Edit profile' ), 
                    'position'  => 1,
                    'parent_id' => 'profile', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
                ]
            ];

            $nav['logout'] = [
                'type'      => 'link', 
                'url'       => '#',
                'label'     => t( 'Sign out' ), 
                'position'  => 2,
                'parent_id' => 'profile', 
                'attrs'     => [ 'data-popup' => 'logout' ]
            ];

            $nav['balance'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => me()->getBalanceF(), 
                'position'  => 2,
                'parent_id' => false
            ];

            $nav['withdraw'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'withdraw' ), 
                'label'     => t( 'Withdraw' ), 
                'position'  => 2,
                'parent_id' => 'balance', 
                'attrs'     => [ 'data-popup' => 'withdraw' ]
            ];

            $perms = me()->getPermsArray( true );

            if( !me()->isSurveyor() ) {
                $nav['bsurv'] = [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Create surveys' ), 
                    'position'  => 2,
                    'parent_id' => 'profile', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'become-surveyor' ] ]
                ];
            }

            if( count( $perms ) > 1 ) {
                $nav['switch'] = [
                    'type'      => 'label', 
                    'label'     => t( 'Switch to' ), 
                    'position'  => 2, 
                    'parent_id' => 'profile' 
                ];
                foreach( $perms as $key => $name ) {
                    $nav['perm_' . $key] = [
                        'type'      => 'link', 
                        'url'       => admin_url( 'index?viewAs=' . $key ), 
                        'label'     => $name, 
                        'position'  => 3,
                        'parent_id' => 'profile'
                    ];

                    if( me()->viewAsId == $key ) {
                        $nav['perm_' . $key]['icon'] = '<i class="fas fa-check"></i>';
                    }
                }
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'respondent_head_nav', [] );
    }

    private function head_nav_surveyor() {
        filters()->add_filter( 'surveyor_head_nav', function( $f, $nav ) {
            $nav['profile'] = [
                'type'      => 'link', 
                'url'       => '#',
                'label'     => esc_html( me()->getDisplayName() ), 
                'icon'      => me()->getAvatarMarkup( 160 ),  
                'position'  => 1,
                'attrs'     => [ 'class' => 'sav sav3' ],
                'parent_id' => false
            ];

            $nav['editp'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Edit profile' ), 
                'position'  => 1,
                'parent_id' => 'profile', 
                'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
            ];

            $nav['logout'] = [
                'type'      => 'link', 
                'url'       => '#',
                'label'     => t( 'Sign out' ), 
                'position'  => 2,
                'parent_id' => 'profile', 
                'attrs'     => [ 'data-popup' => 'logout' ]
            ];

            $nav['balance'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => me()->getBalanceF(), 
                'position'  => 2,
                'parent_id' => false,
            ];

            $nav['deposit'] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => t( 'Deposit' ), 
                'position'  => 2,
                'parent_id' => 'balance', 
                'attrs'     => [ 'data-popup' => 'deposit' ]
            ];

            $perms = me()->getPermsArray( true );

            if( count( $perms ) > 1 ) {
                $nav['switch'] = [
                    'type'      => 'label', 
                    'label'     => t( 'Switch to' ), 
                    'position'  => 2, 
                    'parent_id' => 'profile' 
                ];
                foreach( $perms as $key => $name ) {
                    $nav['perm_' . $key] = [
                        'type'      => 'link', 
                        'url'       => admin_url( 'index?viewAs=' . $key ), 
                        'label'     => $name, 
                        'position'  => 3,
                        'parent_id' => 'profile'
                    ];

                    if( me()->viewAsId == $key ) {
                        $nav['perm_' . $key]['icon'] = '<i class="fas fa-check"></i>';
                    }
                }
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'surveyor_head_nav', [] );
    }

    private function head_nav_moderator() {
        filters()->add_filter( 'moderator_head_nav', function() {
            $nav = [
                'profile' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => esc_html( me()->getDisplayName() ), 
                    'icon'      => me()->getAvatarMarkup( 160 ),  
                    'position'  => 1,
                    'attrs'     => [ 'class' => 'sav sav3' ],
                    'parent_id' => false,
                ],

                'editp' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Edit profile' ), 
                    'position'  => 1,
                    'parent_id' => 'profile', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
                ],

                'logout' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Sign out' ), 
                    'position'  => 3,
                    'parent_id' => false, 
                    'attrs'     => [ 'data-popup' => 'logout' ]
                ]
            ];

            $perms = me()->getPermsArray( true );

            if( count( $perms ) > 1 ) {
                $nav['switch'] = [
                    'type'      => 'label', 
                    'label'     => t( 'Switch to' ), 
                    'position'  => 2, 
                    'parent_id' => 'profile' 
                ];
                foreach( $perms as $key => $name ) {
                    $nav['perm_' . $key] = [
                        'type'      => 'link', 
                        'url'       => admin_url( 'index?viewAs=' . $key ), 
                        'label'     => $name, 
                        'position'  => 3,
                        'parent_id' => 'profile'
                    ];

                    if( me()->viewAsId == $key ) {
                        $nav['perm_' . $key]['icon'] = '<i class="fas fa-check"></i>';
                    }
                }
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'moderator_head_nav', [] );
    }

    private function head_nav_admin() {
        filters()->add_filter( 'admin_head_nav', function() {
            $nav = [
                'profile' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => esc_html( me()->getDisplayName() ), 
                    'icon'      => me()->getAvatarMarkup( 160 ),  
                    'position'  => 1,
                    'attrs'     => [ 'class' => 'sav sav3' ],
                    'parent_id' => false,
                ],

                'editp' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Edit profile' ), 
                    'position'  => 1,
                    'parent_id' => 'profile', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
                ],

                'logout' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Sign out' ), 
                    'position'  => 3,
                    'parent_id' => false, 
                    'attrs'     => [ 'data-popup' => 'logout' ]
                ]
            ];

            $perms = me()->getPermsArray( true );

            if( count( $perms ) > 1 ) {
                $nav['switch'] = [
                    'type'      => 'label', 
                    'label'     => t( 'Switch to' ), 
                    'position'  => 2, 
                    'parent_id' => 'profile' 
                ];
                foreach( $perms as $key => $name ) {
                    $nav['perm_' . $key] = [
                        'type'      => 'link', 
                        'url'       => admin_url( 'index?viewAs=' . $key ), 
                        'label'     => $name, 
                        'position'  => 3,
                        'parent_id' => 'profile'
                    ];

                    if( me()->viewAsId == $key ) {
                        $nav['perm_' . $key]['icon'] = '<i class="fas fa-check"></i>';
                    }
                }
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'admin_head_nav', [] );
    }

    private function head_nav_owner() {
        filters()->add_filter( 'owner_head_nav', function() {
            $nav = [
                'profile' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => esc_html( me()->getDisplayName() ), 
                    'icon'      => me()->getAvatarMarkup( 160 ), 
                    'position'  => 1,
                    'attrs'     => [ 'class' => 'sav sav3' ],
                    'parent_id' => false,
                ],

                'editp' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Edit profile' ), 
                    'position'  => 1,
                    'parent_id' => 'profile', 
                    'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'edit-profile' ] ]
                ],

                'logout' => [
                    'type'      => 'link', 
                    'url'       => '#', 
                    'label'     => t( 'Logout' ), 
                    'position'  => 3,
                    'parent_id' => false, 
                    'attrs'     => [ 'data-popup' => 'logout' ]
                ]
            ];

            $perms = me()->getPermsArray( true );

            if( count( $perms ) > 1 ) {
                $nav['switch'] = [
                    'type'      => 'label', 
                    'label'     => t( 'Switch to' ), 
                    'position'  => 2, 
                    'parent_id' => 'profile' 
                ];
                foreach( $perms as $key => $name ) {
                    $nav['perm_' . $key] = [
                        'type'      => 'link', 
                        'url'       => admin_url( 'index?viewAs=' . $key ), 
                        'label'     => $name, 
                        'position'  => 3,
                        'parent_id' => 'profile'
                    ];

                    if( me()->viewAsId == $key ) {
                        $nav['perm_' . $key]['icon'] = '<i class="fas fa-check"></i>';
                    }
                }
            }

            return $nav;
        }, 10 );

        $this->nav = filters()->do_filter( 'owner_head_nav', [] );
    }

    private function respondent_head_left() {
        $this->nav = filters()->do_filter( 'respondent_head_left_nav', [] );
    }

    private function surveyor_head_left() {
        $this->nav = filters()->do_filter( 'surveyor_head_left_nav', [] );
    }

    private function moderator_head_left() {
        $this->nav = filters()->do_filter( 'moderator_head_left_nav', [] );
    }

    private function admin_head_left() {
        $this->nav = filters()->do_filter( 'admin_head_left_nav', [] );
    }

    private function owner_head_left() {
        $this->nav = filters()->do_filter( 'owner_head_left_nav', [] );
    }

    private function nav_survey() {
        filters()->add_filter( 'survey_nav', function( $f, $nav ) {
            $nav['dashboard'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'surveys' ), 
                'label'     => t( 'My surveys' ), 
                'icon'      => '<i class="fas fa-chevron-left"></i>', 
                'position'  => 1,
                'parent_id' => false,
                'attrs'     => [ 'data-to' => 'surveys' ]
            ];

            return $nav;
        } );

        $this->nav = filters()->do_filter( 'survey_nav', [] );
    }

}
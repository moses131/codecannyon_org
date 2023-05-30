<?php

namespace admin\markup;

class dashboard {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        if( is_callable( [ $this, 'dashboard_' . me()->viewAs ] ) )
        $this->{ 'dashboard_' . me()->viewAs }();
    }

    private function dashboard_respondent() {
        $markup = '
        <div class="df oa t1 fp">';
        $markup .= '
        <div class="dfc pra5">
            <div class="df mwel mb40">
                <h3 class="wb mb0">' . sprintf( t( 'Welcome <strong>%s</strong>' ), esc_html( me()->getDisplayName() ) ) . ',</h3>';
                $markup .= '
            </div>';

            $boxes  = new \admin\markup\stats_box;

            $boxes
            ->add( 'commissions_today',         t( 'Earnings today' ),      '', 'fas fa-comment-dollar',    true, false )
            ->add( 'commissions_this_month',    t( 'Earnings this month' ), '', 'fas fa-comment-dollar',    true, false )
            ->add( 'loyalty_points',            t( 'Loyalty stars' ),       '', 'fas fa-star',              true, false );
    
            $uqid = 'boxes_' . uniqid();
            $markup .= $boxes->markup( $uqid . ' mb60' );
    
            $this->callbacks[] = '{
                "callback": "cms_populate_boxes",
                "class": "' . $uqid . '"
            }';

            $markup .= '
            <div class="table t2 dfc h100 mb0 wa2 oa ns">
            <div class="w100p">
        
            <div class="tr">
                <div class="w100p">
                    <h3 class="mb0">' . t( 'Last activity' ) . '</h3>
                </div>
            </div>';

            $laMarkup   = '';
            $lrReader   = new \markup\back_end\latest_results_respondent;
            $newest     = new \query\survey\results_with_survey;
            $newest     ->setUserId( me()->getId() );
            foreach( $newest->fetch( 30 ) as $latest_news ) {
                $result = $lrReader->getMarkup( (array) $latest_news );
                if( $result ) {
                    $laMarkup .= '<div class="td"><div class="atext">' . $result . '</div></div>';
                }
            }
            $markup .= $laMarkup;
            if( $laMarkup == '' )
            $markup .= '<div class="msg info2 mb0">' . t( 'Nothing yet' ) . '</div>';
        $markup .= '
        </div>
        </div>';
        $markup .= '</div> 

        <div class="table t2 dfc mb0 wa pra5 ns">
        <div class="oa w100p">';

        $todos = [];

        if( !me()->hasProfileCompleted() ) {
            $todos['incomplete_profile'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Your profile is incomplete, please update your profile' ) . '</div>
                    <div>
                        <a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'edit-profile' ] ) . '\'>' . t( 'Edit your profile' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        if( !me()->hasEmailVerified() ) {
            $todos['verify_email'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Please verify your email address' ) . '</div>
                    <div>
                        <a href="' . admin_url( 'verify-email?verify' ) . '" class="btn" target="_blank">' . t( 'Verify your email address' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        if( !me()->isVerified() ) {
            $todos['verify_account'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Please verify your identity. This prevent surveyors from getting fake responses.' ) . '</div>
                    <div>
                        <a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'identity-verification' ] ) . '\'>' . t( 'Verify your identity' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        $respondent_vars    = filters()->do_filter( 'respondent_vars', [] );
        $pending_completion = $respondent_vars['pending_responses'] ?? NULL;
        if( $pending_completion ) {
            $todos['pending_completion'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Hey! You have responses not finished yet' ) . '</div>
                    <div>
                        <a href="' . admin_url( 'my-responses/status/1' ) . '" class="btn" data-to="my-responses"  data-options=\'' . cms_json_encode( [ 'status' => 1 ] ) . '\'>' . t( 'My pending responses' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        $todos = filters()->do_filter( 'respondent_dashboard_todos', $todos );

        if( !empty( $todos ) ) {
            $markup .= '
            <div class="tr">
                <div class="w100p"><h3 class="mb0">' . t( 'To-dos' ) . '</h3></div>
            </div>';

            $markup .= implode( "\n", $todos );
        }

        $markup .= '
        <div class="tr">
            <div class="w100p"><h3 class="mb0">' . t( 'Alerts' ) . '</h3></div>
        </div>';

        $aMarkup = '';
        $aReader = new \markup\back_end\alerts;
        foreach( me()->getAlerts()->fetch( 30 ) as $alert ) {
            if( !( $alertContent = $aReader->readAlert( $alert->text ) ) ) continue;
            $aMarkup .= '
            <div class="td">
                <div class="atext">' . $alertContent . '</div>
            </div>';
        }
        $markup .= $aMarkup;
        if( $aMarkup == '' )
        $markup .= '<div class="msg info2 mb0">' . t( 'No alerts' ) . '</div>';

        $markup .= '
        </div>
        </div>';
        
        $this->markup = $markup;
    }

    private function dashboard_surveyor() {
        $markup = '
        <div class="df oa t1 fp">';
        $markup .= '
        <div class="dfc pra5">
            <div class="df mwel">
                <h3 class="wb mb0">' . sprintf( t( 'Welcome <strong>%s</strong>' ), esc_html( me()->getDisplayName() ) ) . ',</h3>';
            $markup .= '
            </div>
            <div class="df mb60">
                <div class="txt2">
                    ' . sprintf( t( 'Your surveys <strong>%s</strong> <span>/ %s</span>' ), me()->ownSurveys(), ( me()->limits()->surveys() > -1 ? me()->limits()->surveys() : t( 'unlimited' ) ) ) . '
                </div>
                <div class="tx">
                    <a href="#" data-popup="add-survey" class="btn"> ' . t( 'New survey' ) . '</a>
                </div>
            </div>';

            $boxes  = new \admin\markup\stats_box;

            $boxes
            ->add( 'responses_today',       t( 'Responses today' ),         '', 'fas fa-pencil-alt',    true, false )
            ->add( 'responses_yesterday',   t( 'Responses yesterday' ),     '', 'fas fa-pencil-alt',    true, false )
            ->add( 'responses_this_month',  t( 'Responses this month' ),    '', 'fas fa-pencil-alt',    true, false );
    
            $uqid = 'boxes_' . uniqid();
            $markup .= $boxes->markup( $uqid . ' mb60' );
    
            $this->callbacks[] = '{
                "callback": "cms_populate_boxes",
                "class": "' . $uqid . '"
            }';

            $markup .= '
            <div class="table t2 dfc h100 mb0 wa2 oa ns">
            <div class="w100p">
        
            <div class="tr">
                <div class="w100p">
                    <h3 class="mb0">' . t( 'Last activity' ) . '</h3>
                </div>
            </div>';

            $laMarkup   = '';
            $lrReader   = new \markup\back_end\latest_results;
            $newest     = new \query\survey\latest_results;
            $newest     ->setUserId( me()->getId() )
                        ->setLast7Days();
            foreach( $newest->fetch( 30 ) as $latest_news ) {
                $result = $lrReader->getMarkup( (array) $latest_news );
                if( $result ) {
                    $laMarkup .= '<div class="td"><div class="atext">' . $result . '</div></div>';
                }
            }
            $markup .= $laMarkup;
            if( $laMarkup == '' ) 
            $markup .= '<div class="msg info2 mb0">' . t( 'Nothing yet' ) . '</div>';
        $markup .= '
        </div>
        </div>';
        $markup .= '</div> 

        <div class="table t2 dfc mb0 wa pra5 ns">
        <div class="oa w100p">';

        $todos = [];

        if( !me()->hasProfileCompleted() ) {
            $todos['incomplete_profile'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Your profile is incomplete, please update your profile' ) . '</div>
                    <div>
                        <a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'edit-profile' ] ) . '\'>' . t( 'Edit your profile' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        if( !me()->myLimits()->isFree() ) {
            $expires = strtotime( me()->limits()->expiration() );
            // expires in less than 5 days
            if( $expires < time() ) {
                $todos['plan_expires_low_balance'] = '
                <div class="td">
                    <div class="atext">
                        <div>' . t( 'Your plan has expired, please extend your subscription as fast as possible or you will lose all plan benefits' ) . '</div>
                        <div><a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'my-subscription' ] ) . '\'>' . t( 'View your plan' ) . '</a></div>
                    </div>
                </div>';

            // expires in less than 5 days & auto-renew is deactivated or low balance
            } else if( ( $expires - 432000 ) < time() && ( !me()->myLimits()->autorenew() || me()->getBalance() < me()->myLimits()->getPrice() ) ) {
                $todos['plan_expires_low_balance'] = '
                <div class="td">
                    <div class="atext">
                        <div>' . t( 'Your plan exires soon' ) . '</div>
                        <div><a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'my-subscription' ] ) . '\'>' . t( 'View your plan' ) . '</a></div>
                    </div>
                </div>';
            }
        }

        $todos = filters()->do_filter( 'surveyor_dashboard_todos', $todos );

        if( !empty( $todos ) ) {
            $markup .= '
            <div class="tr">
                <div class="w100p"><h3 class="mb0">' . t( 'To-dos' ) . '</h3></div>
            </div>';

            $markup .= implode( "\n", $todos );
        }

        $markup .= '
        <div class="tr">
            <div class="w100p"><h3 class="mb0">' . t( 'Alerts' ) . '</h3></div>
        </div>';

        $aMarkup = '';
        $aReader = new \markup\back_end\alerts;
        foreach( me()->getAlerts()->fetch( 30 ) as $alert ) {
            if( !( $alertContent = $aReader->readAlert( $alert->text ) ) ) continue;
            $aMarkup .= '
            <div class="td">
                <div class="atext">' . $alertContent . '</div>
            </div>';
        }
        $markup .= $aMarkup;
        if( $aMarkup == '' )
        $markup .= '<div class="msg info2 mb0">' . t( 'No alerts' ) . '</div>';

        $markup .= '
        </div>
        </div>';

        $this->markup = $markup;
    }

    private function dashboard_moderator() {
        $markup = '
        <div class="df oa t1 fp">';
        $markup .= '
        <div class="dfc pra5">
            <div class="df mwel mb40">
                <h3 class="wb mb0">' . sprintf( t( 'Welcome <strong>%s</strong>' ), esc_html( me()->getDisplayName() ) ) . ',</h3>
            </div>

            <div class="table t2 dfc h100 mb0 wa2 oa ns">
            <div class="w100p">
        
            <div class="tr">
                <div class="w100p">
                    <h3 class="mb0">' . t( 'Last activity' ) . '</h3>
                </div>
            </div>';

            $laMarkup   = '';
            $lrReader   = new \markup\back_end\latest_results;
            $newest     = new \query\survey\latest_results;
            $newest     ->setUserId( me()->getId() )
                        ->setLast7Days();
            foreach( $newest->fetch( 30 ) as $latest_news ) {
                $result = $lrReader->getMarkup( (array) $latest_news );
                if( $result ) {
                    $laMarkup .= '<div class="td"><div class="atext">' . $result . '</div></div>';
                }
            }
            $markup .= $laMarkup;
            if( $laMarkup == '' )
            $markup .= '<div class="msg info2 mb0">' . t( 'Nothing yet' ) . '</div>';
        $markup .= '
        </div>
        </div>';
        $markup .= '</div> 

        <div class="table t2 dfc mb0 wa pra5 ns">
        <div class="oa w100p">';

        $todos = [];

        if( !me()->hasProfileCompleted() ) {
            $todos['incomplete_profile'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Your profile is incomplete, please update your profile' ) . '</div>
                    <div>
                        <a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'edit-profile' ] ) . '\'>' . t( 'Edit your profile' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        if( !me()->hasEmailVerified() ) {
            $todos['verify_email'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Please verify your email address' ) . '</div>
                    <div>
                        <a href="' . admin_url( 'verify-email?verify' ) . '" class="btn" target="_blank">' . t( 'Verify your email address' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        if( !me()->isVerified() ) {
            $todos['verify_account'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Please verify your identity. This prevent surveyors from getting fake responses.' ) . '</div>
                    <div>
                        <a href="#" class="btn" data-popup="user-options"  data-options=\'' . cms_json_encode( [ 'action' => 'identity-verification' ] ) . '\'>' . t( 'Verify your identity' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        $respondent_vars    = filters()->do_filter( 'respondent_vars', [] );
        $pending_completion = $respondent_vars['pending_responses'] ?? NULL;
        if( $pending_completion ) {
            $todos['pending_completion'] = '
            <div class="td">
                <div class="atext">
                    <div>' . t( 'Hey! You have responses not finished yet' ) . '</div>
                    <div>
                        <a href="' . admin_url( 'my-responses/status/1' ) . '" class="btn" data-to="my-responses"  data-options=\'' . cms_json_encode( [ 'status' => 1 ] ) . '\'>' . t( 'My pending responses' ) . '</a>
                    </div>
                </div>
            </div>';
        }

        $todos = filters()->do_filter( 'respondent_dashboard_todos', $todos );

        if( !empty( $todos ) ) {
            $markup .= '
            <div class="tr">
                <div class="w100p"><h3 class="mb0">' . t( 'To-dos' ) . '</h3></div>
            </div>';

            $markup .= implode( "\n", $todos );
        }

        $markup .= '
        <div class="tr">
            <div class="w100p"><h3 class="mb0">' . t( 'Alerts' ) . '</h3></div>
        </div>';

        $aMarkup = '';
        $aReader = new \markup\back_end\alerts;
        foreach( me()->getAlerts()->fetch( 30 ) as $alert ) {
            if( !( $alertContent = $aReader->readAlert( $alert->text ) ) ) continue;
            $aMarkup .= '
            <div class="td">
                <div class="atext">' . $alertContent . '</div>
            </div>';
        }
        $markup .= $aMarkup;
        if( $aMarkup == '' )
        $markup .= '<div class="msg info2 mb0">' . t( 'No alerts' ) . '</div>';

        $markup .= '
        </div>
        </div>';
        
        $this->markup = $markup;
    }

    private function dashboard_admin() {
        $markup = '
        <div class="df oa t1 fp">';
        $markup .= '
        <div class="dfc">
            <div class="df mb40">
                <h3 class="wb mb0">' . sprintf( t( 'Welcome <strong>%s</strong>' ), esc_html( me()->getDisplayName() ) ) . ',</h3>
            </div>';

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'subscriptions_today',       t( 'Subscriptions today' ), '', 'fas fa-calendar-alt',      true, false )
        ->add( 'commissions_today',         t( 'Commissions today' ),   '', 'fas fa-comment-dollar',    true, false )
        ->add( 'deposits_today',            t( 'Deposits today' ),      '', 'fas fa-wallet',            true, false );

        $uqid = 'boxes_' . uniqid();
        $markup .= $boxes->markup( $uqid );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "class": "' . $uqid . '"
        }';
    
        $markup .= '
            <div class="mta hm">
                <div class="w100p">' . sprintf( t( 'Your time: %s, system: <strong>%s</strong>' ), custom_time()[0], user_time()->toServerTime()->format() ) . ', ' . sprintf( t( 'Version: <strong>%s</strong>' ), SCRIPT_VERSION ) . '</div>
            </div>
        </div>

        <div class="table t2 dfc mb0 wa ns pra5 mb0">
        <div class="oa w100p">

        <div class="tr">
            <div class="w100p"><h3 class="mb0">' . t( 'Alerts' ) . '</h3></div>
        </div>';

        $aMarkup = '';
        $aReader = new \markup\back_end\alerts;
        foreach( me()->getAlerts()->fetch( 30 ) as $alert ) {
            if( !( $alertContent = $aReader->readAlert( $alert->text ) ) ) continue;
            $aMarkup .= '
            <div class="td">
                <div class="atext">' . $alertContent . '</div>
            </div>';
        }
        $markup .= $aMarkup;

        if( $aMarkup == '' )
        $markup .= '<div class="msg info2">' . t( 'No alerts') . '</div>';

        $markup .= '
        </div>
        </div>';

        $this->markup = $markup;
    }

    private function dashboard_owner() {
        $markup = '
        <div class="df oa t1 fp">';
        $markup .= '
        <div class="dfc">
            <div class="df mb40">
                <h3 class="wb mb0">' . sprintf( t( 'Welcome <strong>%s</strong>' ), esc_html( me()->getDisplayName() ) ) . ',</h3>
            </div>';

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'subscriptions_today',       t( 'Subscriptions today' ), '', 'fas fa-calendar-alt',      true, false )
        ->add( 'commissions_today',         t( 'Commissions today' ),   '', 'fas fa-comment-dollar',    true, false )
        ->add( 'deposits_today',            t( 'Deposits today' ),      '', 'fas fa-wallet',            true, false );

        $uqid = 'boxes_' . uniqid();
        $markup .= $boxes->markup( $uqid );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "class": "' . $uqid . '"
        }';
    
        $markup .= '
            <div class="mta hm">
                <div class="w100p">' . sprintf( t( 'Your time: %s, system: <strong>%s</strong>' ), custom_time()[0], user_time()->toServerTime()->format() ) . ', ' . sprintf( t( 'Version: <strong>%s</strong>' ), SCRIPT_VERSION ) . '</div>
            </div>
        </div>

        <div class="table t2 dfc mb0 wa ns pra5 mb0">
        <div class="oa w100p">

        <div class="tr">
            <div class="w100p"><h3 class="mb0">' . t( 'Alerts' ) . '</h3></div>
        </div>';

        $aMarkup = '';
        $aReader = new \markup\back_end\alerts;
        foreach( me()->getAlerts()->fetch( 30 ) as $alert ) {
            if( !( $alertContent = $aReader->readAlert( $alert->text ) ) ) continue;
            $aMarkup .= '
            <div class="td">
                <div class="atext">' . $alertContent . '</div>
            </div>';
        }
        $markup .= $aMarkup;

        if( $aMarkup == '' )
        $markup .= '<div class="msg info2">' . t( 'No alerts') . '</div>';

        $markup .= '
        </div>
        </div>';

        $this->markup = $markup;
    }

    public function markup() {
        return $this->markup;
    }

    public function callbacks() {
        return $this->callbacks;
    }

    public function result( array $result ) {
        return $result + $this->result;
    }
    
}
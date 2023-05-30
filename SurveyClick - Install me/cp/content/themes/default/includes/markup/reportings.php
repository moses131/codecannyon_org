<?php

namespace admin\markup;

class reportings {

    private $markup;
    private $callbacks;
    private $options;
    private $result = [];

    function __construct() {
        $this->options = $_POST['options'] ?? [];

        if( !isset( $this->options['dir'] ) ) {
            if( is_callable( [ $this, 'reportings_' . me()->viewAs ] ) )
            $this->{ 'reportings_' . me()->viewAs }();
        } else if( is_callable( [ $this, 'reportings_' . $this->options['dir']] ) ) {
            $this->{ 'reportings_' . $this->options['dir'] }();
        }
    }

    private function reportings_respondent() {
        $boxes = new \admin\markup\stats_box;
        $boxes
        ->title( t( 'Earnings' ) )
        ->add( 'commissions_today',         t( 'Today' ),       '', 'fas fa-comment-dollar cl1',   true, false )
        ->add( 'commissions_yesterday',     t( 'Yesterday' ),   '', 'fas fa-comment-dollar cl2',   true, false )
        ->add( 'commissions_this_week',     t( 'This week' ),   '', 'fas fa-comment-dollar cl3',   true, false )
        ->add( 'commissions_last_week',     t( 'Last week' ),   '', 'fas fa-comment-dollar cl4',   true, false )
        ->add( 'commissions_this_month',    t( 'This month' ),  '', 'fas fa-comment-dollar cl5',   true, false )
        ->add( 'commissions_last_month',    t( 'Last month' ),  '', 'fas fa-comment-dollar cl6',   true, false )
        ->title( t( 'Responses' ) )
        ->add( 'responses_today',           t( 'Today' ),       '', 'fas fa-poll-h cl1',    true, false )
        ->add( 'responses_yesterday',       t( 'Yesterday' ),   '', 'fas fa-poll-h cl2',    true, false )
        ->add( 'responses_this_week',       t( 'This week' ),   '', 'fas fa-poll-h cl3',    true, false )
        ->add( 'responses_last_week',       t( 'Last week' ),   '', 'fas fa-poll-h cl4',    true, false )
        ->add( 'responses_this_month',      t( 'This month' ),  '', 'fas fa-poll-h cl5',    true, false )
        ->add( 'responses_last_month',      t( 'Last month' ),  '', 'fas fa-poll-h cl6',    true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ), 'classes' => 'wa' ]
        ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form sales_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_earnings",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'reportings';
    }

    private function reportings_surveyor() {
        $this->markup .= '<h2>' . t( 'Responses' ) . '</h2>';

        $boxes = new \admin\markup\stats_box;

        $boxes
        ->add( 'responses_today_ms',        t( 'Today' ),           '', 'fas fa-pencil-alt cl1',   true, false )
        ->add( 'responses_yesterday_ms',    t( 'Yesterday' ),       '', 'fas fa-pencil-alt cl2',   true, false )
        ->add( 'responses_this_week_ms',    t( 'This week' ),       '', 'fas fa-pencil-alt cl3',   true, false )
        ->add( 'responses_last_week_ms',    t( 'Last week' ),       '', 'fas fa-pencil-alt cl4',   true, false )
        ->add( 'responses_this_month_ms',   t( 'This month' ),      '', 'fas fa-pencil-alt cl5',   true, false )
        ->add( 'responses_last_month_ms',   t( 'Last month' ),      '', 'fas fa-pencil-alt cl6',   true, false )
        ->title( t( 'Commissions paid' ) )
        ->add( 'commissions_today_ms',      t( 'Today' ),           '', 'fas fa-comment-dollar cl7',    true, false )
        ->add( 'commissions_yesterday_ms',  t( 'Yesterday' ),       '', 'fas fa-comment-dollar cl8',    true, false )
        ->add( 'commissions_this_week_ms',  t( 'This week' ),       '', 'fas fa-comment-dollar cl9',    true, false )
        ->add( 'commissions_last_week_ms',  t( 'Last week' ),       '', 'fas fa-comment-dollar cl10',   true, false )
        ->add( 'commissions_this_month_ms', t( 'This month' ),      '', 'fas fa-comment-dollar cl11',   true, false )
        ->add( 'commissions_last_month_ms', t( 'Last month' ),      '', 'fas fa-comment-dollar cl11',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form sales_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_surveyor",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'reportings';
    }

    private function reportings_users() {
        $this->markup .= '<h2>' . t( 'Users' ) . '</h2>';

        $boxes = new \admin\markup\stats_box;

        $boxes
        ->add( 'users_today',           t( 'Today' ),           '', 'fas fa-user-friends cl1',   true, false )
        ->add( 'users_yesterday',       t( 'Yesterday' ),       '', 'fas fa-user-friends cl2',   true, false )
        ->add( 'users_this_week',       t( 'This week' ),       '', 'fas fa-user-friends cl3',   true, false )
        ->add( 'users_last_week',       t( 'Last week' ),       '', 'fas fa-user-friends cl4',   true, false )
        ->add( 'users_this_month',      t( 'This month' ),      '', 'fas fa-user-friends cl5',   true, false )
        ->add( 'users_last_month',      t( 'Last month' ),      '', 'fas fa-user-friends cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form sales_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_users",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_users';
    }

    private function reportings_surveys() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Surveys' );

        if( !empty( $this->options['category'] ) ) {
            $categories = categories( (int) $this->options['category'] );
            if( $categories->getObject() ) {
                $this->markup   .= '<div class="mt15">' . esc_html( $categories->getName() ) . '</div>';
                $curr_category  = $categories->getId();
            }
        }

        $this->markup .= '</h2>';

        $opt    = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'surveys_today',           t( 'Today' ),           '', 'fas fa-poll-h cl1',   true, false )
        ->add( 'surveys_yesterday',       t( 'Yesterday' ),       '', 'fas fa-poll-h cl2',   true, false )
        ->add( 'surveys_this_week',       t( 'This week' ),       '', 'fas fa-poll-h cl3',   true, false )
        ->add( 'surveys_last_week',       t( 'Last week' ),       '', 'fas fa-poll-h cl4',   true, false )
        ->add( 'surveys_this_month',      t( 'This month' ),      '', 'fas fa-poll-h cl5',   true, false )
        ->add( 'surveys_last_month',      t( 'Last month' ),      '', 'fas fa-poll-h cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_surveys",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_surveys';
    }

    private function reportings_responses() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Responses' ) . '</h2>';

        $opt    = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'responses_today',       t( 'Today' ),           '', 'fas fa-pencil-alt cl1',   true, false )
        ->add( 'responses_yesterday',   t( 'Yesterday' ),       '', 'fas fa-pencil-alt cl2',   true, false )
        ->add( 'responses_this_week',   t( 'This week' ),       '', 'fas fa-pencil-alt cl3',   true, false )
        ->add( 'responses_last_week',   t( 'Last week' ),       '', 'fas fa-pencil-alt cl4',   true, false )
        ->add( 'responses_this_month',  t( 'This month' ),      '', 'fas fa-pencil-alt cl5',   true, false )
        ->add( 'responses_last_month',  t( 'Last month' ),      '', 'fas fa-pencil-alt cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_responses",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_responses';
    }

    private function reportings_commissions() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Commissions' ) . '</h2>';

        $opt    = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'commissions_today',       t( 'Today' ),           '', 'fas fa-comment-dollar cl1',   true, false )
        ->add( 'commissions_yesterday',   t( 'Yesterday' ),       '', 'fas fa-comment-dollar cl2',   true, false )
        ->add( 'commissions_this_week',   t( 'This week' ),       '', 'fas fa-comment-dollar cl3',   true, false )
        ->add( 'commissions_last_week',   t( 'Last week' ),       '', 'fas fa-comment-dollar cl4',   true, false )
        ->add( 'commissions_this_month',  t( 'This month' ),      '', 'fas fa-comment-dollar cl5',   true, false )
        ->add( 'commissions_last_month',  t( 'Last month' ),      '', 'fas fa-comment-dollar cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_commissions",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_commissions';
    }

    private function reportings_wcommissions() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Website commissions' ) . '</h2>';

        $opt    = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'wcommissions_today',        t( 'Today' ),           '', 'fas fa-comment-dollar cl1',   true, false )
        ->add( 'wcommissions_yesterday',    t( 'Yesterday' ),       '', 'fas fa-comment-dollar cl2',   true, false )
        ->add( 'wcommissions_this_week',    t( 'This week' ),       '', 'fas fa-comment-dollar cl3',   true, false )
        ->add( 'wcommissions_last_week',    t( 'Last week' ),       '', 'fas fa-comment-dollar cl4',   true, false )
        ->add( 'wcommissions_this_month',   t( 'This month' ),      '', 'fas fa-comment-dollar cl5',   true, false )
        ->add( 'wcommissions_last_month',   t( 'Last month' ),      '', 'fas fa-comment-dollar cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_wcommissions",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_w_commissions';
    }

    private function reportings_deposits() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Deposits' ) . '</h2>';

        $opt    = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'deposits_today',        t( 'Today' ),           '', 'fas fa-wallet cl1',   true, false )
        ->add( 'deposits_yesterday',    t( 'Yesterday' ),       '', 'fas fa-wallet cl2',   true, false )
        ->add( 'deposits_this_week',    t( 'This week' ),       '', 'fas fa-wallet cl3',   true, false )
        ->add( 'deposits_last_week',    t( 'Last week' ),       '', 'fas fa-wallet cl4',   true, false )
        ->add( 'deposits_this_month',   t( 'This month' ),      '', 'fas fa-wallet cl5',   true, false )
        ->add( 'deposits_last_month',   t( 'Last month' ),      '', 'fas fa-wallet cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_deposits",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_deposits';
    }

    private function reportings_subscriptions() {
        if( !me()->isAdmin() ) return ;

        $this->markup .= '<h2>' . t( 'Subscriptions' ) . '</h2>';

        $opt = \util\etc::buildFilterOptions();

        $boxes  = new \admin\markup\stats_box;
        $boxes
        ->add( 'subscriptions_today',        t( 'Today' ),           '', 'fas fa-calendar-alt cl1',   true, false )
        ->add( 'subscriptions_yesterday',    t( 'Yesterday' ),       '', 'fas fa-calendar-alt cl2',   true, false )
        ->add( 'subscriptions_this_week',    t( 'This week' ),       '', 'fas fa-calendar-alt cl3',   true, false )
        ->add( 'subscriptions_last_week',    t( 'Last week' ),       '', 'fas fa-calendar-alt cl4',   true, false )
        ->add( 'subscriptions_this_month',   t( 'This month' ),      '', 'fas fa-calendar-alt cl5',   true, false )
        ->add( 'subscriptions_last_month',   t( 'Last month' ),      '', 'fas fa-calendar-alt cl6',   true, false );

        $uqid = 'boxes_' . uniqid();
        $this->markup .= $boxes->markup( $uqid . ' mb40' );

        $this->callbacks[] = '{
            "callback": "cms_populate_boxes",
            "options": "' . $opt . '",
            "class": "' . $uqid . '"
        }';

        $this->markup .= '<div class="df mb40">';

        $form = new \markup\front_end\form_fields( [
            'year'  => [ 'type' => 'select', 'options' => array_combine( ( $ry = range( date( 'Y', strtotime( me()->getDate() ) ), date( 'Y' ) ) ), $ry ), 'value' => date( 'Y' ) ],
            'month' => [ 'type' => 'select', 'options' => \util\etc::monthsList(), 'value' => date( 'm' ) ]
        ] );

        if( isset( $curr_category ) )
        $form->addFields( [ 'category' => [ 'type' => 'hidden', 'value' => $curr_category ] ] );

        $uqid   = 'boxes_' . uniqid();
        $fields = $form->build();
        $this->markup .= '<form id="' . $uqid . '" class="form list_form reportings_list_form"' . $form->formAttributes() . '>';
        $this->markup .= $fields;
        $this->markup .= '</form>';

        $this->markup .= '</div>';

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" style="width:100%;height:500px;"></div>';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "populate_chart",
            "chart": "reportings_subscriptions",
            "options": "' . $opt . '",
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link'] = 'r_subscriptions';
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
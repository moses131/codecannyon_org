<?php

namespace admin\markup;

class survey {

    private $options;
    private $markup;
    private $callbacks  = [];
    private $result     = [];
    private $isOwner    = false;

    function __construct( string $type = '' ) {
        $this->options  = $_POST['options'] ?? [];
        $id             = $this->options['id'] ?? NULL;

        if( !$id ) 
        return ;

        if( !me()->manageSurvey( 'view', $id ) ) 
        return ;

        $this->isOwner = me()->manageSurvey( 'delete-survey' );

        $location = $this->options['action'] ?? 'main';

        switch( $location ) {
            case 'report':          $path = 'report'; break;
            case 'reports':         $path = 'reports'; break;
            case 'responses':       $path = 'responses'; break;
            case 'label_responses': $path = 'label_responses'; break;
            case 'reportings':      $path = 'reportings'; break;
            default:                $path = 'main';
        }

        $this->{ 'survey_' . $path }();
    }

    private function survey_main() {
        // Survey variable
        $survey = me()->getSelectedSurvey();

        // Set menu
        $this   ->setMenu();

        $markup = '
        <ul class="brc">
            <li><a href="' . admin_url( 'surveys' ) . '" data-to="surveys">' . t( 'Surveys' ) . '</a></li>';
            if( ( $category = $survey->getCategory() ) && $category->getObject() )
            $markup .= '<li><a href="#" data-to="surveys" data-options=\'' . cms_json_encode( [ 'category' => $category->getId() ] ) . '\'>' . esc_html( $category->getName() ) . '</a></li>';
        $markup .= '
        </ul>
        <div class="df mb40">
            <h3 class="mb0">
                <strong>' . esc_html( $survey->getName() ) . '</strong>
            </h3>
        </div>';

        $markup .= '
        <div class="df survey_dashboard oa t1 fp pra5">';
        $markup .= '
        <div class="table t2 report wa oa dfc ns mb0 report-ph">';
        if( ( $view_results = me()->manageSurvey( 'view-result' ) ) ) {
            $markup .= $this->resultForm( $survey, 1, true );
        }
        $markup .= '
        </div>

        <div class="table t2 dashboard wa oa dfc ns mb0 pra5">
        <div class="dfc fp oa w100p">';

        if( $view_results ) {
            $markup .= '
            <div class="tr">
                <h3 class="df w100p mb0">
                    <span>' . t( 'Dashboard' ) . '</span>
                    <span class="mla">
                        <a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'customize', 'survey' => $survey->getId() ] ) . '\'>
                            <i class="fas fa-tasks"></i>
                        </a>
                    </span>
                </h3>
            </div>';

            $dashboard  = $survey->dashboardMarkup();
            if( !empty( $dashboard->items ) ) {
                foreach( $dashboard->items as $item ) {
                    if( !$item ) continue;
                    $markup .= '<div class="td">';
                    $markup .= $item;
                    $markup .= '</div>';
                }
                $this->result[ 'load_scripts' ] = [ 'https://www.gstatic.com/charts/loader.js' => '{
                    "callback": "init_survey_chart2",
                    "container": ".table.dashboard",
                    "placeholders": ' . cms_json_encode( $dashboard->getPlaceholders() ) . ',
                    "data": ' . cms_json_encode( $dashboard->getData() ) . '
                }' ];

                if( $dashboard->getCallbacks() )
                $this->result = array_merge( $this->callbacks, $dashboard->getCallbacks() );

                if( $dashboard->modifyResult() )
                $this->result = array_merge_recursive( $this->result, $dashboard->modifyResult() );
            } else {
                $markup .= '
                <div class="cta_box">
                    <div>
                        <div class="tc">
                            <div class="slet"><i class="fas fa-tasks"></i></div>
                            <h3>' . t( 'Customize your dashboard' ) . '</h3>
                            <div class="mb40">
                                <a href="#" class="btn" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'customize', 'survey' => $survey->getId() ] ) . '\'>' . t( 'Customize' ) . '</a>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }

        $markup .= '
        </div>
        </div>';

        // Init dashboard
        $this->callbacks[] = '{
            "callback": "init_survey_dashboard",
            "survey": "' . $survey->getId() . '"
        }';

        $this->result['menu_link']      = 'home';
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();
        $this->markup = $markup;
    }

    private function survey_report() {
        $reportId= $this->options['report'];
        // Survey variable
        $survey  = me()->getSelectedSurvey();
        $smarkup = $survey->reportMarkup( (int) $reportId );

        if( !$smarkup ) return cms_json_encode( [ 'content' => '' ] );

        // Set menu
        $this   ->setMenu();

        $markup = '
        <ul class="brc">
            <li><a href="' . admin_url( 'surveys' ) . '" data-to="surveys">' . t( 'Surveys' ) . '</a></li>';
            if( ( $category = $survey->getCategory() ) && $category->getObject() )
            $markup .= '<li><a href="#" data-to="surveys" data-options=\'' . cms_json_encode( [ 'category' => $category->getId() ] ) . '\'>' . esc_html( $category->getName() ) . '</a></li>';
        $markup .= '
        </ul>
        <div class="df mb40">
            <h3 class="mb0">
                <strong>' . esc_html( $survey->getName() ) . '</strong>
            </h3>
        </div>';

        $markup .= '
        <div class="df survey_dashboard oa t1 fp pra5">';
        $markup .= '
        <div class="table t2 report oa dfc ns mb0 report-' . $smarkup->getId() . '">';

        $markup .= '
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

        $markup .= '
        </div>

        <div class="table t2 dashboard oa dfc ns mb0 pra5 report-ph">
        ' . $this->resultForm( $survey, 2, true );

        $markup .= '
        </div>
        </div>';

        // Init dashboard
        $this->callbacks[] = '{
            "callback": "init_survey_dashboard",
            "survey": "' . $survey->getId() . '"
        }';

        $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
            "callback": "init_survey_chart2",
            "container": ".table.report-' . $smarkup->getId() . '",
            "placeholders": ' . cms_json_encode( $smarkup->getPlaceholders() ) . ',
            "data": ' . cms_json_encode( $smarkup->getData() ) . '
        }' ];

        $this->markup = $markup;
    }

    private function survey_reports() {
        // Survey variable
        $survey = me()->getSelectedSurvey();

        // Set menu
        $this   ->setMenu();

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Survey' ), 'before_label' => '<i class="fas fa-poll-h"></i>', 'fields' => [
                'id' => [ 'type' => 'radio', 'options' => array_map( function( $v ) {
                    return esc_html( $v->name );
                }, me()->getSurveys()->fetch( -1 ) ) ]
            ], 'grouped' => false ] ], 'grouped' => false ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ],
            'action'    => [ 'type' => 'hidden' ]
        ] );

        if( isset( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup = '<div class="filters">';
        $markup .= '<form id="reports_list" class="form list_form options_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        // Build the table
        $surveys = new \admin\markup\table( [
            t( 'Name' ) => 'tl w150p',
            t( 'Date' ) => 'tar',
            ''          => '' 
        ] );
        
        $surveys
        ->title( t( 'Saved reports' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{date}', 'tar' )
        ->add( '{options}', 'df' )
        ->save( 'template' );

        $uqid   = 'table_' . uniqid();
        $markup .= $surveys->markup( $uqid );

        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "survey_reports",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';

        $this->result['menu_link']      = 'generate';
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();
        $this->markup   = $markup;
    }

    private function survey_responses() {
        // Survey variable
        $survey = me()->getSelectedSurvey();

        // Check permission
        if( !me()->manageSurvey( 'view-result' ) )
        return ;

        // Set menu
        $this   ->setMenu();

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-on"></i>', 'options' => [ '' => t( 'Any' ), 3 => t( 'Finished' ), 2 => t( 'Finished & awaiting approval' ), 1 => t( 'In progress' ), 0 => t( 'Rejected' ) ], 'value' => '', 'placeholder' => t( 'Status' ) ],
            [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Report' ), 'before_label' => '<i class="fas fa-table"></i>', 'fields' => [
                'report' => [ 'type' => 'radio', 'options' => ( [ '' => t( 'Any' ) ] + array_map( function( $v ) {
                    return esc_html( $v->title );
                }, $survey->reports()->fetch( -1 ) ) ), 'value' => '' ]
            ], 'grouped' => false ] ], 'grouped' => false ],
            [ 'type' => 'custom', 'callback' => '
            <a href="#" data-before="cms_set_form_values" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'results-filter', 'survey' => $survey->getId() ] ) . '\'>
                <i class="fas fa-tasks"></i>
                <span>' . t( 'Advanced' ) . '</span>
                <i class="fas fa-expand"></i>
            </a>', 'classes' => 'ab2', 'when' => [ 'EMPTY', 'data[report]' ] ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ],
            'action'    => [ 'type' => 'hidden' ],
            'id'        => [ 'type' => 'hidden', 'value' => $survey->getId() ],
            'advanced'  => [ 'type' => 'hidden' ]
        ] );

        if( isset( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup = '<div class="filters">';
        $markup .= '<form id="responses_list" class="form list_form options_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        // Build the table
        $responses = new \admin\markup\table( [
            t( 'Name' )     => 'tl w150p',
            t( 'Country' )  => '',
            t( 'Duration' ) => 'tar',
            t( 'Date' )     => 'tar',
            ''              => '' 
        ] );
        
        $responses
        ->title( t( 'Responses' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{country}', 'ico' )
        ->add( '{duration}', 'tar' )
        ->add( '{date}', 'tar' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $responses->markup( $uqid );

        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "survey_responses",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';

        $this->result['menu_link']      = ( isset( $_POST['options']['status'] ) && $_POST['options']['status'] == 2 ? 'pending_responses' : 'view_responses' );
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();
        $this->markup = $markup;
    }

    private function survey_label_responses() {
        // Survey variable
        $survey = me()->getSelectedSurvey();

        // Check permission
        if( !me()->manageSurvey( 'view-result' ) ) {
            return ;
        }

        if( !isset( $this->options['label'] ) ) {
            return ;
        }

        // Label
        $labels = new \query\survey\labels;
        $labels ->setId( $this->options['label'] );
        
        if( !$labels->getObject() || $survey->getId() !== $labels->getSurveyId() ) {
            return ;
        }

        // Set menu
        $this   ->setMenu();

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-on"></i>', 'options' => [ '' => t( 'Any' ), 3 => t( 'Finished' ), 2 => t( 'Finished & awaiting approval' ), 1 => t( 'In progress' ), 0 => t( 'Rejected' ) ], 'value' => '', 'placeholder' => t( 'Status' ) ],
            'checked'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-check"></i>', 'options' => [ '' => t( 'Any' ), 1 => t( 'Checked' ), 0 => t( 'Not checked' ) ], 'placeholder' => t( 'Checked' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ],
            'action'    => [ 'type' => 'hidden' ],
            'id'        => [ 'type' => 'hidden', 'value' => $survey->getId() ],
            'label'     => [ 'type' => 'hidden', 'value' => $labels->getId() ],
        ] );

        if( isset( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup = '<div class="filters">';
        $markup .= '<form id="label_responses_list" class="form list_form options_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        // Build the table
        $responses = new \admin\markup\table( [
            t( 'Name' )     => 'tl w150p',
            t( 'Country' )  => '',
            t( 'Duration' ) => 'tar',
            t( 'Date' )     => 'tar',
            ''              => '' 
        ] );
        
        $responses
        ->title( esc_html( $labels->getName() ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{country}', 'ico' )
        ->add( '{duration}', 'tar' )
        ->add( '{date}', 'tar' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $responses->markup( $uqid );

        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "survey_label_responses",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';

        $this->result['menu_link']      = 'label' . $labels->getId();
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();
        $this->markup = $markup;
    }

    private function survey_reportings() {
        $location = $this->options['action2'] ?? '';

        switch( $location ) {
            case 'responses': return $this->reportings_responses(); break;
            case 'geographic': return $this->reportings_geographic(); break;
            case 'statistics': return $this->reportings_statistics(); break;
        }

        return ;
    }

    private function reportings_responses() {
        // Survey variable
        $survey     = me()->getSelectedSurvey();

        $today      = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportToday();
        if( !empty( $today ) ) {
            $today = end( $today );
            $today = [ 'responses' => $today->responses_done, 'commissions' => cms_money_format( $today->sum ) ];
        } else {
            $today = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        $yesterday  = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportYesterday();
        if( !empty( $yesterday ) ) {
            $yesterday = end( $yesterday );
            $yesterday = [ 'responses' => $yesterday->responses_done, 'commissions' => cms_money_format( $yesterday->sum ) ];
        } else {
            $yesterday = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        $thisWeek   = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportThisWeek();
        if( !empty( $thisWeek ) ) {
            $thisWeek = end( $thisWeek );
            $thisWeek = [ 'responses' => $thisWeek->responses_done, 'commissions' => cms_money_format( $thisWeek->sum ) ];
        } else {
            $thisWeek = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        $lastWeek   = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportLastWeek();
        if( !empty( $lastWeek ) ) {
            $lastWeek = end( $lastWeek );
            $lastWeek = [ 'responses' => $lastWeek->responses_done, 'commissions' => cms_money_format( $lastWeek->sum ) ];
        } else {
            $lastWeek = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        $thisMonth  = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportThisMonth();
        if( !empty( $thisMonth ) ) {
            $thisMonth = end( $thisMonth );
            $thisMonth = [ 'responses' => $thisMonth->responses_done, 'commissions' => cms_money_format( $thisMonth->sum ) ];
        } else {
            $thisMonth = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        $lastMonth  = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions()->reportLastMonth();
        if( !empty( $lastMonth ) ) {
            $lastMonth = end( $lastMonth );
            $lastMonth = [ 'responses' => $lastMonth->responses_done, 'commissions' => cms_money_format( $lastMonth->sum ) ];
        } else {
            $lastMonth = [ 'responses' => 0, 'commissions' => cms_money_format( 0 ) ];
        }

        // Set menu
        $this   ->setMenu();
        
        $boxes = new \admin\markup\stats_box;
        $boxes
        ->title( t( 'Responses' ) )
        ->add( '',  t( 'Today' ),        $today['responses'],       'fas fa-pencil-alt cl1' )
        ->add( '',  t( 'Yesterday' ),    $yesterday['responses'],   'fas fa-pencil-alt cl2' )
        ->add( '',  t( 'This week' ),    $thisWeek['responses'],    'fas fa-pencil-alt cl3' )
        ->add( '',  t( 'Last week' ),    $lastWeek['responses'],    'fas fa-pencil-alt cl4' )
        ->add( '',  t( 'This month' ),   $thisMonth['responses'],   'fas fa-pencil-alt cl5' )
        ->add( '',  t( 'Last month' ),   $lastMonth['responses'],   'fas fa-pencil-alt cl6' );
        
        if( $this->isOwner ) {
            $boxes
            ->title( t( 'Commissions paid' ) )
            ->add( '',  t( 'Today' ),        $today['commissions'],     'fas fa-comment-dollar cl1' )
            ->add( '',  t( 'Yesterday' ),    $yesterday['commissions'], 'fas fa-comment-dollar cl2' )
            ->add( '',  t( 'This week' ),    $thisWeek['commissions'],  'fas fa-comment-dollar cl3' )
            ->add( '',  t( 'Last week' ),    $lastWeek['commissions'],  'fas fa-comment-dollar cl4' )
            ->add( '',  t( 'This month' ),   $thisMonth['commissions'], 'fas fa-comment-dollar cl5' )
            ->add( '',  t( 'Last month' ),   $lastMonth['commissions'], 'fas fa-comment-dollar cl6' );
        }

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
            "chart": "reportings_survey",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "data": ' . cms_json_encode( [ 'survey' => $survey->getId() ] ) . ',
            "class": "' . $uqid . '",
            "table": "' . $table . '"
        }' ];

        $this->result['menu_link']      = 'r_responses';
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();
    }

    private function reportings_geographic() {
        // Survey variable
        $survey     = me()->getSelectedSurvey();
        $responses  = $survey->resultsByCountry();
        $responses  ->setStatus( 3 );
        $fResponses = $responses->fetch( -1 );

        // Set menu
        $this   ->setMenu();

        $this->result['menu_link']      = 'r_geo';
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();

        $this->markup .= '
        <div class="df mb40">
            <h3 class="mb0">
                <strong>' . t( 'Geographic' ) . '</strong>
            </h3>
        </div>';

        if( empty( $fResponses ) ) {
            $this->markup .= '<div class="msg info2">' . t( 'No responses yet' ) . '</div>';
            return ;
        }

        $this->markup .= '<div id="' . ( $table = 'table_' . uniqid() ) . '" class="mb40" style="width:100%;height:400px;flex-shrink:0;"></div>';

        // Build the table
        $responsesTable = new \admin\markup\table;
        // Populate the chart
        $data   = [];
        $data[] = [ 'Country code', 'Country name', t( 'Responses' ) ];

        foreach( $fResponses as $response ) {
            $responses->setObject( $response );
            if( $responses->getCountry() ) {
                $data[]         = [ esc_html( $responses->getCountry() ), t( esc_html( $responses->getCountryName() ) ), $responses->getCount() ];
                $responsesTable ->newLine( [ [ '<div class="ico"><img  src="' . site_url( 'assets/flags/' . esc_html( $responses->getCountry() ) . '.svg' ) . '"></div>', 'w100' ], [ t( esc_html( $responses->getCountryName() ) ) ], [ $responses->getCount(), 'wa' ] ] );
            } else
            $responsesTable->newLine( [ [ '', 'w100' ], [ t( 'Unknown' ) ], [ $responses->getCount(), 'wa' ] ] );
        }

        $uqid   = 'table_' . uniqid();
        $this->markup .= $responsesTable->markup( $uqid );

        if( count( $data ) > 1 ) {
            $this->result['load_scripts'] = [ 'https://www.gstatic.com/charts/loader.js' => '{
                "callback": "populate_chart4",
                "data": ' . cms_json_encode( $data ) . ',
                "table": "#' . $table . '"
            }' ];
        }
    }

    private function reportings_statistics() {
        // Survey variable
        $survey = me()->getSelectedSurvey();
        $stats  = $survey->stats()
                ->results();

        // Set menu
        $this   ->setMenu();

        $this->result['menu_link']      = 'statistics';
        $this->result['menu_selected']  = 'survey_nav_' . $survey->getId();

        $this->markup .= '
        <div class="df mb40">
            <h3 class="mb0">
                <strong>' . t( 'Statistics' ) . '</strong>
            </h3>
        </div>';

        if( empty( $stats['total'] ) ) {
            $this->markup .= '<div class="msg info2">' . t( 'No responses yet' ) . '</div>';
            return ;
        }

        $this->markup .= '<div class="df oa t1 fp">';
        $this->markup .= '<div class="table t2 oa dfc mb0">
        <div class="oa w100p">        
            <div class="td"><div>' . t( 'Approved responses' ) . '</div><div class="wa">' . $stats['approved'] . '</div></div>
            <div class="td"><div>' . t( 'Rejected responses' ) . '</div><div class="wa">' . $stats['rejected'] . '</div></div>
            <div class="td"><div>' . t( 'Abandoned responses' ) . '</div><div class="wa">' . $stats['abandoned'] . '</div></div>
            <div class="td"><div>' . t( 'Approval rate' ) . '</div><div class="wa">' . ( $stats['approved'] ? round( ( $stats['approved'] / $stats['submited'] * 100 ), 2 ) : 0 ) . '%</div></div>
            <div class="td"><div>' . t( 'Rejection rate' ) . '</div><div class="wa">' . ( $stats['rejected'] ? round( ( $stats['rejected'] / $stats['submited'] * 100 ), 2 ) : 0 ) . '%</div></div>
            <div class="td"><div>' . t( 'Abandon rate' ) . '</div><div class="wa">' . ( $stats['abandoned'] ? round( ( $stats['abandoned'] / $stats['total'] * 100 ), 2 ) : 0 ) . '%</div></div>
            <div class="td"><div>' . t( 'Average duration' ) . '</div><div class="wa">' . ( $stats['approved'] ? round( $stats['duration'] / $stats['approved'] ) : '-' ) . '</div></div>
        </div></div>';
        $this->markup .= '<div></div>';
        $this->markup .= '</div>';
    }

    public static function survey_print() {
        $markup = '
        <div class="oa w100p">
            <div class="tr">
                <div>
                    <h3 class="edtt">
                        <span contenteditable="true" spellcheck="false" class="reportName">' . t( 'My report' ) . '</span>
                        <a href="#"><i class="fas fa-pencil-alt"></i></a>
                    </h3>
                </div>
            </div>
            <div class="td">
                <div>';
                $markup .= me()->forms()->survey_report( $survey, $position );
                $markup .= '</div>
            </div>
            <div class="td">
                <div>
                    <a href="#" class="viewn">
                        <i class="fas fa-chevron-right"></i>
                        <span>' . t( 'View a saved report' ) . '</span>
                    </a>
                    <div class="mt0">' . me()->forms()->survey_reports( $survey, $position ) . '</div>
                </div>
            </div>
        </div>';

        return $markup;
    }

    public static function resultForm( object $survey, int $pos = 1, bool $isNew = false ) {
        $markup = '
        <div class="oa w100p">
            <div class="tr">
                <div>
                    <h3 class="edtt">
                        <span contenteditable="true" spellcheck="false" class="reportName">' . t( 'My report' ) . '</span>
                        <a href="#"><i class="fas fa-pencil-alt"></i></a>
                    </h3>
                </div>
            </div>
            <div class="td">
                <div>';
                $markup .= me()->forms()->survey_report( $survey, $pos, $isNew );
                $markup .= '</div>
            </div>
            <div class="td">
                <div>
                    <a href="#" class="viewn">
                        <i class="fas fa-chevron-right"></i>
                        <span>' . t( 'View a saved report' ) . '</span>
                    </a>
                    <div class="mt20">' . me()->forms()->survey_reports( $survey ) . '</div>
                </div>
            </div>
        </div>';

        return $markup;
    }

    private function setMenu() {
        // Survey variable
        $survey = me()->getSelectedSurvey();

        // Set current menu
        $this->result['menu'] = 'survey_nav_' .  $survey->getId();

        filters()->add_filter( 'survey_nav', function( $f, $nav ) use ( $survey ) {
            // Manage
            $view_results   = me()->manageSurvey( 'view-result' );
            $add_response   = me()->manageSurvey( 'add-response' );
            $apr_response   = me()->manageSurvey( 'approve-response' );

            $not_appr       = 0;

            if( $apr_response ) {
                $results    = $survey->getResults();
                $results    ->setStatus( 2 );
                $not_appr   = $results->count();
            }

            $nav['view'] = [ 
                'type'      => 'label', 
                'label'     => t( 'View' ), 
                'position'  => 2,
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['home'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'survey/' . $survey->getId() ), 
                'label'     => t( 'Dashboard' ), 
                'icon'      => '<i class="fas fa-home"></i>', 
                'position'  => 1,
                'parent_id' => 'view',
                'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'id' => $survey->getId() ] ]
            ];

            if( $view_results ) {
                $nav['generate'] = [
                    'type'      => 'link', 
                    'url'       => admin_url( 'survey/' . $survey->getId() . '/reports' ), 
                    'label'     => t( 'Saved reports' ), 
                    'icon'      => '<i class="fas fa-table"></i>', 
                    'position'  => 2,
                    'parent_id' => 'view',
                    'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'reports', 'id' => $survey->getId() ] ]
                ];
            }

            if( $view_results || $add_response || ( $apr_response && $not_appr ) ) {
                $nav['responses'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url( 'survey/' . $survey->getId() . '/responses' ), 
                    'label'     => t( 'Responses' ), 
                    'icon'      => '<i class="fas fa-pencil-alt"></i>', 
                    'position'  => 3,
                    'parent_id' => 'view',
                ];
            }

            if( $view_results ) {
                $nav['view_responses'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url( 'survey/' . $survey->getId() . '/responses' ), 
                    'label'     => t( 'View responses' ), 
                    'icon'      => '<i class="fas fa-pencil-alt"></i>', 
                    'position'  => 1,
                    'parent_id' => 'responses',
                    'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'responses', 'id' => $survey->getId() ] ]
                ];
            }

            if( $apr_response && $not_appr ) {
                if( $results ) {
                    $nav['pending_responses'] = [ 
                        'type'      => 'link', 
                        'url'       => admin_url( 'survey/' . $survey->getId() . '/responses/status/2' ), 
                        'label'     => t( 'Pending responses' ), 
                        'after'     => '<span class="a1">' . $not_appr . '</span>',
                        'icon'      => '<i class="fas fa-hourglass-half"></i>', 
                        'position'  => 1.1,
                        'parent_id' => 'responses',
                        'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'responses', 'id' => $survey->getId(), 'status' => 2 ] ]
                    ];
                }
            }
            
            if( $add_response ) {
                $nav['new_response'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'New response' ), 
                    'icon'      => '<i class="fas fa-plus"></i>', 
                    'position'  => 2,
                    'parent_id' => 'responses',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'add-response', 'survey' => $survey->getId() ] ]
                ];
            }

            if( $view_results ) {
                $nav['labels'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Labels' ), 
                    'icon'      => '<i class="fas fa-tags"></i>', 
                    'position'  => 4,
                    'parent_id' => 'view'
                ];

                $labels = $survey->getLabels();

                foreach( $labels->fetch( -1 ) as $label ) {
                    $labels->setObject( $label );
                    $nav['label' . $labels->getId()] = [
                        'type'      => 'link',
                        'url'       => admin_url( 'survey/' . $survey->getId() . '/label_responses/label/' . $labels->getId() ), 
                        'label'     => $labels->getName(),
                        'icon'      => '<i class="avt-' . $labels->getColor() . '"></i>',
                        'position'  => 1,
                        'parent_id' => 'labels',
                        'attrs'     => [ 'class' => 'sav', 'data-to' => 'survey', 'data-options' => [ 'action' => 'label_responses', 'id' => $survey->getId(), 'label' => $labels->getId() ] ]
                    ];
                }

                $nav['new_label'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'New label' ),
                    'icon'      => '<i class="fas fa-plus"></i>', 
                    'position'  => 999,
                    'parent_id' => 'labels',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'add-label', 'survey' => $survey->getId() ] ]
                ];
            }

            $nav['manage'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Manage' ), 
                'position'  => 3, 
                'min'       => true,
                'parent_id' => false 
            ];

            if( me()->manageSurvey( 'manage-question' ) ) {
                $nav['questions'] = [
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Questions' ), 
                    'icon'      => '<i class="fas fa-question"></i>', 
                    'position'  => 1,
                    'parent_id' => 'manage',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'questions', 'survey' => $survey->getId() ] ]
                ];
            }

            if( me()->manageSurvey( 'manage-collector' ) ) {
                $nav['collectors'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Collectors' ), 
                    'icon'      => '<i class="fas fa-link"></i>', 
                    'position'  => 2,
                    'parent_id' => 'manage',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'collectors', 'survey' => $survey->getId() ] ]
                ];
            }

            if( $this->isOwner ) {
                $nav['collaborators'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Collaborators' ), 
                    'icon'      => '<i class="fas fa-users"></i>', 
                    'position'  => 3,
                    'parent_id' => 'manage',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'collaborators', 'survey' => $survey->getId() ] ]
                ];
            }

            $nav['reportings'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Reportings' ), 
                'position'  => 4, 
                'min'       => true,
                'parent_id' => false 
            ];

            $nav['r_responses'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'survey/' . $survey->getId() . '/reportings/responses' ),
                'label'     => t( 'Responses & commissions' ), 
                'icon'      => '<i class="fas fa-chart-line"></i>', 
                'position'  => 1,
                'parent_id' => 'reportings',
                'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'reportings', 'action2' => 'responses', 'id' => $survey->getId() ] ]
            ];

            $nav['r_geo'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'survey/' . $survey->getId() . '/reportings/geographic' ),
                'label'     => t( 'Geographic' ), 
                'icon'      => '<i class="fas fa-flag-usa"></i>', 
                'position'  => 3,
                'parent_id' => 'reportings',
                'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'reportings', 'action2' => 'geographic', 'id' => $survey->getId() ] ]
            ];

            $nav['statistics'] = [ 
                'type'      => 'link', 
                'url'       => admin_url( 'survey/' . $survey->getId() . '/reportings/statistics' ),
                'label'     => t( 'Statistics' ), 
                'icon'      => '<i class="fas fa-chart-bar"></i>', 
                'position'  => 4,
                'parent_id' => 'reportings',
                'attrs'     => [ 'data-to' => 'survey', 'data-options' => [ 'action' => 'reportings', 'action2' => 'statistics', 'id' => $survey->getId() ] ]
            ];

            $nav['edit_settings'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Edit & settings' ), 
                'icon'      => '<i class="fas fa-question"></i>', 
                'position'  => 5,
                'parent_id' => false,
            ];

            if( $this->isOwner ) {
                $nav['edit'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Edit' ), 
                    'position'  => 1,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'edit', 'survey' => $survey->getId() ] ]
                ];
            }

            if( $this->isOwner ) {
                $nav['balance'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Budget' ),
                    'after'     => '<span class="a1">' . $survey->getBudgetF() . '</span>',
                    'position'  => 2,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'budget', 'survey' => $survey->getId() ] ]
                ];
            }

            if( ( $edit_settings = me()->manageSurvey( 'edit-settings' ) ) ) {
                $nav['settings'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Settings' ), 
                    'position'  => 3,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'settings', 'survey' => $survey->getId() ] ]
                ];

                $nav['terms'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Terms of use' ), 
                    'position'  => 4,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'terms-of-use', 'survey' => $survey->getId() ] ]
                ];
            }

            if( $this->isOwner ) {
                $nav['view_labels'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Labels' ), 
                    'position'  => 5,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'labels', 'survey' => $survey->getId() ] ]
                ];
            }

            if( $this->isOwner ) {
                $nav['advanced'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Advanced' ), 
                    'position'  => 6,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'advanced', 'survey' => $survey->getId() ] ]
                ];
            }

            $nav['personalize'] = [ 
                'type'      => 'label', 
                'label'     => t( 'Personalize' ), 
                'position'  => 7,
                'min'       => true,
                'parent_id' => 'edit_settings' 
            ];

            if( $edit_settings ) {
                $nav['logo'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Logo' ), 
                    'position'  => 8,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'logo', 'survey' => $survey->getId() ] ]
                ];

                $nav['meta_tags'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Meta tags' ), 
                    'position'  => 9,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'meta-tags', 'survey' => $survey->getId() ] ]
                ];

                $nav['text'] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => t( 'Texts & messages' ), 
                    'position'  => 9,
                    'parent_id' => 'edit_settings',
                    'attrs'     => [ 'data-popup' => 'manage-survey', 'data-options' => [ 'action' => 'texts', 'survey' => $survey->getId() ] ]
                ];
            }

            return $nav;
        } );
    
        $nav    = new \admin\markup\nav( 'survey' );
        $nav2   = $nav->markup( 'nave nav survey-nav' );

        // Change menu
        $this->callbacks[] = '{
            "callback": "change_menu",
            "el": "' . base64_encode( $nav2 ) . '",
            "nav": "survey_nav_' . $survey->getId() . '"
        }';
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
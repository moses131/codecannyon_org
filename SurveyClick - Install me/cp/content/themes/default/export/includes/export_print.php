<?php

class export_print {

    private $type;
    private $id         = [];
    private $settings   = [];

    public function setType( string $type ) : void {
        if( $type == 'report' || $type == 'response' )
        $this->type = $type;
    }

    public function setId( int $id ) : void {
        if( $id )
        $this->id = [ $id ];
    }

    public function setIds( array $ids ) : void {
        $this->id = $ids;
    }

    public function setSettings( array $settings ) : void {
        $this->settings = $settings;
    }

    public function header() {
        $header = "<!DOCTYPE html>\n<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
        $header .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" />\n";
        $header .= "<title>" . t( 'Print reports' ) . "</title>\n";
        $header .= "<meta property=\"og:title\" content=\"" . t( 'Print reports' ) . "\" />\n";
        $header .= "<meta name=\"robots\" content=\"noindex, nofollow\" />\n";
        $header .= "<link href=\"//fonts.googleapis.com/css?family=Quicksand:500,700\" rel=\"stylesheet\">";
        $header .= "<link href=\"" . admin_url( 'export/assets/css/main.css', true ) . "\" media=\"all\" rel=\"stylesheet\" />\n";
        $header .= "<link href=\"" . assets_url( 'css/fontawesome-all.min.css', true ) . "\" media=\"all\" rel=\"stylesheet\" />\n";
        $header .= "<script src=\"//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js\"></script>\n
        <script src=\"//www.gstatic.com/charts/loader.js\"></script>\n
        <script src=\"" . admin_url( 'export/assets/js/functions.js', true ) . "\"></script>\n";
        $header .= "</head>\n";
        $header .= "<body>\n";
        return $header;
    }

    private function footer() {
        return "\n</body>\n</html>";
    }

    private function getData() {
        if( !$this->type || !me() ) return ;

        switch( $this->type ) {
            case 'report':
                $eType  = $this->settings['export_print'];

                switch( $eType ) {
                    case 'report':
                    case 'compare':
                        $ids        = $eType == 'compare' ? $this->settings['reports'] : $this->id;
                        $report     = NULL;
                        $reports    = [];
                        $qAvailable = survey_types()->getTypesSummary();

                        foreach( $ids as $id ) {
                            $rpts = new \query\survey\saved_reports;
                            $rpts ->setId( $id );
                            if( $rpts->getObject() ) {

                                $export = new \admin\markup\question\print_report;
                                $export ->setResults( $rpts->getResult() );
                                $export ->setReport( $rpts->getid() );
                                $rpts   ->export = $export;

                                if( !$report ) {
                                    $report         = $rpts;
                                    $reports[$id]   = $rpts;
                                } else if( $report->getSurveyId() == $rpts->getSurveyId() )
                                    $reports[$id]   = $rpts;
                            }
                        }

                        $cReports   = count( $reports );
                        $nReports   = $cReports > 1;

                        if( !$report )
                        return ;

                        // Surveys class
                        $survey     = $report->getSurvey();

                        // Return if the survey does not exist
                        if( !$survey->getObject() )
                        return ;

                        // Insufficient permissions to view report

                        // Questions class
                        $questions  = $survey->getQuestions()
                                    ->setVisible( 2 );

                        // Report options
                        $options    = $report->getOptions();

                        $markup = $this->header();
                        $markup .= '<div class="container results-' . count( $reports ) . '">';
                        $markup .= '
                        <div class="title ced isPH">
                            <span spellcheck="false">' . t( 'Title' ) . '</span>
                        </div>';

                        $markup .= '
                        <div class="item isPH">
                            <h2 class="ced">
                                <span spellcheck="false">Options</span>
                            </h2>
                            <div class="itemc">
                                <div class="desc">
                                    <span>' . t( 'When you print this page, empty title, empty descriptions, and options will be hidden' ) . '</span>
                                </div>
                                <div class="p20">
                                    <form class="options">
                                        <div class="igroup">
                                            <label for="chartTypes">' . t( 'Chart type' ) . '</label>
                                            <div>
                                                <select name="chartTypes" id="chartTypes">
                                                    <option value="pie3d">' . t( 'Pie 3D' ) . '</option>
                                                    <option value="pie">' . t( 'Pie' ) . '</option>
                                                    <option value="donut">' . t( 'Donut' ) . '</option>
                                                </select>
                                            </div>
                                        </div>';
                                        $p = 1;
                                        foreach( $reports as $r ) {
                                            $markup .= '
                                            <div class="igroup">
                                                <label class="ced"><span spellcheck="false" data-group="r-' . $r->getId() . '" contenteditable="false">' . esc_html( $r->getTitle() ) . '</span></label>
                                                <div>
                                                    <label for="pos-' . $r->getId() . '">' . t( 'Position' ) . '</label>
                                                    <select name="changePos" data-r="' . $r->getId() . '" id="pos-' . $r->getId() . '">';
                                                    foreach( range( 1, $cReports ) as $pos ) {
                                                        $markup .= '<option value="' . $pos . '"' . ( $pos == $p ? ' selected' : '' ) . '>' . $pos . '</option>';
                                                    }
                                                    $markup .= '
                                                    </select>
                                                    <label for="col-' . $r->getId() . '">' . t( 'Background color' ) . '</label>
                                                    <select name="changeCol" data-r="' . $r->getId() . '" id="col-' . $r->getId() . '">
                                                    <option value="0">' . t( 'Select' ) . '</option>';
                                                    foreach( range( 1, 10 ) as $col ) {
                                                        $markup .= '<option value="' . $col . '">' . sprintf( t( 'Color %s' ), $col ) . '</option>';
                                                    }
                                                    $markup .= '
                                                    </select>
                                                </div>
                                            </div>';
                                            $p++;
                                        }
                                    $markup .= '
                                    </form>
                                </div>
                            </div>';
                        $markup .= '
                        </div>';

                        // export data
                        $placeholders   = [];
                        $data           = [];

                        foreach( $questions->fetch( -1 ) as $question ) {
                            $questions  ->setObject( $question );

                            if( array_search( $questions->getType(), $qAvailable ) === false || 
                            ( !empty( $options['show'] ) && array_search( $questions->getId(), $options['show'] ) === false ) ) continue;

                            $markup     .= '
                            <div class="item">
                            <h2 class="ced">
                                <span spellcheck="false">' . esc_html( $questions->getTitle() ) . '</span>
                            </h2>
                            <div class="itemc">

                            <div class="desc ced isPH">
                                <span spellcheck="false">' . t( 'Description' ) . '</span>
                            </div>

                            <div class="results">';

                            $export = false;

                            foreach( $reports as $report ) {
                                $export = $report->export;
                                $export ->setQuestion( $question );
                                $markup .= '<div class="qc report-' . $report->getId() . '">';
                                if( $nReports )
                                $markup .= '<h3 class="ced"><span spellcheck="false" data-group="r-' . $report->getId() . '">' . esc_html( $report->getTitle() ) . '</span></h3>';
                                $markup .= $export->questionMarkup();
                                $markup .= '</div>';

                                // export data
                                $placeholders[$report->getId()] = $export->getPlaceholders();
                                $data[$report->getId()]         = $export->getData();
                            }

                            $markup .= '</div>';

                            if( $export )
                            $markup .= $export->legend();

                            $markup .= '</div>
                            </div>';
                        }

                        switch( $cReports ) {
                            case 1:     $height = 280; break;
                            case 2:     $height = 240; break;
                            default:    $height = 200; break;
                        }

                        $markup .= '</div>
                        <script>
                        init_survey_chart2( { 
                            "container": ".container",
                            "placeholders": ' . cms_json_encode( $placeholders ) . ',
                            "data": ' . cms_json_encode( $data ) . ',
                            "height": ' . $height . ' } );
                        </script>';

                        $markup     .= $this->footer();
                        return $markup;
                    break;

                    case 'responses':
                        $id     = current( $this->id );
                        $report = new \query\survey\saved_reports;
                        $report ->setId( $id );

                        if( !$report->getObject() ) 
                        return ;

                        // Surveys class
                        $survey = $report->getSurvey();

                        // Return if the survey does not exist
                        if( !$survey->getObject() ) 
                        return ;

                        // Insufficient permissions to view report

                        // Questions class
                        $questions  = $survey->getQuestions()
                        ->markupView( 'print_view' )
                        ->setVisible( 2 );

                        // Results
                        $results    = $report->getResults();
                        $results    ->answerSelect( 'value, value_str' );
    
                        // Countries
                        $countries  = new \query\countries;
                        $countries  = $countries->select( [ 'iso_3166, name' ] )->fetch( -1 );

                        $markup = $this->header();
                        $markup .= '<div class="container r">';

                        $usePoints      = isset( $this->settings['print_also_export']['points'] ) || ( isset( $this->settings['orderby'] ) && ( $this->settings['orderby'] == 'points' || $this->settings['orderby'] == 'points_d' ) );
                        $useRtime       = isset( $this->settings['print_also_export']['rtime'] ) || ( isset( $this->settings['orderby'] ) && ( $this->settings['orderby'] == 'time' || $this->settings['orderby'] == 'time_d' ) );
                        $sortBy         = $this->settings['orderby'] ?? '';
                        $resultsAsArray = [];

                        foreach( $results->fetch( -1 ) as $responseId => $response ) {
                            $results    ->setObject( $response );
                            $response   ->unixDate  = strtotime( $response->date );

                            if( $useRtime )
                            $response   ->duration  = strtotime( $response->fin ) - $response->unixDate;

                            if( $usePoints )
                            $response   ->points    = $results->getAnswer( 5, $responseId )->value ?? 0;

                            if( !empty( $response->country ) && isset( $countries[$response->country] ) )
                            $response   ->countryN  = esc_html( $countries[$response->country]->name );

                            if( isset( $this->settings['print_also_export']['tid'] ) )
                            $response   ->tIds      = $results->getAnswer( 8, $responseId );

                            if( isset( $this->settings['print_also_export']['vars'] ) )
                            $response   ->vars      = $results->getAnswer( 9, $responseId );

                            if( isset( $this->settings['print_also_export']['labels'] ) )
                            $response   ->labels    = array_map( function( $v ) {
                                return esc_html( $v->name );
                            }, $results->getLabels()->fetch( -1 ) );

                            if( isset( $this->settings['print_also_export']['date'] ) )
                            $response   ->dateF     = custom_time( $response->date, 2 );

                            $resultsAsArray[]       = $response;
                        }

                        switch( $sortBy ) {
                            case 'points':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->points === (double) $b->points ) return 0;
                                    return ( (double) $a->points < (double) $b->points ? -1 : 1 );
                                } );
                            break;

                            case 'points_d':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->points === (double) $b->points ) return 0;
                                    return ( (double) $a->points > (double) $b->points ? -1 : 1 );
                                } );
                            break;

                            case 'time':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->duration === (double) $b->duration ) return 0;
                                    return ( (double) $a->duration > (double) $b->duration ? -1 : 1 );
                                } );
                            break;

                            case 'time_d':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->duration === (double) $b->duration ) return 0;
                                    return ( (double) $a->duration < (double) $b->duration ? -1 : 1 );
                                } );
                            break;

                            case 'date':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->unixDate === (double) $b->unixDate ) return 0;
                                    return ( (double) $a->unixDate < (double) $b->unixDate ? -1 : 1 );
                                } );
                            break;

                            case 'date_d':
                                uasort( $resultsAsArray, function( $a, $b ) {
                                    if( (double) $a->unixDate === (double) $b->unixDate ) return 0;
                                    return ( (double) $a->unixDate > (double) $b->unixDate ? -1 : 1 );
                                } );
                            break;
                        }

                        foreach( $resultsAsArray as $response ) {
                            $results->setObject( $response );

                            $the_res    = $results->getResults();
                            $the_steps  = $the_res['st'] ?? [];

                            $markup     .= '
                            <div class="title2 ced">
                                <span spellcheck="false">' . sprintf( t( 'Response #%s' ), $results->getId() ) . '</span>
                            </div>';

                            $feats      = [];
                            if( isset( $response->points ) )
                            $feats[]    = '<li><span>' . t( 'Points' ) . ':</span>' . $response->points . '</li>';
                            if( isset( $response->duration ) )
                            $feats[]    = '<li><span>' . t( 'Duration' ) . ':</span>' . $results->getDuration() . '</li>';
                            if( !empty( $response->labels ) )
                            $feats[]    = '<li><span>' . t( 'Labels' ) . ':</span>' . implode( ', ', $response->labels ) . '</li>';
                            if( isset( $response->countryN ) )
                            $feats[]    = '<li><span>' . t( 'Country' ) . ':</span>' . t( $response->countryN ) . '</li>';
                            if( isset( $response->dateF ) )
                            $feats[]    = '<li><span>' . t( 'Date' ) . ':</span>' . t( $response->dateF ) . '</li>';
                            $markup     .= '
                            <ul class="feat">' . implode( "\n", $feats ) . '</ul>
                            <div class="desc ced isPH">
                                <span spellcheck="false">' . t( 'Description' ) . '</span>
                            </div>';

                            foreach( $the_steps as $id => $values ) {
                                $steps  = steps( $id );
                                if( count( $the_steps ) != 1 ) {
                                    if( !$steps->getObject() || $steps->getSurveyId() != $survey->getId() )
                                    continue;
                                    $markup .= '<h2 class="step"><span>' . esc_html( $steps->getName() ) . '</span></h2>';
                                }
                                $questions  ->setStepId( $id );
                                foreach( $questions->fetch( -1 ) as $question ) {
                                    $questions  ->setObject( $question );
                                    $markup     .= $questions->markup( ( $values['vl'][$questions->getId()] ?? [] ), isset( $this->settings['print_settings']['emptyq'] ) );
                                }
                            }

                            if( isset( $response->tIds ) && $response->tIds ) {
                                if( isset( $response->tIds->value_str ) )
                                $response->tIds = [ $response->tIds ];
                                $markup .= '
                                <div class="item">
                                <h2 class="ced">
                                    <span spellcheck="false">' . t( 'Tracking Id' ) . '</span>
                                </h2>
                                <div class="itemc">

                                <div class="answer vars">';
                                foreach( $response->tIds as $var ) {
                                    $varVal = json_decode( $var->value_str, true );
                                    if( is_array( $varVal ) ) {
                                        $markup .= '<div>
                                            <span>' . key( $varVal ) . ':</span>
                                            <span>' . current( $varVal ) . '</span>
                                        </div>';
                                    }
                                }
                                $markup .= '</div>';

                                $markup .= '
                                </div>
                                </div>';
                            }

                            if( isset( $response->vars ) && $response->vars ) {
                                if( isset( $response->vars->value_str ) )
                                $response->vars = [ $response->vars ];

                                $markup .= '
                                <div class="item">
                                <h2 class="ced">
                                    <span spellcheck="false">' . t( 'Variables' ) . '</span>
                                </h2>
                                <div class="itemc">

                                <div class="answer vars">';
                                foreach( $response->vars as $var ) {
                                    $varVal = json_decode( $var->value_str, true );
                                    if( is_array( $varVal ) ) {
                                        $markup .= '<div>
                                            <span>' . key( $varVal ) . ':</span>
                                            <span>' . current( $varVal ) . '</span>
                                        </div>';
                                    }
                                }
                                $markup .= '</div>';

                                $markup .= '
                                </div>
                                </div>';
                            }
                        }

                        $markup     .= $this->footer();
                        return $markup;
                    break;
                }
            break;

            case 'response':
                // Response id
                $id  = current( $this->id );

                // Results class
                $results = results( (int) $id );

                // Return if the result does not exist
                if( !$results->getObject() )
                return ;

                // Surveys class
                $survey = $results->getSurvey();

                // Return if the survey does not exist
                if( !$survey->getObject() )
                return ;

                // Insufficient permissions to view report

                // Questions class
                $questions  = $survey->getQuestions()
                ->markupView( 'print_view' )
                ->setVisible( 2 );

                // Countries
                $countries  = new \query\countries;
                $countries  = $countries->select( [ 'iso_3166, name' ] )->fetch( -1 );

                $markup = $this->header();
                $markup .= '<div class="container r">';

                $the_res    = $results->getResults();
                $the_steps  = $the_res['st'] ?? [];

                $markup     .= '
                <div class="title2 ced">
                    <span spellcheck="false">' . sprintf( t( 'Response #%s' ), $results->getId() ) . '</span>
                </div>';

                $feats      = [];
                if( isset( $this->settings['print_also_export']['points'] ) )
                $feats[]    = '<li><span>' . t( 'Points' ) . ':</span>' . ( $results->getAnswer( 5 )->value ?? 0 ) . '</li>';

                if( isset( $this->settings['print_also_export']['rtime'] ) )
                $feats[]    = '<li><span>' . t( 'Duration' ) . ':</span>' . $results->getDuration() . '</li>';

                if( !empty( $this->settings['print_also_export']['labels'] ) ) {
                    $labels     = array_map( function( $v ) {
                        return esc_html( $v->name );
                    }, $results->getLabels()->fetch( -1 ) );
                    if( !empty( $labels ) )
                    $feats[]    = '<li><span>' . t( 'Labels' ) . ':</span>' . implode( ', ', $labels ) . '</li>';
                }

                if( isset( $this->settings['print_also_export']['country'] ) && isset( $countries[$results->getCountry()] ) )
                $feats[]    = '<li><span>' . t( 'Country' ) . ':</span>' . t( esc_html( $countries[$results->getCountry()]->name ) ) . '</li>';

                if( isset( $this->settings['print_also_export']['date'] ) )
                $feats[]    = '<li><span>' . t( 'Date' ) . ':</span>' . custom_time( $results->getDate(), 2 ) . '</li>';

                $markup .= '
                <ul class="feat">' . implode( "\n", $feats ) . '</ul>';

                $markup .= '
                <div class="desc ced isPH">
                    <span spellcheck="false">' . t( 'Description' ) . '</span>
                </div>';

                foreach( $the_steps as $id => $values ) {
                    $steps  = steps( $id );
                    if( count( $the_steps ) != 1 ) {
                        if( !$steps->getObject() || $steps->getSurveyId() != $survey->getId() )
                        continue;
                        $markup .= '<h2 class="step"><span>' . esc_html( $steps->getName() ) . '</span></h2>';
                    }
                    $questions  ->setStepId( $id );
                    foreach( $questions->fetch( -1 ) as $question ) {
                        $questions  ->setObject( $question );
                        $markup     .= $questions->markup( ( $values['vl'][$questions->getId()] ?? [] ), isset( $this->settings['print_settings']['emptyq'] ) );
                    }
                }

                if( isset( $this->settings['print_also_export']['tid'] ) && ( $tIds = $results->getAnswer( 8 ) ) ) {
                    if( isset( $tIds->value_str ) )
                    $tIds = [ $tIds ];

                    $markup .= '
                    <div class="item">
                    <h2 class="ced">
                        <span spellcheck="false">' . t( 'Tracking Id' ) . '</span>
                    </h2>
                    <div class="itemc">

                    <div class="answer vars">';
                    foreach( $tIds as $var ) {
                        $varVal = json_decode( $var->value_str, true );
                        if( is_array( $varVal ) ) {
                            $markup .= '<div>
                                <span>' . key( $varVal ) . ':</span>
                                <span>' . current( $varVal ) . '</span>
                            </div>';
                        }
                    }
                    $markup .= '</div>';

                    $markup .= '
                    </div>
                    </div>';
                }

                if( isset( $this->settings['print_also_export']['vars'] ) && ( $vars = $results->getAnswer( 9 ) ) ) {
                    if( isset( $vars->value_str ) )
                    $vars = [ $vars ];

                    $markup .= '
                    <div class="item">
                    <h2 class="ced">
                        <span spellcheck="false">' . t( 'Variables' ) . '</span>
                    </h2>
                    <div class="itemc">

                    <div class="answer vars">';
                    foreach( $vars as $var ) {
                        $varVal = json_decode( $var->value_str, true );
                        if( is_array( $varVal ) ) {
                            $markup .= '<div>
                                <span>' . key( $varVal ) . ':</span>
                                <span>' . current( $varVal ) . '</span>
                            </div>';
                        }
                    }
                    $markup .= '</div>';

                    $markup .= '
                    </div>
                    </div>';
                }

                $markup     .= $this->footer();
                return $markup;
            break;
        }

    }

    public function export() {
        return $this->getData();
    }

}
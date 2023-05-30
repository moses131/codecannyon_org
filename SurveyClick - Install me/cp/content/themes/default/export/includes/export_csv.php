<?php

class export_csv {

    private $type;
    private $id;
    private $settings = [];

    public function setType( string $type ) : void {
        if( $type == 'report' || $type == 'response' )
        $this->type = $type;
    }

    public function setId( int $id ) : void {
        if( $id )
        $this->id = $id;
    }
    public function setSettings( array $settings ) : void {
        $this->settings = $settings;
    }

    private function getData() {
        if( !$this->type || !$this->id || !me() ) return ;

        switch( $this->type ) {
            case 'report':
                // Saved reports class
                $report = new \query\survey\saved_reports;
                $report ->setId( $this->id );

                // Return if the report does not exist
                if( !$report->getObject() ) return ;

                // Surveys class
                $survey = $report->getSurvey();

                // Return if the survey does not exist
                if( !$survey->getObject() ) return ;
 
                // Insufficient permissions to view report

                $eType  = $this->settings['export_csv'];

                switch( $eType ) {
                    case 'report':
                        header( 'Content-Encoding: UTF-8' );
                        header( 'Content-Type: text/csv; charset=UTF-8');
                        header( 'Content-Disposition: attachment; filename=report.csv' );
                        
                        $questions  = $survey->getQuestions()
                                    ->setVisible( 2 );
                        $export     = new \admin\markup\question\export_report;
                        $export     ->setResults( $report->getResult() );
                        $out        = fopen( 'php://output', 'w' );

                        foreach( $questions->fetch( -1 ) as $question ) {
                            $questions  ->setObject( $question );
                            $export     ->setQuestion( $questions );
                            if( $export->hasHead() ) {
                                fputcsv( $out, $export->head() );
                                fputcsv( $out, $export->result() );
                            }
                        }

                        fclose( $out );

                        die;
                    break;

                    case 'responses':
                        header( 'Content-Encoding: UTF-8' );
                        header( 'Content-Type: text/csv; charset=UTF-8');
                        header( 'Content-Disposition: attachment; filename=responses.csv' );

                        $steps      = $survey->getSteps();
                        $stepsList  = $steps->fetch( -1 );
                        $questions  = $survey->getQuestions()
                                    ->setVisible( 2 );

                        foreach( $questions->fetch( -1 ) as $q => $question ) {
                            if( isset( $stepsList[$question->step] ) )
                            $stepsList[$question->step]->questions[$q] = $question;
                        }

                        $head       = [];
                        $countries  = [];
                        $labels     = [];

                        foreach( $stepsList as $step ) {
                            $steps  ->setObject( $step );
                            $head[] = '#' . $steps->getName();
                            foreach( $step->questions as $question ) {
                                $questions  ->setObject( $question );
                                $head[]     = $questions->getTitle();
                            }
                        }

                        $v_points   = isset( $this->settings['csv_also_export']['points'] );
                        $v_rtime    = isset( $this->settings['csv_also_export']['rtime'] );
                        $v_country  = isset( $this->settings['csv_also_export']['country'] );
                        $v_date     = isset( $this->settings['csv_also_export']['date'] );
                        $v_tid      = isset( $this->settings['csv_also_export']['tid'] );
                        $v_vars     = isset( $this->settings['csv_also_export']['vars'] );
                        $v_labels   = isset( $this->settings['csv_also_export']['labels'] );

                        if( $v_points )
                        $head[] = '*' . t( 'Points' );

                        if( $v_rtime )
                        $head[] = '*' . t( 'Response time' );

                        if( $v_country ) {
                            $head[]     = '*' . t( 'Country' );
                            $countries  = new \query\countries;
                            $countries  = $countries->select( [ 'iso_3166, name' ] )->fetch( -1 );
                        }

                        if( $v_date )
                        $head[] = '*' . t( 'Date' );

                        if( $v_tid )
                        $head[] = '*' . t( 'Tracking id' );

                        if( $v_vars )
                        $head[] = '*' . t( 'Variables' );

                        if( $v_labels ) {
                            $labels = $survey->getLabels();
                            $labels ->select( [ 'id', 'name' ] );
                            $labels = $labels->fetch( -1 );
                            foreach( $labels as $label ) {
                                $head[] = '*' . $label->name;
                            }
                        }

                        $export     = new \admin\markup\question\export_response;
                        $responses  = $report->getResults();
                        $fResponses = $responses->fetch( -1 );

                        $out        = fopen( 'php://output', 'w' );
                        fputcsv( $out, $head );

                        foreach( $fResponses as $response ) {
                            $responses  ->setObject( $response );
                            $export     ->setResults( $responses->getResults() );
                            $rDate      = custom_time( $response->date, 2 );
                            $answers    = [];
                            foreach( $stepsList as $sid => $step ) {
                                $steps  ->setObject( $step );
                                $export ->setStep( $steps );
                                $answers[] = '';
                                foreach( $step->questions as $qid => $question ) {
                                    $questions  ->setObject( $question );
                                    $export     ->setQuestion( $questions );
                                    $answers[]  = $export->result();
                                }
                            }

                            if( $v_points )
                            $answers[] = ( $responses->getAnswer( 5 )->value ?? 0 );
    
                            if( $v_rtime )
                            $answers[] = strtotime( $responses->getFinishDate() ) - strtotime( $responses->getDate() );
    
                            if( $v_country )
                            $answers[]  = $countries[$responses->getCountry()]->name ?? '';
                                
                            if( $v_date )
                            $answers[] = $rDate;
    
                            if( $v_tid ) {
                                $trackIds   = $responses->answerSelect( 'value_str' )->getAnswer( 8, $responses->getId() );
                                if( isset( $trackIds->value_str ) )
                                $trackIds   = [ $trackIds ];
                                $ids        = [];
                                foreach( $trackIds as $value ) {
                                    $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                                    $ids[]  = current( $v );   
                                }
                                $answers[]  = cms_json_encode( $ids );
                            }
            
                            if( $v_vars ) {
                                $vars       = $responses->answerSelect( 'value_str' )->getAnswer( 9, $responses->getId() );
                                if( isset( $vars->value_str ) )
                                $vars       = [ $vars ];
                                $ids        = [];
                                foreach( $vars as $value ) {
                                    $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                                    $ids[key( $v )] = current( $v );   
                                }
                                $answers[]  = cms_json_encode( $ids );
                            }

                            if( $v_labels ) {
                                $rLabels    = $responses->getLabels();
                                $rLabels    ->select( [ 'l.id', 'l.name' ] );
                                $rLabels    = $rLabels->fetch( -1 );

                                foreach( $labels as $labelId => $labelName ) {
                                    $answers[] = isset( $rLabels[$labelId] );
                                }
                            }

                            fputcsv( $out, $answers );
                        }

                        fclose( $out );

                        die;
                    break;
                }
            break;

            case 'response':
                header( 'Content-Encoding: UTF-8' );
                header( 'Content-Type: text/csv; charset=UTF-8');
                header( 'Content-Disposition: attachment; filename=responses.csv' );

                // Responses class
                $response = new \query\survey\results;
                $response ->setId( $this->id );

                // Return if the response does not exist
                if( !$response->getObject() ) {
                    return ;
                }

                // Return if the report does not exist
                if( !$response->getObject() ) return ;

                // Surveys class
                $survey = $response->getSurvey();

                // Return if the survey does not exist
                if( !$survey->getObject() ) return ;
    
                // Insufficient permissions to view report

                $steps      = $survey->getSteps();
                $stepsList  = $steps->fetch( -1 );
                $questions  = $survey->getQuestions()
                            ->setVisible( 2 );

                foreach( $questions->fetch( -1 ) as $q => $question ) {
                    if( isset( $stepsList[$question->step] ) )
                    $stepsList[$question->step]->questions[$q] = $question;
                }

                $head       = [];
                $countries  = [];
                $labels     = [];

                foreach( $stepsList as $step ) {
                    $steps  ->setObject( $step );
                    $head[] = '#' . $steps->getName();
                    foreach( $step->questions as $question ) {
                        $questions  ->setObject( $question );
                        $head[]     = $questions->getTitle();
                    }
                }

                $v_points   = isset( $this->settings['csv_also_export']['points'] );
                $v_rtime    = isset( $this->settings['csv_also_export']['rtime'] );
                $v_country  = isset( $this->settings['csv_also_export']['country'] );
                $v_date     = isset( $this->settings['csv_also_export']['date'] );
                $v_tid      = isset( $this->settings['csv_also_export']['tid'] );
                $v_vars     = isset( $this->settings['csv_also_export']['vars'] );
                $v_labels   = isset( $this->settings['csv_also_export']['labels'] );

                if( $v_points )
                $head[] = '*' . t( 'Points' );

                if( $v_rtime )
                $head[] = '*' . t( 'Response time' );

                if( $v_country ) {
                    $head[]     = '*' . t( 'Country' );
                    $countries  = new \query\countries;
                    $countries  = $countries->select( [ 'iso_3166, name' ] )->fetch( -1 );
                }

                if( $v_date )
                $head[] = '*' . t( 'Date' );

                if( $v_tid )
                $head[] = '*' . t( 'Tracking id' );

                if( $v_vars )
                $head[] = '*' . t( 'Variables' );

                if( $v_labels ) {
                    $labels = $survey->getLabels();
                    $labels ->select( [ 'id', 'name' ] );
                    $labels = $labels->fetch( -1 );
                    foreach( $labels as $label ) {
                        $head[] = '*' . $label->name;
                    }
                }

                $export     = new \admin\markup\question\export_response;

                $out        = fopen( 'php://output', 'w' );
                fputcsv( $out, $head );

                $export     ->setResults( $response->getResults() );
                $rDate      = custom_time( $response->getDate(), 2 );
                $answers    = [];
                foreach( $stepsList as $sid => $step ) {
                    $steps  ->setObject( $step );
                    $export ->setStep( $steps );
                    $answers[] = '';
                    foreach( $step->questions as $qid => $question ) {
                        $questions  ->setObject( $question );
                        $export     ->setQuestion( $questions );
                        $answers[]  = $export->result();
                    }
                }

                if( $v_points )
                $answers[] = ( $response->getAnswer( 5 )->value ?? 0 );

                if( $v_rtime )
                $answers[] = strtotime( $response->getFinishDate() ) - strtotime( $response->getDate() );

                if( $v_country )
                $answers[]  = $countries[$response->getCountry()]->name ?? '';
                    
                if( $v_date )
                $answers[] = $rDate;

                if( $v_tid ) {
                    $trackIds   = $response->answerSelect( 'value_str' )->getAnswer( 8, $response->getId() );
                    if( $trackIds ) {
                        if( isset( $trackIds->value_str ) )
                        $trackIds   = [ $trackIds ];
                        $ids        = [];
                        foreach( $trackIds as $value ) {
                            $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                            $ids[]  = current( $v );   
                        }
                        $answers[]  = cms_json_encode( $ids );
                    } else 
                        $answers[]  = '';
                }

                if( $v_vars ) {
                    $vars       = $response->answerSelect( 'value_str' )->getAnswer( 9, $response->getId() );
                    if( $vars ) {
                        if( isset( $vars->value_str ) )
                        $vars       = [ $vars ];
                        $ids        = [];
                        foreach( $vars as $value ) {
                            $v      = ( !empty( $value->value_str ) ? json_decode( $value->value_str, true ) : [] );
                            $ids[key( $v )] = current( $v );   
                        }
                        $answers[]  = cms_json_encode( $ids );
                    } else
                        $answers[]  = '';
                }

                if( $v_labels ) {
                    $rLabels    = $response->getLabels();
                    $rLabels    ->select( [ 'l.id', 'l.name' ] );
                    $rLabels    = $rLabels->fetch( -1 );

                    foreach( $labels as $labelId => $labelName ) {
                        $answers[] = isset( $rLabels[$labelId] );
                    }
                }

                fputcsv( $out, $answers );

                fclose( $out );

                die;
            break;
        }

    }

    public function export() {
        return $this->getData();
    }

}
<?php

namespace admin\markup\question;

class export_response {

    private $result;
    private $question;
    private $step;
    private $results;

    public function setResults( $results ) {
        $this->results  = $results;
        return $this;
    }

    public function setQuestion( \query\survey\questions $question ) {
        $this->question = $question;
        $this->result   = '';
        $this->getData();
        return $this;
    }

    public function setStep( \query\survey\steps $step ) {
        $this->step = $step;
        return $this;
    }

    private function getData() {
        $result = $this->results['st'][$this->step->getId()]['vl'][$this->question->getId()] ?? NULL;

        switch( $this->question->getType() ) {
            case 'net_prom':
                // Result
                $vote   = $result['int_group'][0] ?? NULL;

                if( $vote < 7 ) {
                    $this->result = t( 'Detractor' ) . '(' . $vote . ')';
                } else if( $vote < 9 ) {
                    $this->result = t( 'Passive' ) . '(' . $vote . ')';
                } else {
                    $this->result = t( 'Promoter' ) . '(' . $vote . ')';
                }
            break;
            
            case 'srating':
                // Result
                $setting    = $this->question->getSetting();
                $stars      = $setting['srating_setting']['stars'] ?? 5;
                $vote       = $result['int_group'][0] ?? NULL;
                
                $this->result  = $vote . '/' . $stars;
            break;

            case 'checkbox':
                // Result
                $vote = $result['int_group'][0] ?? NULL;
                
                switch( $vote ) {
                    case 0:
                        $this->result = t( 'No' ) . '(0)';
                    break;
                    case 1:
                        $this->result = t( 'Yes' ) . '(1)';
                    break;
                }
            break;

            case 'slider':
                // Result
                $vote       = $result['int_group'][0] ?? NULL;
                if( !$vote ) return ;
                $setting    = $this->question->getSetting();
                $min        = $setting['slider_setting']['from'] ?? 1;
                $max        = $setting['slider_setting']['to'] ?? 100;

                $this->result = $vote . '(' . $min . '-' . $max . ')';
            break;

            case 'matrix_rs':
                // Result
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $votes      = $result['int_cascade'] ?? NULL;
                $results    = [];

                foreach( $fLabels as $label ) {
                    $labels     ->setObject( $label );
                    $labelId    = $labels->getId();
                    $vote       = $votes[$labelId] ?? NULL;
                    if( !$vote ) continue ;
                    $stars      = 5;
                    $results[]  = $labels->getTitle() . ' ' . $vote . '/' . $stars;
                }

                if( !empty( $results ) )
                $this->result = cms_json_encode( $results );
            break;

            case 'matrix_mc':
                // Rresult
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->question->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );
                $votes      = $result['int_cascade'] ?? [];
                $results    = [];

                foreach( $votes as $labelId => $columnId ) {
                    $vote   = $votes[$labelId] ?? NULL;
                    if( $vote && isset( $fLabels[$labelId] ) && isset( $fColumns[$columnId] ) ) {
                        $labels ->setObject( $fLabels[$labelId] );
                        $columns->setObject( $fColumns[$columnId] );
                        $results[$labels->getTitle()] = $columns->getTitle();
                    }
                }

                if( !empty( $results ) )
                $this->result = cms_json_encode( $results );
            break;

            case 'matrix_dd':
                // Rresult
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->question->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );
                $vgroups    = $result['int_cascade'] ?? [];
                $results    = [];

                foreach( $vgroups as $labelId => $votes ) {
                    if( isset( $fLabels[$labelId] ) ) {
                        foreach( $votes as $columnId => $optionId ) {
                            $labels ->setObject( $fLabels[$labelId] );
                            if( isset( $fColumns[$columnId] ) ) {
                                $columns    ->setObject( $fColumns[$columnId] );
                                $colName    = $columns->getTitle();
                                $options    = $columns->getOptions();
                                $fOptions   = $options->fetch( -1 );

                                if( isset( $fOptions[$optionId] ) ) {
                                    $options->setObject( $fOptions[$optionId] );
                                    $results[$labels->getTitle()][$colName] = $options->getTitle();
                                }
                            }
                        }
                    }
                }

                if( !empty( $results ) )
                $this->result = cms_json_encode( $results );
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                // Result
                $options    = $this->question->getOptions();
                $fOpts      = $options->fetch( -1 );
                $values     = $result['int_group'] ?? [];
                $results    = [];

                foreach( $fOpts as $option ) {
                    $options    ->setObject( $option );
                    if( array_search( $options->getId(), $values ) !== false )
                    $results[]  = $options->getTitle();
                }

                if( !empty( $results ) )
                $this->result = cms_json_encode( $results );
            break;

            case 'date':
                if( !empty( $result['date'] ) )
                $this->result = custom_time( $result['date'], 4 );
            break;

            case 'text':
            case 'textarea':
            case 'contact':
                if( !empty( $result['text'] ) )
                $this->result = $result['text'];
            break;

            case 'ranking':
                // Rresult
                $options    = $this->question->getOptions()
                            ->fetch( -1 );                
                $value      = $result['int_cascade'] ?? [];
                $results    = [];

                foreach( $value as $opt_id => $pos ) {
                    if( isset( $options[$opt_id] ) ) {
                        $results[$pos] = esc_html( $options[$opt_id]->title );
                    }
                }
                
                if( !empty( $results ) )
                $this->result = cms_json_encode( $results );
            break;

            case 'file':
                if( !empty( $result['attachments'] ) || !empty( $result['text'] ) ) {
                    $value = [];
                    if( !empty( $result['text'] ) ) {
                        $value = [ $result['text'] ];
                    } else if( !empty( $result['attachments'] ) ) {
                        foreach( $result['attachments'] as $attachment ) {
                            $URL        = mediaLinks( (int) $attachment['media'] )->getItemURL();
                            if( $URL )
                            $value[]    = $URL;
                        }
                    }
                    $this->result = cms_json_encode( $value );
                }
            break;

            default:
            return filters()->do_filter( 'question:responses:export:csv', false, $this->question, $this->step, $this->results );
        }
    }

    public function result() {
        return $this->result;
    }
    
}
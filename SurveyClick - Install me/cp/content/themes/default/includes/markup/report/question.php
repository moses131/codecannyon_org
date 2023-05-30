<?php

namespace admin\markup\report;

class question {

    private $questions;
    private $placeholders   = []; 
    private $data           = [];
    private $results        = [];

    function __construct( $question = NULL) {
        // Init questions class
        $this->questions = new \query\survey\questions;

        // Set question
        if( $question );
        $this->setQuestion( $question );
    }

    public function setQuestion( $question ) {
        if( is_numeric( $question ) ) {
            $this->questions
            ->resetInfo()
            ->setId( $question );

        } else if( is_object( $question ) ) {
            $this->questions
            ->setObject( $question );
        }

        return $this;
    }

    public function setResults( array $results ) {
        $this->results += $results;
        return $this;
    }

    private function markupStatQuestion() {
        $markup = '
        <h3>
            <span>' . esc_html( $this->questions->getTitle() ) . '</span>
            <ul class="btnset s2">';
                $markup .= '
                <li>
                    <a href="#"><i class="fas fa-chart-bar"></i></a>
                    <ul class="btnset" data-types>
                        <li><a href="#" data-type="text"><span>' . t( 'Text' ) . '</span></a></li>
                        <li><a href="#" data-type="pie"><span>' . t( 'Pie' ) . '</span></a></li>
                        <li><a href="#" data-type="3d"><span>' . t( '3D' ) . '</span></a></li>
                        <li><a href="#" data-type="donut"><span>' . t( 'Donut' ) . '</span></a></li>
                    </ul>
                </li>
            </ul>
        </h3>';

        return $markup;
    }

    public function questionMarkup() {
        if( !$this->questions->getObject() ) return ;

        $markup = '<div class="q l">';
        $result = $this->results[$this->questions->getId()] ?? NULL;

        switch( $this->questions->getType() ) {
            // Net Promoter® Score question type
            case 'net_prom':
                // Question header
                $markup .= $this->markupStatQuestion();
                
                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '', '', '' ];

                $ac_markup  = '';

                if( ( $count = $result['count'] ?? 0 ) ) {
                    $detractors = $passives = $promoters = 0;
                    foreach( $result['opts'] as $note => $votes ) {
                        if( $note < 7 ) {
                            $detractors += $votes;
                        } else if( $note < 9 ) {
                            $passives   += $votes;
                        } else {
                            $promoters  += $votes;
                        }
                    }

                    $promotersp = $promoters ? round( ( $promoters / $count ) * 100, 1 ) . '%' : '0%';
                    $passivesp  = $passives ? round( ( $passives / $count ) * 100, 1 ) . '%' : '0%';
                    $detractorsp= $detractors ? round( ( $detractors / $count ) * 100, 1 ) . '%' : '0%';

                    $this->data[$this->questions->getId()][]  = [ t( 'Promoters' ) . ' (' . $promotersp . ')', $promoters, t( 'Promoters' ), $promotersp ];
                    $this->data[$this->questions->getId()][]  = [ t( 'Passives' ) . ' (' . $passivesp . ')', $passives, t( 'Passives' ), $passivesp ];
                    $this->data[$this->questions->getId()][]  = [ t( 'Detractors' ) . ' (' . $detractorsp . ')', $detractors, t( 'Detractors' ), $detractorsp ];

                    $list   = '<li><span>' . t( 'Promoters' ) . '</span><span>' . $promotersp . '</span></li>' . "\n";
                    $list   .= '<li><span>' . t( 'Passives' ) . '</span><span>' . $passivesp . '</span></li>' . "\n";
                    $list   .= '<li><span>' . t( 'Detractors' ) . '</span><span>' . $detractorsp . '</span></li>' . "\n";

                    $ac_markup  = '<ul>' . $list . '</ul>';
                }

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( $ac_markup != '' ? $ac_markup : '' );

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;
            break;

            // Stars rating question type
            case 'srating':
                // Question header
                $markup .= $this->markupStatQuestion();

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '', '', '' ];

                $setting    = $this->questions->getSetting();
                $stars      = $setting['srating_setting']['stars'] ?? 5;
                $count      = 0;
                $ac_markup = '';
                
                for( $i = $stars; $i >= 1; $i-- ) {
                    $v      = $result['opts'][$i] ?? 0;
                    $count += $v;
                }

                if( $count ) {
                    $avg        = 0;
                    $ac_markup  = '';
                    $list       = '';

                    for( $i = $stars; $i >= 1; $i-- ) {
                        $votes  = $result['opts'][$i] ?? 0;
                        $avg    += $i * $votes;
                        $note   = sprintf( t( '%s-star' ), $i );
                        $perc   = $votes ? round( ( $votes / $count ) * 100, 1 ) . '%' : '0%';
                        $list   .= '<li><span>' . $i . '</span><span>' . $perc . '</span></li>' . "\n";
                        $this->data[$this->questions->getId()][] = [ $note . ' (' . $perc . ')', $votes, $note, $perc ];
                    }
            
                    $ac_markup  = '<ul>' . $list . '</ul>';

                    $average    = $avg / $count;
                    $ac_markup  .= '<div class="rating"><div>';
                    $ac_markup  .= '<div class="stars">';
            
                    for( $i = 1; $i <= $stars; $i++ ) {
                        if( $average >= $i )
                        $ac_markup .= '<i class="fas fa-star"></i>';
                        else if( $i <= ( $average + 0.5 ) ) 
                        $ac_markup .= '<i class="fas fa-star-half-alt"></i>';
                        else 
                        $ac_markup .= '<i class="far fa-star"></i>';
                    }
            
                    $ac_markup .= '</div>';
                    $ac_markup .= '<div>' . sprintf( t( '%s out of %s votes' ), round( $average, 1 ), $count ) . '</div>';
                    $ac_markup .= '</div>';
                    $ac_markup .= '</div>';
                }
                    
                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( $ac_markup != '' ? $ac_markup : '' );

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;
            break;

            // Checkbox question type
            case 'checkbox':
                // Question header
                $markup .= $this->markupStatQuestion();

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '', '', '' ];

                $ac_markup  = '';

                if( ( $count = $result['count'] ?? 0 ) ) {
                    $yes = $no = 0;
                    foreach( $result['opts'] as $note => $votes ) {
                        if( $note == 0 ) {
                            $no += $votes;
                        } else {
                            $yes += $votes;
                        }
                    }
            
                    $yesp   = $yes ? round( ( $yes / $count ) * 100, 1 ) . '%' : '0%';
                    $nop    = $no ? round( ( $no / $count ) * 100, 1 ) . '%' : '0%';
            
                    $this->data[$this->questions->getId()][]  = [ t( 'Yes' ) . ' (' . $yesp . ')', $yes, t( 'Yes' ), $yesp ];
                    $this->data[$this->questions->getId()][]  = [ t( 'No' ) . ' (' . $nop . ')', $no, t( 'No' ), $nop ];

                    $list   = '<li><span>' . t( 'Yes' ) . '</span><span>' . $yesp . '</span></li>' . "\n";
                    $list   .= '<li><span>' . t( 'No' ) . '</span><span>' . $nop . '</span></li>' . "\n";

                    $ac_markup  = '<ul>' . $list . '</ul>';
                }

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( $ac_markup != '' ? $ac_markup : '' );

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;
            break;

            // Slider question type
            case 'slider':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question settings
                $setting    = $this->questions->getSetting();
                $min        = $setting['slider_setting']['from'] ?? 1;
                $max        = $setting['slider_setting']['to'] ?? 100;
                $diff       = $max - $min + 1;
                $avg        = 0;
                $list       = '';
                $ac_markup  = '';

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '', '', '' ];

                if( $diff <= 10 ) {
                    $count = 0;
                    for( $i = $min; $i <= $max; $i++ ) {
                        $v      = $result['opts'][$i] ?? 0;
                        $count += $v;
                    }
                    if( $count )
                    for( $i = $min; $i <= $max; $i++ ) {
                        $votes  = $result['opts'][$i] ?? 0;
                        $avg    += $i * $votes;
                        $perc   = $votes ? round( ( $votes / $count ) * 100, 1 ) . '%' : '0%';
                        $list   .= '<li><span>' . $i . '</span><span>' . $perc . '</span></li>' . "\n";
                        $this->data[$this->questions->getId()][] = [ $i . ' (' . $perc . ')', $votes, $i, $perc ];
                    }
                } else {
                    $count      = 0;
                    $group_limit= floor( $diff / 5 );
                    $group[1]   = [ 'limit' => $min,                    'count' => 0,   'name' => $min . '-' . ($group_limit+$min-1) ];
                    $group[2]   = [ 'limit' => ($group_limit+$min),     'count' => 0,   'name' => ($group_limit+$min) . '-' . ($group_limit*2+$min-1) ];
                    $group[3]   = [ 'limit' => ($group_limit*2+$min),   'count' => 0,   'name' => ($group_limit*2+$min) . '-' . ($group_limit*3+$min-1) ];
                    $group[4]   = [ 'limit' => ($group_limit*3+$min),   'count' => 0,   'name' => ($group_limit*3+$min) . '-' . ($group_limit*4+$min-1) ];
                    $group[5]   = [ 'limit' => ($group_limit*4+$min),   'count' => 0,   'name' => ($group_limit*4+$min) . '-' . $max ];

                    if( $result ) {
                        foreach( $result['opts'] as $value => $votes ) {
                            if( $value >= $group[5]['limit'] ) {
                                if( $value <= $max ) {
                                    $avg                += $value * $votes;
                                    $count              += $votes;
                                    $group[5]['count']  += $votes;
                                }
                            } else if( $value >= $group[4]['limit'] ) {
                                $avg                += $value * $votes;
                                $count              += $votes;
                                $group[4]['count']  += $votes;
                            } else if( $value >= $group[3]['limit'] ) {
                                $avg                += $value * $votes;
                                $count              += $votes;
                                $group[3]['count']  += $votes;
                            } else if( $value >= $group[2]['limit'] ) {
                                $avg                += $value * $votes;
                                $count              += $votes;
                                $group[2]['count']  += $votes;
                            } else if( $value >= $group[1]['limit'] ) {
                                if( $value >= $min ) {
                                    $avg                += $value * $votes;
                                    $count              += $votes;
                                    $group[1]['count']  += $votes;
                                }
                            }
                        }
                    }
        
                    if( $count ) {
                        foreach( $group as $g ) {
                            $perc   = $g['count'] ? round( ( $g['count'] / $count ) * 100, 1 ) . '%' : '0%';
                            $list   .= '<li><span>' . $g['name'] . '</span><span>' . $perc . '</span></li>' . "\n";
                            $this->data[$this->questions->getId()][] = [ $g['name'] . ' (' . $perc . ')', $g['count'], $g['name'], $perc ];
                        }
                    }
                }

                if( $count ) {
                    $ac_markup  = '<ul>' . $list . '</ul>';

                    $average    = round( ( $avg / $count ), 1 );
                    $percent    = round( ( $average / $max * 100 ), 1 );
                    $ac_markup  .= '
                    <div class="slider">
                        <div>
                            <div>' . $min . '</div>
                            <div class="line">
                                <div style="width:' . $percent . '%;"></div>
                                <div style="left:calc(' . $percent . '% - 75px);">' . $average . '</div>
                            </div>
                            <div>' . $max . '</div>
                        </div>
                    </div>';
                }

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( $ac_markup != '' ? $ac_markup : '' );

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;
            break;

            case 'matrix_rs':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );

                foreach( $fLabels as $label ) {
                    $labels     ->setObject( $label );
                    $labelId    = $labels->getId();
                    $avg        = 0;
                    $list       = '';
                    $uqid       = 'stat_' . uniqid();
                    $markup     .= '<h3 class="s2">' . esc_html( $labels->getTitle() ) . '</h3>';
                    $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                    // Results
                    $this->data[$this->questions->getId()][$labelId]      = [];
                    $this->data[$this->questions->getId()][$labelId][]    = [ '', '', '', '' ];

                    if( !empty( $result['opts'][$labelId] ) ) {
                        $result_o   = $result['opts'][$labelId] ?? [];
                        $count      = $result_o['count'] ?? 0;
                
                        if( $count ) {
                            for( $i = 5; $i >= 1; $i-- ) {
                                $votes  = $result_o['opts'][$i] ?? 0;
                                $avg    += $i * $votes;
                                $note   = sprintf( t( '%s-star' ), $i );
                                $perc   = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                                $list   .= '<li><span>' . $note . '</span><span>' . $perc . '</span></li>' . "\n";
                                $this->data[$this->questions->getId()][$labelId][] = [ $note . ' (' . $perc . ')', $votes, $note, $perc ];
                            }

                            $average    = $avg / $count;
                            $ac_markup  = '<ul>' . $list . '</ul>';
                    
                            $ac_markup  .= '<div class="rating"><div>';
                            $ac_markup  .= '<div class="stars">';
                    
                            for( $i = 1; $i <= 5; $i++ ) {
                                if( $average >= $i )
                                $ac_markup .= '<i class="fas fa-star"></i>';
                                else if( $i <= ( $average + 0.5 ) ) 
                                $ac_markup .= '<i class="fas fa-star-half-alt"></i>';
                                else 
                                $ac_markup .= '<i class="far fa-star"></i>';
                            }
                    
                            $ac_markup  .= '</div>';
                            $ac_markup  .= '<div>' . sprintf( t( '%s out of %s votes' ), round( $average, 1 ), $count ) . '</div>';
                            $ac_markup  .= '</div>';
                            $ac_markup  .= '</div>';

                            // Placeholder markup
                            $markup .= $ac_markup;
                        }
                    }

                    // Placeholder for the result
                    $this->placeholders[$this->questions->getId()][$labelId] = '.' . $uqid;
                }
            break;

            case 'matrix_mc':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels & columns
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->questions->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );

                foreach( $fLabels as $label ) {
                    $labels     ->setObject( $label );
                    $labelId    = $labels->getId();
                    $list       = '';

                    // Results
                    $this->data[$this->questions->getId()][$labelId]      = [];
                    $this->data[$this->questions->getId()][$labelId][]    = [ '', '', '', '' ];

                    if( !empty( $result['opts'][$labelId] ) ) {
                        $result_o   = $result['opts'][$labelId] ?? [];
                        $count      = $result_o['count'] ?? 0;
        
                        if( $count )
                        foreach( $fColumns as $column ) {
                            $columns    ->setObject( $column );
                            $votes      = $result_o['opts'][$columns->getId()] ?? 0;
                            $perc       = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                            $list       .= '<li><span>' . $columns->getTitle() . '</span><span>' . $perc . '</span></li>' . "\n";
                            $this->data[$this->questions->getId()][$labelId][] = [ $columns->getTitle() . ' (' . $perc . ')', $votes, $columns->getTitle(), $perc ];
                        }
                    }

                    $uqid       = 'stat_' . uniqid();
                    $markup     .= '<h3 class="s2">' . esc_html( $labels->getTitle() ) . '</h3>';
                    $ac_markup  = '<ul>' . $list . '</ul>';

                    // Placeholder markup
                    $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( isset( $ac_markup ) ? $ac_markup : '' );

                    // Placeholder for the result
                    $this->placeholders[$this->questions->getId()][$labelId] = '.' . $uqid;
                }
            break;

            case 'matrix_dd':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels & columns
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->questions->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );

                foreach( $fLabels as $label ) {
                    $labels ->setId( $label->id )
                            ->setObject( $label );
                    $markup .= '<h3 class="tc s2">' . esc_html( $labels->getTitle() ) . '</h3>';

                    foreach( $fColumns as $columnId => $column ) {
                        $columns    ->setObject( $column );
                        $options    = $columns->getOptions();
        
                        $result_o   = $result['opts'][$label->id]['opts'][$column->id] ?? [];
                        $count      = $result_o['count'] ?? 0;
                        $colTitle   = $columns->getTitle();
                        $labCol     = $labels->getId() . '_' . $columns->getId();
                        $list       = '';
                        
                        // Results
                        $this->data[$this->questions->getId()][$labCol]     = [];
                        $this->data[$this->questions->getId()][$labCol][]   = [ '', '', '', '' ];

                        if( $count ) {                                        
                            foreach( $options->fetch( -1 ) as $option ) {
                                $options    ->setObject( $option );
                                $votes      = $result_o['opts'][$options->getId()] ?? 0;
                                $perc       = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                                $list       .= '<li><span>' . esc_html( $options->getTitle() ) . '</span><span>' . $perc . '</span></li>' . "\n";
                                $this->data[$this->questions->getId()][$labCol][] = [ $options->getTitle() . ' (' . $perc . ')', $votes, $options->getTitle(), $perc ];
                            }
                        }
        
                        $uqid       = 'stat_' . uniqid();
                        $markup     .= '<h3 class="s2">' . esc_html( $colTitle ) . '</h3>';
                        $ac_markup  = '<ul>' . $list . '</ul>';
        
                        // Placeholder markup
                        $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( isset( $ac_markup ) ? $ac_markup : '' );

                        // Placeholder for the result
                        $this->placeholders[$this->questions->getId()][$labCol] = '.' . $uqid;        
                    }
                }
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Options
                $options    = $this->questions->getOptions();
                $fOpts      = $options->fetch( -1 );
                $count      = $result['count'] ?? 0;
                $list       = '';

                // Results
                $this->data[$this->questions->getId()]      = [];
                $this->data[$this->questions->getId()][]    = [ '', '', '', '' ];

                if( $count ) {
                    foreach( $fOpts as $option ) {
                        $options    ->setObject( $option );
                        $votes      = $result['opts'][$options->getId()] ?? 0;
                        $perc       = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%' );
                        $list       .= '<li><span>' . esc_html( $options->getTitle() ) . '</span><span>' . $perc . '</span></li>' . "\n";
                        $this->data[$this->questions->getId()][] = [ esc_html( $options->getTitle() ) . ' (' . $perc . ')', $votes, esc_html( $options->getTitle() ), $perc ];
                    }

                    $ac_markup  = '<ul>' . $list . '</ul>';
                }

                // Placeholder markup
                $uqid       = 'stat_' . uniqid();
                $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( isset( $ac_markup ) ? $ac_markup : '' );

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;   
            break;

            case 'ranking':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Options
                $options    = $this->questions->getOptions();
                $fOpts      = $options->fetch( -1 );
                $limit      = count( $fOpts );

                foreach( $fOpts as $option ) {
                    $options    ->setObject( $option );
                    $votes      = $result['opts'][$options->getId()] ?? 0;
                    $list       = '';

                    $this->data[$this->questions->getId()][$options->getId()]   = [];
                    $this->data[$this->questions->getId()][$options->getId()][] = [ '', '', '', '' ];

                    for( $i = 1; $i <= $limit; $i++ ) {
                        $count  = $result['opts'][$options->getId()]['count'] ?? 0;
                        $votes  = $result['opts'][$options->getId()]['opts'][$i] ?? 0;
                        $perc   = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%' );
                        $list   .= '<li><span>' . $i . '</span><span>' . $perc . '</span></li>' . "\n";
                        $this->data[$this->questions->getId()][$options->getId()][] = [ $i . ' (' . $perc . ')', $votes, esc_html( $options->getTitle() ), $perc ];
                    }

                    $uqid       = 'stat_' . uniqid();
                    $markup     .= '<h3 class="s2">' . esc_html( $options->getTitle() ) . '</h3>';
                    $ac_markup  = '<ul>' . $list . '</ul>';

                    // Placeholder markup
                    $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>' . ( isset( $ac_markup ) ? $ac_markup : '' );

                    // Placeholder for the result
                    $this->placeholders[$this->questions->getId()][$options->getId()] = '.' . $uqid;
                }
            break;

            default:
            return filters()->do_filter( 'question:reports:view', false, $this->questions, $this->markupStatQuestion(), 'stat_' . uniqid() );
        }

        $markup .= '</div>';
        
        return $markup;
    }

    public function getPlaceholders() {
        return $this->placeholders;
    }

    public function getData() {
        return $this->data;
    }
    
}
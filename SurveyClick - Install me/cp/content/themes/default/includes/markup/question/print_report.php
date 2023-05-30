<?php

namespace admin\markup\question;

class print_report {

    private $questions;
    private $report;
    private $legend         = [];
    private $placeholders   = []; 
    private $data           = [];
    private $results        = [];

    function __construct( $question = NULL) {
        // Init questions class
        $this->questions = new \query\survey\questions;

        // Set question
        if( $question );
        $this->setQuestion( $question );

        // Get colors
        $this->colors = filters()->do_filter( 'chart_colors', [ '#00A170', '#FFB347', '#D31027', '#55B4B0' ] );
    }

    public function setQuestion( $question ) {
        $this->legend = [];
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

    public function setReport( int $report ) {
        $this->report = $report;
        return $this; 
    }

    public function setResults( array $results ) {
        $this->results += $results;
        return $this;
    }

    public function color( int $pos ) {
        if( isset( $this->colors[$pos] ) ) {
            return $this->colors[$pos];
        }
        return end( $this->colors );
    }

    private function markupStatQuestion() {
        $markup = '
        <h3>
        <div class="bullet"></div>
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

    public function legend() {
        $markup = '<div class="legend">';
        foreach( $this->legend as $id => $item ) {
                $markup .= '<div class="items">';
                if( isset( $item['title'] ) ) {
                    $markup .= '<h3 class="ced"><span spellcheck="false" data-group="col-' . $id . '">' . esc_html( $item['title'] ) . '</span></h3>';
                }
                $markup .= '<div class="item">';
                foreach( $item['elements'] as $eId => $item ) {
                    $markup .= '
                    <div>
                        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1">
                            <circle cx="10" cy="10" r="10" fill="' . $item[1] . '" />
                        </svg>
                        <div class="ced"><span spellcheck="false">' . esc_html( $item[0] ) . '</span></div>
                    </div>';
                }
                $markup .= '</div>
                </div>';
        }
        $markup .= '</div>';
        return $markup;
    }

    public function getLegend() {
        return $this->legend;
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
                $this->data[$this->questions->getId()][]  = [ '', '' ];

                $detractors = $passives = $promoters = 0;
                $count      = $result['count'] ?? 0;

                if( $count ) {
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

                    $this->data[$this->questions->getId()][]  = [ t( 'Promoters' ) . ' (' . $promotersp . ')', $promoters ];
                    $this->data[$this->questions->getId()][]  = [ t( 'Passives' ) . ' (' . $passivesp . ')', $passives ];
                    $this->data[$this->questions->getId()][]  = [ t( 'Detractors' ) . ' (' . $detractorsp . ')', $detractors ];
                }

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;

                // Legend
                $this->legend[] = [
                    'elements' => [
                        [ t( 'Promoters' ), $this->color( 0 ) ],
                        [ t( 'Passives' ), $this->color( 1 ) ],
                        [ t( 'Detractors' ), $this->color( 2 ) ]
                    ],
                ];

            break;

            // Stars rating question type
            case 'srating':
                // Question header
                $markup .= $this->markupStatQuestion();

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '' ];

                $setting    = $this->questions->getSetting();
                $stars      = $setting['srating_setting']['stars'] ?? 5;
                $count      = 0;
                $ac_markup  = '';
                $legendItems= [];
                
                for( $i = $stars; $i >= 1; $i-- ) {
                    $v      = $result['opts'][$i] ?? 0;
                    $count += $v;
                }

                $avg    = 0;
                $c      = 0;
                
                for( $i = $stars; $i >= 1; $i-- ) {
                    $note   = sprintf( t( '%s-star' ), $i );
                    $votes  = $result['opts'][$i] ?? 0;

                    if( $count ) {
                        $avg    += $i * $votes;
                        $perc   = $votes ? round( ( $votes / $count ) * 100, 1 ) . '%' : '0%';
                        $this->data[$this->questions->getId()][] = [ $note . ' (' . $perc . ')', $votes ];
                    }

                    $legendItems[$i] = [ $note, $this->color( $c ), $votes ];
                    $c++;
                }
            
                if( $count ) {
                    $average    = $avg / $count;
                    $ac_markup  = '<div class="rating"><div>';
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

                // Legend
                $this->legend[] = [
                    'elements' => $legendItems,
                ];
            break;

            // Checkbox question type
            case 'checkbox':
                // Question header
                $markup .= $this->markupStatQuestion();

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '' ];

                $count  = $result['count'] ?? 0;
                $yes    = $no = 0;

                if( $count ) {
                    foreach( $result['opts'] as $note => $votes ) {
                        if( $note == 0 ) {
                            $no += $votes;
                        } else {
                            $yes += $votes;
                        }
                    }
            
                    $yesp   = $yes ? round( ( $yes / $count ) * 100, 1 ) . '%' : '0%';
                    $nop    = $no ? round( ( $no / $count ) * 100, 1 ) . '%' : '0%';
            
                    $this->data[$this->questions->getId()][]  = [ t( 'Yes' ) . ' (' . $yesp . ')', $yes ];
                    $this->data[$this->questions->getId()][]  = [ t( 'No' ) . ' (' . $nop . ')', $no ];
                }

                $legendItems    = [];
                $legendItems[1] = [ t( 'Yes' ), $this->color( 0 ), $yes ];
                $legendItems[0] = [ t( 'No' ), $this->color( 1 ), $no ];

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;

                // Legend
                $this->legend[] = [
                    'elements' => $legendItems,
                ];
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
                $c          = 0;
                $legendItems= [];
                $ac_markup  = '';

                // Results
                $this->data[$this->questions->getId()]    = [];
                $this->data[$this->questions->getId()][]  = [ '', '' ];

                if( $diff <= 10 ) {
                    $count = 0;
                    for( $i = $min; $i <= $max; $i++ ) {
                        $v      = $result['opts'][$i] ?? 0;
                        $count += $v;
                    }
                    for( $i = $min; $i <= $max; $i++ ) {
                        $votes  = $result['opts'][$i] ?? 0;

                        if( $count ) {
                            $avg    += $i * $votes;
                            $perc   = $votes ? round( ( $votes / $count ) * 100, 1 ) . '%' : '0%';
                            $this->data[$this->questions->getId()][] = [ $i . ' (' . $perc . ')', $votes ];
                        }

                        $legendItems[$i] = [ $i, $this->color( $c ), $votes ];
                        $c++;
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

                    $legendItems[1] = [ $group[1]['name'], $this->color( 0 ), $group[1]['count'] ];
                    $legendItems[2] = [ $group[2]['name'], $this->color( 1 ), $group[2]['count'] ];
                    $legendItems[3] = [ $group[3]['name'], $this->color( 2 ), $group[3]['count'] ];
                    $legendItems[4] = [ $group[4]['name'], $this->color( 3 ), $group[4]['count'] ];
                    $legendItems[5] = [ $group[5]['name'], $this->color( 4 ), $group[5]['count'] ];
        
                    if( $count ) {
                        foreach( $group as $g ) {
                            $perc   = $g['count'] ? round( ( $g['count'] / $count ) * 100, 1 ) . '%' : '0%';
                            $this->data[$this->questions->getId()][] = [ $g['name'] . ' (' . $perc . ')', $g['count'] ];
                        }
                    }
                }

                if( $count ) {
                    $average    = round( ( $avg / $count ), 1 );
                    $percent    = round( ( $average / $max * 100 ), 1 );
                    $ac_markup  = '
                    <div class="slider">
                        <div>
                            <div>' . $min . '</div>
                            <div class="line">
                                <div style="width:' . $percent . '%;"></div>
                                <div style="left:calc(' . $percent . '% - 55px);">' . $average . '</div>
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

                // Legend
                $this->legend[] = [
                    'elements' => $legendItems,
                ];
            break;

            case 'matrix_rs':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );

                foreach( $fLabels as $labelId => $label ) {
                    $labels     ->setObject( $label );
                    $avg        = 0;
                    $uqid       = 'stat_' . uniqid();
                    $markup     .= '<h3 class="ced cc s2"><span spellcheck="false" data-group="label-' . $labelId . '">' . esc_html( $labels->getTitle() ) . '</span></h3>';
                    $markup     .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                    // Results
                    $this->data[$this->questions->getId()][$labelId]      = [];
                    $this->data[$this->questions->getId()][$labelId][]    = [ '', '' ];

                    if( !empty( $result['opts'][$labelId] ) ) {
                        $result_o   = $result['opts'][$labelId] ?? [];
                        $count      = $result_o['count'] ?? 0;

                        if( $count ) {
                            for( $i = 5; $i >= 1; $i-- ) {
                                $note   = sprintf( t( '%s-star' ), $i );
                                $votes  = $result_o['opts'][$i] ?? 0;
                                if( $votes ) {
                                    $avg    += $i * $votes;
                                    $perc   = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                                    $this->data[$this->questions->getId()][$labelId][] = [ $note . ' (' . $perc . ')', $votes ];
                                }
                            }

                            $average    = $avg / $count;
                    
                            $ac_markup  = '<div class="rating"><div>';
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

                $legendItems= [];
                $c          = 0;

                for( $i = 5; $i >= 1; $i-- ) {
                    $note   = sprintf( t( '%s-star' ), $i );
                    $legendItems[$i] = [ $note, $this->color( $c ), 0 ];
                    $c++;
                }

                // Legend
                $this->legend[] = [
                    'elements' => $legendItems,
                ];
            break;

            case 'matrix_mc':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels & columns
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->questions->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );
                $legendEls  = [];
                
                foreach( $fLabels as $label ) {
                    $labels     ->setObject( $label );
                    $labelId    = $labels->getId();
                    $uqid       = 'stat_' . uniqid();
                    $votes      = 0;

                    // Results
                    $this->data[$this->questions->getId()][$labelId]      = [];
                    $this->data[$this->questions->getId()][$labelId][]    = [ '', '' ];

                    if( !empty( $result['opts'][$labelId] ) ) {
                        $result_o   = $result['opts'][$labelId] ?? [];
                        $count      = $result_o['count'] ?? 0;
                        $c          = 0;

                        foreach( $fColumns as $column ) {
                            $columns    ->setObject( $column );
                            $votes      = $result_o['opts'][$columns->getId()] ?? 0;

                            if( $votes ) {
                                $perc   = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                                $this->data[$this->questions->getId()][$labelId][] = [ $columns->getTitle() . ' (' . $perc . ')', $votes ];
                            }

                            if( !isset( $legendEls[$columns->getId()] ) )
                            $legendEls[$columns->getId()] = [ esc_html( $columns->getTitle() ), $this->color( $c++ ) ];
                        }
                    }

                    $markup .= '<h3 class="ced cc s2"><span spellcheck="false" data-group="label-' . $labelId . '">' . esc_html( $labels->getTitle() ) . '</span></h3>';

                    // Placeholder markup
                    $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                    // Placeholder for the result
                    $this->placeholders[$this->questions->getId()][$labelId] = '.' . $uqid;
                }

                // Legend
                $this->legend[] = [ 'elements' => $legendEls ];
            break;

            case 'matrix_dd':
                // Question header
                $markup     .= $this->markupStatQuestion();

                // Question labels & columns
                $labels     = $this->questions->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->questions->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );

                foreach( $fLabels as $labelId => $label ) {
                    $labels ->setId( $labelId )
                            ->setObject( $label );
                    
                    $markup .= '<h3 class="ced cc label s2"><span spellcheck="false" data-group="label-' . $labelId . '">' . esc_html( $labels->getTitle() ) . '</span></h3>';

                    foreach( $fColumns as $columnId => $column ) {
                        $columns    ->setObject( $column );
                        $options    = $columns->getOptions();
        
                        $result_o   = $result['opts'][$label->id]['opts'][$column->id] ?? [];
                        $count      = $result_o['count'] ?? 0;
                        $colTitle   = $columns->getTitle();
                        $labCol     = $labelId . '_' . $columnId;
                        $uqid       = 'stat_' . uniqid();
                        $legendEls  = [];
                        $c          = 0;

                        // Results
                        $this->data[$this->questions->getId()][$labCol]     = [];
                        $this->data[$this->questions->getId()][$labCol][]   = [ '', '' ];

                        foreach( $options->fetch( -1 ) as $option ) {
                            $options    ->setObject( $option );
                            $votes      = $result_o['opts'][$options->getId()] ?? 0;

                            if( $votes ) {
                                $perc   = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                                $this->data[$this->questions->getId()][$labCol][] = [ $options->getTitle() . ' (' . $perc . ')', $votes ];
                            }

                            if( !isset( $this->legend[$columnId] ) )
                            $legendEls[$options->getId()] = [ esc_html( $options->getTitle() ), $this->color( $c++ ) ];
                        }

                        $markup .= '<h3 class="ced cc s2"><span spellcheck="false" data-group="col-' . $columnId . '">' . esc_html( $colTitle ) . '</span></h3>';
        
                        // Placeholder markup
                        $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                        // Placeholder for the result
                        $this->placeholders[$this->questions->getId()][$labCol] = '.' . $uqid;

                        // Legend
                        if( !isset( $this->legend[$columnId] ) )
                        $this->legend[$columnId] = [ 'title' => esc_html( $colTitle ), 'elements' => $legendEls ];
                    }
                }
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                // Question header
                $markup     .= $this->markupStatQuestion();

                $options    = $this->questions->getOptions();
                $fOpts      = $options->fetch( -1 );
                $count      = $result['count'] ?? 0;
                $i          = 0;
                $legendItems= [];

                // Results
                $this->data[$this->questions->getId()]      = [];
                $this->data[$this->questions->getId()][]    = [ '', '' ];

                foreach( $fOpts as $option ) {
                    $options    ->setObject( $option );
                    $votes      = $result['opts'][$options->getId()] ?? 0;
                    $perc       = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%': '0%' );
                    $this->data[$this->questions->getId()][] = [ esc_html( $options->getTitle() ) . ' (' . $perc . ')', $votes ];
                    $legendItems[$options->getId()] = [ esc_html( $options->getTitle() ), $this->color( $i ), $votes ];
                    $i          ++;
                }

                // Placeholder markup
                $uqid   = 'stat_' . uniqid();
                $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                // Placeholder for the result
                $this->placeholders[$this->questions->getId()] = '.' . $uqid;

                // Legend
                $this->legend[] = [
                    'elements' => $legendItems,
                ];
            break;

            case 'ranking':
                // Question header
                $markup     .= $this->markupStatQuestion();

                $options    = $this->questions->getOptions();
                $fOpts      = $options->fetch( -1 );
                $limit      = count( $fOpts );
                $legendItems= [];

                foreach( $fOpts as $option ) {
                    $options    ->setObject( $option );
                    $c          = 0;
                    $list       = '';

                    $this->data[$this->questions->getId()][$options->getId()]   = [];
                    $this->data[$this->questions->getId()][$options->getId()][] = [ '', '', '', '' ];

                    $markup .= '<h3 class="ced cc s2"><span spellcheck="false" data-group="opt-' . $options->getId() . '">' . esc_html( $options->getTitle() ) . '</span></h3>';

                    for( $i = 1; $i <= $limit; $i++ ) {
                        $count  = $result['opts'][$options->getId()]['count'] ?? 0;
                        $votes  = $result['opts'][$options->getId()]['opts'][$i] ?? 0;
                        $perc   = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%': '0%' );
                        $this->data[$this->questions->getId()][$options->getId()][] = [ $i . ' (' . $perc . ')', $votes, esc_html( $options->getTitle() ), $perc ];
                    }

                    // Placeholder markup
                    $uqid   = 'stat_' . uniqid();
                    $markup .= '<div class="' . $uqid . '"><i class="fas fa-circle-notch fa-spin"></i></div>';

                    // Placeholder for the result
                    $this->placeholders[$this->questions->getId()][$options->getId()] = '.' . $uqid;
                }

                for( $i = 1; $i <= $count; $i++ ) {
                    $legendItems[$i] = [ $i, $this->color( $c++ ), $votes ];
                }

                // Legend
                $this->legend[] = [ 
                    'elements' => $legendItems,
                ];
            break;

            default:
            return filters()->do_filter( 'question:reports:export:view', false, $this->questions, $this->markupStatQuestion(), 'stat_' . uniqid() );
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
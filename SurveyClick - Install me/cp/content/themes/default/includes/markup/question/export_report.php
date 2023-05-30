<?php

namespace admin\markup\question;

class export_report {

    private $head;
    private $result;
    private $question;
    private $results;

    public function setResults( $results ) {
        $this->results  = $results;
        return $this;
    }

    public function setQuestion( \query\survey\questions $question ) {
        $this->question = $question;
        $this->head     = [];
        $this->result   = [];
        $this->getData();
        return $this;
    }

    private function getData() {
        $result = $this->results[$this->question->getId()] ?? NULL;

        switch( $this->question->getType() ) {
            case 'net_prom':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $this->head    = [ $this->question->getTitle(), t( 'Promoters' ), t( 'Passives' ), t( 'Detractors' ) ];

                $detractors = $passives = $promoters = 0;
                $count      = $result['count'] ?? 0;

                if( isset( $result['opts'] ) ) {
                    foreach( $result['opts'] as $note => $votes ) {
                        if( $note < 7 ) {
                            $detractors += $votes;
                        } else if( $note < 9 ) {
                            $passives   += $votes;
                        } else {
                            $promoters  += $votes;
                        }
                    }
                }

                $promotersp = $promoters ? round( ( $promoters / $count ) * 100, 1 ) . '%' : '0%';
                $passivesp  = $passives ? round( ( $passives / $count ) * 100, 1 ) . '%' : '0%';
                $detractorsp= $detractors ? round( ( $detractors / $count ) * 100, 1 ) . '%' : '0%';

                $this->result  = [ '', $promoters . ' (' . $promotersp . ')', $passives . ' (' . $passivesp . ')' , $detractors . ' (' . $detractorsp . ')' ];
            break;
            
            case 'srating':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $setting    = $this->question->getSetting();
                $stars      = $setting['srating_setting']['stars'] ?? 5;
                $count      = 0;
                
                for( $i = $stars; $i >= 1; $i-- ) {
                    $v      = $result['opts'][$i] ?? 0;
                    $count += $v;
                }

                $average = 0;

                if( $count ) {
                    $avg        = 0;
                    for( $i = $stars; $i >= 1; $i-- ) {
                        $votes  = $result['opts'][$i] ?? 0;
                        $avg    += $i * $votes;
                    }
                    $average    = $avg / $count;
                }

                $average = round( $average, 1 );

                $this->head    = [ $this->question->getTitle(), t( 'Rating' ) ];
                $this->result  = [ '', $average . '/' . $stars ];
            break;

            case 'checkbox':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $count = $result['count'] ?? 0;
                $yes = $no = 0;

                if( isset( $result['opts'] ) ) {
                    foreach( $result['opts'] as $note => $votes ) {
                        if( $note == 0 ) {
                            $no += $votes;
                        } else {
                            $yes += $votes;
                        }
                    }
                }
        
                $yesp   = $yes ? round( ( $yes / $count ) * 100, 1 ) . '%' : '0%';
                $nop    = $no ? round( ( $no / $count ) * 100, 1 ) . '%' : '0%';
        
                $this->head    = [ $this->question->getTitle(), t( 'Yes' ), t( 'No' ) ];
                $this->result  = [ '', $yes . ' (' . $yesp . ')', $no . ' (' . $nop . ')' ];
            break;

            case 'slider':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $setting    = $this->question->getSetting();
                $min        = $setting['slider_setting']['from'] ?? 1;
                $max        = $setting['slider_setting']['to'] ?? 100;
                $diff       = $max - $min + 1;
                $avg        = 0;
                $count      = 0;

                // Results
                if( $diff <= 10 ) {
                    for( $i = $min; $i <= $max; $i++ ) {
                        $v      = $result['opts'][$i] ?? 0;
                        $count += $v;
                    }

                    for( $i = $min; $i <= $max; $i++ ) {
                        $votes  = $result['opts'][$i] ?? 0;
                        $avg    += $i * $votes;
                        $perc   = $votes ? round( ( $votes / $count ) * 100, 1 ) . '%' : '0%';
                        $group[$i] = [ 'count' => $votes . ' (' . $perc . ')', 'name' => $i ];
                    }
                } else {
                    $group_limit= floor( $diff / 5 );
                    $group[1]   = [ 'limit' => $min,                    'count' => 0,   'name' => $min . '-' . ($group_limit+$min-1) ];
                    $group[2]   = [ 'limit' => ($group_limit+$min),     'count' => 0,   'name' => ($group_limit+$min) . '-' . ($group_limit*2+$min-1) ];
                    $group[3]   = [ 'limit' => ($group_limit*2+$min),   'count' => 0,   'name' => ($group_limit*2+$min) . '-' . ($group_limit*3+$min-1) ];
                    $group[4]   = [ 'limit' => ($group_limit*3+$min),   'count' => 0,   'name' => ($group_limit*3+$min) . '-' . ($group_limit*4+$min-1) ];
                    $group[5]   = [ 'limit' => ($group_limit*4+$min),   'count' => 0,   'name' => ($group_limit*4+$min) . '-' . $max ];

                    if( isset( $result['opts'] ) ) {
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
                        foreach( $group as $gid => $g ) {
                            $group[$gid]['count'] = $g['count'] ? $g['count'] . ' (' . round( ( $g['count'] / $count ) * 100, 1 ) . '%)' : '0 (0%)';
                        }
                    }
                }

                if( $count ) {
                    $average    = round( ( $avg / $count ), 1 );
                }

                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $group as $grp ) {
                    $this->head[]   = $grp['name'];
                    $this->result[] = $grp['count'];
                }

                if( $count ) {
                    $average        = round( ( $avg / $count ), 1 );
                    $this->head[]   = t( 'Info' );
                    $this->result[] = sprintf( t( 'Average %s' ), $average );
                }
            break;

            case 'matrix_rs':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $labels         = $this->question->getLabels( 1 );
                $fLabels        = $labels->fetch( -1 );
                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $fLabels as $label ) {
                    $labels         ->setObject( $label );
                    $labelId        = $labels->getId();
                    $result_o       = $result['opts'][$labelId] ?? [];
                    $count          = $result_o['count'] ?? 0;
                    $avg            = 0;
                    $stars          = 5;

                    for( $i = $stars; $i >= 1; $i-- ) {
                        $votes  = $result_o['opts'][$i] ?? 0;
                        $avg    += $i * $votes;
                    }
                    
                    $this->head[]   = $labels->getTitle();
                    if( !$count ) $this->result[] = '';
                    else
                    $this->result[] = round( $avg / $count, 1 ) . '/' . $stars;
                }
            break;

            case 'matrix_mc':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $labels         = $this->question->getLabels( 1 );
                $fLabels        = $labels->fetch( -1 );
                $columns        = $this->question->getLabels( 2 );
                $fColumns       = $columns->fetch( -1 );
                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $fLabels as $label ) {
                    $labels         ->setObject( $label );
                    $labelId        = $labels->getId();
                    $this->head[]   = $labels->getTitle();
                    $this->result[] = '';

                    if( !empty( $result['opts'][$labelId] ) ) {
                        $result_o   = $result['opts'][$labelId] ?? [];
                        $count      = $result_o['count'] ?? 0;
        
                        foreach( $fColumns as $column ) {
                            $columns        ->setObject( $column );
                            $votes          = $result_o['opts'][$columns->getId()] ?? 0;
                            $perc           = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                            $this->head[]   = $columns->getTitle();
                            $this->result[] = $votes . ' (' . $perc . ')';
                        }
                    }
                }
            break;

            case 'matrix_dd':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $labels         = $this->question->getLabels( 1 );
                $fLabels        = $labels->fetch( -1 );
                $columns        = $this->question->getLabels( 2 );
                $fColumns       = $columns->fetch( -1 );
                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $fLabels as $label ) {
                    $labels         ->setObject( $label );
                    $this->head[]   = $labels->getTitle();
                    $this->result[] = '';
        
                    foreach( $fColumns as $column ) {
                        $columns        ->setObject( $column );
                        $options        = $columns->getOptions();
                        $this->head[]   = $columns->getTitle();
                        $this->result[] = '';

                        $result_o       = $result['opts'][$label->id]['opts'][$column->id] ?? [];
                        $count          = $result_o['count'] ?? 0;

                        foreach( $options->fetch( -1 ) as $option ) {
                            $options        ->setObject( $option );
                            $votes          = $result_o['opts'][$options->getId()] ?? 0;
                            $perc           = $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%' : '0%';
                            $this->head[]   = $columns->getTitle();
                            $this->result[] = $votes . ' (' . $perc . ')';
                        }
                    }
                }
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                // Question id
                $id = $this->question->getId();

                // Question & result
                $options        = $this->question->getOptions();
                $fOpts          = $options->fetch( -1 );
                $count          = $result['count'] ?? 0;
                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $fOpts as $option ) {
                    $options        ->setObject( $option );
                    $this->head[]   = $options->getTitle();
                    $this->result[] = '';

                    for( $i = 1; $i <= $count; $i++ ) {
                        $votes  = $result['opts'][$options->getId()]['opts'][$i] ?? 0;
                        $perc   = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%': '0%' );
                        $this->head[]   = $options->getTitle();
                        $this->result[] = $votes . ' (' . $perc . ')';
                    }
                }
            break;

            case 'ranking':
                // Rresult
                $options        = $this->question->getOptions();
                $fOpts          = $options->fetch( -1 );
                $limit          = count( $fOpts );
                $this->head     = [ $this->question->getTitle() ];
                $this->result   = [ '' ];

                foreach( $fOpts as $option ) {
                    $options        ->setObject( $option );
                    $this->head[]   = $options->getTitle();
                    $this->result[] = '';

                    for( $i = 1; $i <= $limit; $i++ ) {
                        $count  = $result['opts'][$options->getId()]['count'] ?? 0;
                        $votes  = $result['opts'][$options->getId()]['opts'][$i] ?? 0;
                        $perc   = ( $votes ? round( ( ( $votes / $count ) * 100 ), 1 ) . '%': '0%' );
                        $this->head[]   = $i;
                        $this->result[] = $votes . ' (' . $perc . ')';
                    }
                }
            break;

            default:
            return filters()->do_filter( 'question:reports:export:csv', false, $this->question, $result );
        }
    }

    public function hasHead() {
        return !empty( $this->head );
    }

    public function head() {
        return $this->head;
    }

    public function result() {
        return $this->result;
    }
    
}
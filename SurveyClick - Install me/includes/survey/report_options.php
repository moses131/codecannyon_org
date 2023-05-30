<?php

namespace survey;

class report_options extends \util\db {

    protected $answersCond;
    protected $currentOptions   = [];
    protected $labels           = [];
    protected $lastReport       = [];

    function __construct( int $survey = 0, int $user = 0 ) {
        parent::__construct();
        $this->setSurveyId( $survey );
        $this->setUserId( $user );
    }

    public function setSurveyId( int $survey ) {
        if( $survey ) $this->survey = $survey;
        return $this;
    }

    public function setUserId( int $user ) {
        if( $user ) $this->user = $user;
        else if( !$user && me() ) $this->user = me()->getId();
        return $this;
    }

    public function setTypes( $type ) {
        $this->types = $type;
        return $this;
    }

    public function setGroupBy( string $type = '' ) {
        switch( $type ) {
            case 'question':
                $this->groupBy = 'a.question, a.value, a.value2, a.value3';
            break;

            default:
                $this->groupBy = 'a.question';
        }
        return $this;
    }

    public function setQuestionId( int $question ) {
        if( $question ) $this->question = $question;
        return $this;
    }

    public function setValue( int $question, array $values, int $type = 1 ) {
        $values = array_intersect_key( $values, [ 'value' => '', 'value2' => '', 'value3' => '', 'value_str' => '', 'value_date' => '' ] );
        if( empty( $values ) ) {
            return ;
        }

        $cond   = [];
        $eConds = [];

        // Validate operators
        foreach( $values as $value_type => $value ) {
            switch( $value_type ) {
                case 'value':
                case 'value2':
                case 'value3':
                    if( in_array( $value[0], [ '=', '>', '<', '>=', '<=' ] ) ) {
                        $cond[]     = sprintf( '`%s` %s %s', $value_type, $value[0], (int) $value[1] );
                        $eConds[$value_type] = $value;
                    } else if( in_array( $value[0], [ 'IN', 'NOT IN' ] ) && is_array( $value[1] ) ) {
                        $cond[]     = sprintf( '`%s` %s (%s)', $value_type, $value[0], implode( ',', array_map( 'intval', $value[1] ) ) );
                        $eConds[$value_type] = $value;
                    } else continue 2;
                break;

                case 'value_str':
                    if( $value[0] == 'LIKE' ) {
                        $cond[]     = sprintf( '`%s` %s "%s"', $value_type, $value[0], '%' . $this->dbp( $value[1] ) . '%' );
                        $eConds[$value_type] = $value;
                    }
                break;

                case 'value_date':
                    if( in_array( $value[0], [ '=', '>', '<', '>=', '<=' ] ) ) {
                        switch( $value[2] ?? '' ) {
                            case 'FROM_UNIXTIME':
                                $cond[] = sprintf( '`%s` %s %s', $value_type, $value[0], ' FROM_UNIXTIME(' . $this->dbp( $value[1] ) . ')' );
                            break;

                            default:
                                $cond[] = sprintf( '`%s` %s %s', $value_type, $value[0], '"' . $this->dbp( $value[1] ) . '"' );
                        }

                        $eConds[$value_type] = $value;
                    }
                break;
            }
        }

        if( empty( $cond ) ) return ;

        $this->answersCond[$type][] = '(question = ' . $question . ' AND ' . implode( ' AND ', $cond ) . ')';
        $this->currentOptions['q'][$question][$type][] = $eConds;

        return $this;
    }

    public function setTimePeriodString( string $period ) {
        switch( $period ) {
            case '12hours':
                $this->answersCond[10]['time_period'] = 'value_date >= DATE_ADD(NOW(), INTERVAL -12 HOUR)';
                $this->currentOptions['d_val'] = [ 'from' => time(), 'to' => strtotime( '-12 hours' ) ];
            break;

            case '24hours':
                $this->answersCond[10]['time_period'] = 'value_date >= DATE_ADD(NOW(), INTERVAL -24 HOUR)';
                $this->currentOptions['d_val'] = [ 'from' => time(), 'to' => strtotime( '-24 hours' ) ];
            break;

            case '7days':
                $this->answersCond[10]['time_period'] = 'value_date >= DATE_ADD(NOW(), INTERVAL -7 DAY)';
                $this->currentOptions['d_val'] = [ 'from' => time(), 'to' => strtotime( '-7 days' ) ];
            break;

            case '30days':
                $this->answersCond[10]['time_period'] = 'value_date >= DATE_ADD(NOW(), INTERVAL -30 DAY)';
                $this->currentOptions['d_val'] = [ 'from' => time(), 'to' => strtotime( '-30 days' ) ];
            break;
        }

        return $this;
    }

    public function setTimePeriodRange( array $range ) {
        if( $range >= 2 ) {
            $this->answersCond[10]['time_period_range'] = '(value_date >= FROM_UNIXTIME(' . $this->dbp( $range[0] ) . ') AND value_date <= FROM_UNIXTIME(' . $this->dbp( $range[1] ) . '))';
            $this->currentOptions['d_val'] = [ 'from' => $range[0], 'to' => $range[1] ];
        }

        return $this;
    }

    public function setCollectors( array $collectors ) {
        $count = count( $collectors );

        if( $count > 1 ) {
            $this->answersCond[4]['collector'] = '`value` IN (' . implode( ',', array_map( 'intval', $collectors ) ) . ')';
        } else if( $count == 1 ) {
            $this->answersCond[4]['collectors'] = '`value` = ' . (int) current( $collectors );
        }

        $this->currentOptions['col'] = array_values( $collectors );

        return $this;
    }

    public function setPoints( int $value1, int $value2 ) {
        $this->answersCond[5]['points'] = '(`value` >= ' . $value1 . ' AND `value` <= ' . $value2 . ')';
        $this->currentOptions['points'] = [ 'min' => $value1, 'max' => $value2 ];
        return $this;
    }

    public function setCountries( array $countries ) {
        $this->answersCond[6]['country'] = '`value_str` IN (' . implode( ',', array_map( function( $country ) {
            return '"' . $this->dbp( $country ) . '"';
        }, $countries ) ) . ')';
        $this->currentOptions['country'] = array_values( $countries );
    }

    public function setResponseTime( int $seconds, string $sign = '>' ) {
        if( in_array( $sign, [ '=', '>', '<', '>=', '<=' ] ) ) {
            $this->answersCond[7]['response_time_' . $sign] = implode( ' ', [ '`value`', $sign, $seconds ] );
            $this->currentOptions['r_time'][$sign] = $seconds;
        }

        return $this;
    }

    public function setTrackId( string $trackStr, string $find = 'anywhere' ) {
        switch( $find ) {
            case 'anywhere':
                $this->answersCond[8][] = implode( ' ', [ 'value_str', 'LIKE', '"%' . $this->dbp( $trackStr ) . '%"' ] );
                $this->currentOptions['tr_id'][] = $trackStr;
            break;
        }

        return $this;
    }

    public function setVariable( string $variable, string $find = 'anywhere') {
        $this->answersCond[9][] = implode( ' ', [ 'value_str', 'LIKE', '"%' . $this->dbp( $variable ) . '%"' ] );
        $this->currentOptions['var'][] = $variable;
        return $this;
    }

    public function addLabels( array $labels ) {
        $this->labels = $labels;
        return $this;
    }
    
    public function filtersFromArray( array $filters ) {
        // show
        if( !empty( $filters['show'] ) && is_array( $filters['show'] ) ) {
            $this->currentOptions['show'] = array_merge( ...array_values( $filters['show']  ) );
        }

        // collectors
        if( !empty( $filters['collectors'] ) ) {
            $this->setCollectors( $filters['collectors'] );
        }

        // answer
        if( !empty( $filters['q'] ) ) {
            foreach( $filters['q'] as $type => $q ) {
                $question = key( $q );
                switch( $type ) {
                    case 'net_prom':
                        $value = current( $q );
                        foreach( $value as $v ) {
                            switch( $v ) {
                                case 1:
                                    $this->setValue( $question, [ 'value' => [ 'IN', range( 1, 6 ) ] ] );
                                break;

                                case 2:
                                    $this->setValue( $question, [ 'value' => [ 'IN', [ 7, 8 ] ] ] );
                                break;

                                case 3:
                                    $this->setValue( $question, [ 'value' => [ 'IN', [ 9, 10 ] ] ] );
                                break;
                            }
                        }
                    break;
                            
                    case 'checkbox':
                        $value = current( $q );
                        $this->setValue( $question, [ 'value' => [ '=', $value ] ] );
                    break;
        
                    case 'slider':
                        $value = current( $q );
                        if( !empty( $value['min'] ) )
                        $this->setValue( $question, [ 'value' => [ '>=', $value['min'] ] ] );
                        if( !empty( $value['max'] ) )
                        $this->setValue( $question, [ 'value' => [ '<=', $value['max'] ] ] );
                    break;
        
                    case 'matrix_rs':
                        $value = current( $q );
                        foreach( $value as $label => $stars ) {
                            $this->setValue( $question, [ 'value' => [ '=', $label ], 'value2' => [ 'IN', $stars ] ] );
                        }
                    break;
        
                    case 'matrix_mc':
                        $value = current( $q );
                        foreach( $value as $label => $col ) {
                            $this->setValue( $question, [ 'value' => [ '=', $label ], 'value2' => [ 'IN', $col ] ] );
                        }
                    break;
        
                    case 'matrix_dd':
                        $value = current( $q );
                        foreach( $value as $label => $cols ) {
                            foreach( $cols as $col => $opts ) {
                                $this->setValue( $question, [ 'value' => [ '=', $label ], 'value2' => [ '=', $col ], 'value3' => [ 'IN', $opts ] ] );
                            }
                        }
                    break;
        
                    case 'srating':
                    case 'multi':
                    case 'checkboxes':
                    case 'dropdown':
                    case 'imagec':
                        $value = current( $q );
                        $this->setValue( $question, [ 'value' => [ 'IN', $value ] ] );
                    break;
        
                    case 'date':
                        $value = current( $q );
                        if( !empty( $value['from'] ) )
                        $this->setValue( $question, [ 'value_date' => [ '>=', strtotime( $value['from'] ), 'FROM_UNIXTIME' ] ], 3 );

                        if( !empty( $value['to'] ) )
                        $this->setValue( $question, [ 'value_date' => [ '<=', strtotime( $value['to'] ), 'FROM_UNIXTIME' ] ], 3 );
                    break;
        
                    case 'text':
                    case 'textarea':
                    case 'contact':
                        $value = current( $q );
                        if( !empty( $value['str'] ) )
                        $this->setValue( $question, [ 'value_str' => [ 'LIKE', $value['str'] ] ], 2 );
                    break;

                    case 'opr':
                        $value = current( $q );
                        $value = array_filter( $value );
                        foreach( $value as $value1 => $value2 ) {
                            if( ( list( $opr, $val ) = explode( '|', $value2 ) ) && in_array( $opr, [ '=', '<', '>', '>=', '<=' ] ) ) {
                                $this->setValue( $question, [ 'value' => [ '=', $value1 ], 'value2' => [ $opr, $val ] ] );
                            }
                        }
                    break;
                }
            }
        }

        // date
        if( !empty( $filters['date_range']['type'] ) ) {
            switch( $filters['date_range']['type'] ) {
                case '12hours':
                    $this->setTimePeriodRange( [ strtotime( '-12 hours' ), time() ] );
                break;

                case '24hours':
                    $this->setTimePeriodRange( [ strtotime( '-24 hours' ), time() ] );
                break;

                case '7days':
                    $this->setTimePeriodRange( [ strtotime( '-7 days' ), time() ] );
                break;

                case '30days':
                    $this->setTimePeriodRange( [ strtotime( '-30 days' ), time() ] );
                break;

                case 'range':
                    $range = $filters['date_range'];
                    if( !empty( $range['from'] ) || !empty( $range['to'] ) )
                    $this->setTimePeriodRange( [ user_time( $range['from'] )->getTimestamp(), user_time( $range['to'] )->getTimestamp() ] );
                break;
            }
        }

        // points
        if( !empty( $filters['points'] ) ) {
            $points = $filters['points'];
            if( !empty( $points['min'] ) || !empty( $points['max'] ) ) {
                $this->setPoints( (int) $points['min'], (int) $points['max'] );
            }
        }

        // countries
        if( !empty( $filters['countries'] ) ) {
            $this->setCountries( $filters['countries'] );
        }

        // response time
        if( !empty( $filters['response_time'] ) ) {
            $response_time = $filters['response_time'];
            if( !empty( $response_time['val'] ) && !empty( $response_time['int'] ) ) {
                $amount     = (int) $response_time['val'];
                $interval   = $response_time['int'];

                switch( $response_time['opr'] ) {
                    case 'g':
                        switch( $interval ) {
                            case 's': $this->setResponseTime( $amount, '>=' ); break;
                            case 'm': $this->setResponseTime( ( $amount * 60 ), '>=' ); break;
                            case 'h': $this->setResponseTime( ( $amount * 3600 ), '>=' ); break;
                        }
                    break;

                    case 'l':
                        switch( $interval ) {
                            case 's': $this->setResponseTime( $amount, '<=' ); break;
                            case 'm': $this->setResponseTime( ( $amount * 60 ), '<=' ); break;
                            case 'h': $this->setResponseTime( ( $amount * 3600 ), '<=' ); break;
                        }
                    break;
                }
            }
        }

        // tracking id
        if( !empty( $filters['tids'] ) ) {
            foreach( $filters['tids'] as $tracking_id ) {
                if( !empty( $tracking_id ) )
                $this->setTrackId( $tracking_id );
            }
        }

        // variables
        if( !empty( $filters['variables'] ) ) {
            array_pop( $filters['variables'] );
            foreach( $filters['variables'] as $variable ) {
                if( !empty( $variable['text'] ) )
                $this->setVariable( $variable['text'] );
            }
        }

        // labels
        if( !empty( $filters['label'] ) ) {
            $this->addLabels( $filters['label'] );
        }

        return $this;
    }

    public function filtersFromOptions( array $options ) {
        // collectors
        if( !empty( $options['col'] ) ) {
            $this->setCollectors( $options['col'] );
        }

        // answer
        if( !empty( $options['q'] ) ) {
            foreach( $options['q'] as $question => $q ) {
                foreach( $q as $type => $values ) {
                    foreach( $values as $value ) {
                        $this->setValue( $question, $value, $type );
                    }
                }
            }
        }

        // date
        if( !empty( $options['d_val'] ) ) {
            $val = $options['d_val'];
            if( isset( $val['from'] ) && isset( $val['to'] ) )
            $this->setTimePeriodRange( array_values( $val ) );
        }

        // points
        if( !empty( $options['points'] ) ) {
            $points = $options['points'];
            if( !empty( $points['min'] ) || !empty( $points['max'] ) ) {
                $this->setPoints( (int) $points['min'], (int) $points['max'] );
            }
        }

        // countries
        if( !empty( $options['country'] ) ) {
            $this->setCountries( $options['country'] );
        }

        // response time
        if( !empty( $options['r_time'] ) ) {
            $rtime = $options['r_time'];
            $this->setResponseTime( (int) current( $rtime ), key( $rtime ) );
        }

        // tracking id
        if( !empty( $options['tr_id'] ) ) {
            foreach( $options['tr_id'] as $tracking_id ) {
                $this->setTrackId( $tracking_id );
            }
        }

        // variables
        if( !empty( $options['var'] ) ) {
            foreach( $options['var'] as $variable ) {
                $this->setVariable( $variable );
            }
        }
    }

    public function setPeriodLimit( $period ) {
        if( !is_numeric( $period ) )
        $period = strtotime( $period );
        $this->answersCond[10]['time_period'] = 'value_date <= FROM_UNIXTIME(' . $period . ')';
        return $this;
    }

    public function getCurrentOptions() {
        return $this->currentOptions;
    }

}
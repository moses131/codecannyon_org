<?php

namespace util;

class db extends query_util {

    protected $conditions   = [];
    private $b_conditions   = [];
    private $use_multiple   = false;
    private $def_operator   = 'AND';

    function __construct() {
        global $db, $filters, $actions;

        $this->db       = $db;
        $this->filters  = $filters;
        $this->actions  = $actions;
    }

    protected function table( string $str ) {
        return DB_TABLE_PREFIX . $str;
    }

    protected function dbp( string $str ) {
        return $this->db->real_escape_string( $str );
    }

    
    protected function dbpa( array $arr ) {
        return implode( ',', array_map( function( $v ) {
            return ( is_int( $v ) ? (int) $v : '\'' . $this->dbp( $v ) . '\'' );
        }, $arr ) );
    }

    protected function buildcond( array $cnds = [] ) {
        $cond   = [];
        $cnds   = array_values( $cnds );
        $count  = count( $cnds );
        $last   = $count - 1;
        $next   = 1;

        for( $i = 0; $i < $count; $i++ ) {
            if( $i > 0 && !is_string( $cnds[$i-1] ) ) {
                if( !is_string( $cnds[$i] ) ) {
                    $cond[] = $this->def_operator;
                } else if( is_string( $cnds[$i] ) && ( isset( $cnds[$next] ) && is_array( $cnds[$next] ) ) ) {
                    $cond[] = in_array( $cnds[$i], [ 'AND', 'OR', '&&', '||' ] ) ? $cnds[$i] : 'AND';
                }
            }
            if( is_array( $cnds[$i] ) ) {
                if( is_string( current( $cnds[$i] ) ) ) {
                    if( count( $cnds[$i] ) === 3 ) {
                        $o = $this->dboperator( $cnds[$i][1], $cnds[$i][2] );
                        if( $o ) {
                            $cond[] = $this->dbp( $cnds[$i][0] ) . ' ' . $o;
                        }
                    } else if( count( $cnds[$i] ) === 1 ) {
                        $cond[] = $cnds[$i][0];
                    }
                } else {
                    $cond[] = '(' . implode( ' ', $this->buildcond( $cnds[$i] ) ) . ')';
                }
            }
            $next++;
        }

        return $cond;
    }

    private function dbcheck_function( $value ) {
        if( is_array( $value ) ) {
            switch( $value[0]  ) {
                case 'FROM_UNIXTIME':
                return 'FROM_UNIXTIME("' . $this->dbp( $value[1] ) . '")';
                break;
                
                case 'DATE_ADD':
                return 'DATE_ADD(' . $this->dbp( $value[1] . ', INTERVAL ' . (int) $value[2] ) . ' ' . $this->dbp( $value[3] ) . ')';
                break;

                case 'NOW()':
                return 'NOW()';
                break;

                case 'NOQ':
                return $this->dbp( $value[1] );
                break;
            }
        }
        return '"' . $this->dbp( $value ) . '"';
    }

    private function dboperator( string $operator, $value ) {
        if( in_array( $operator, [ '=', '>', '<', '!=', '>=', '<=' ] ) ) {
            return $operator . ' ' . $this->dbcheck_function( $value );
        } else if( in_array( $operator, [ 'IN', 'NOT IN' ] ) ) {
            return $operator . ' (' . $this->dbpa( $value ) . ')';
        } else if( in_array( $operator, [ 'LIKE' ] ) ) {
            return $operator . ' "' . $this->dbp( $value ) . '"';
        } else if( in_array( $operator, [ 'IS NULL', 'IS NOT NULL' ] ) ) {
            return $operator;
        } else if( strcasecmp( $operator, 'against' ) === 0 ) {
            return 'AGAINST ("' . $this->dbp( $value ) . '" IN BOOLEAN MODE)';
        }

        return false;
    }

    protected function addCondition( string $index, array $condition ) {
        if( $this->use_multiple ) {
            $this->conditions[] = $condition;
        } else {
            $this->conditions[$index] = $condition;
        }
        return $this;
    }

    public function multiIndex( bool $use_index ) {
        $this->use_multiple = $use_index;
        return $this;
    }

    public function changeOperator( string $operator ) {
        if( in_array( $operator, [ 'AND', 'OR', '&&', '||' ] ) ) {
            $this->def_operator = $operator;
        }
        return $this;
    }

    public function setOperator( string $operator ) {
        if( in_array( $operator, [ 'AND', 'OR', '&&', '||' ] ) ) {
            $this->conditions[] = $operator;
        }
        return $this;       
    }

    public function condition( array $new_conds, bool $grouped = false ) {
        $this->conditions   = array_filter( array_merge( $this->conditions, ( $grouped ? [ $new_conds ] : $new_conds ) ) );
        return $this;
    }
    
    public function removeCondition( string $index ) {
        if( isset( $this->conditions[$index] ) ) {
            unset( $this->conditions[$index] );
        }
        return $this;
    }

    public function resetConditions() {
        $this->conditions   = [];
        return $this;
    }

    public function breakConditions() {
        $this->b_conditions = $this->conditions;
        $this->conditions   = [];
        return $this;
    }

    public function resumeConditions( bool $grouped = false ) {
        $this->conditions   = array_merge( $this->b_conditions, ( $grouped ? [ $this->conditions ] : $this->conditions ) );
        return $this;
    }

    public function getConditions() {
        return implode( ' ', $this->buildcond( $this->conditions ) );
    }

    protected function finalCondition( string $before_str = ' WHERE ', string $after_str = '' ) {
        $cond = $this->buildcond( $this->conditions );
        if( !empty( $cond ) ) {
            return $before_str . implode( ' ', $cond ) . $after_str;
        }
    }

    public function getField( string $field ) {
        if( isset( $this->info->$field ) ) {
            return $this->info->$field;
        }

        return false;
    }

    public function select( array $cols ) {
        $this->select = implode( ', ', array_map( [ $this, 'dbp' ], $cols ) );
        return $this;
    }

    public function selectKey( string $key = NULL ) {
        $this->selectKey = $key;
        return $this;
    }

    public function db_action( string $statement, string $table, array $columns, array $where, int $limit = 20 ) {
        switch( $statement ) {
            case 'UPDATE':
                $params = [];
                $query  = 'UPDATE ';            
                $query  .= $this->table( $table );
                if( !empty( $columns ) ) {
                    $query .= ' SET ';
                    foreach( $columns as $colName => $colValue ) {
                        $query .= ' ' . $this->dbp( $colName ) . ' = ';
                        if( in_array( $colValue[0], [ 's!', 'i!', 'd!', 'b!' ] ) ) {
                            $params[]   = [ rtrim( $colValue[0], '!' ), $colValue[1] ];
                            $query      .= '?';
                        } else {
                            $query      .= $this->dbp( $colValue[0] );
                        }
                    }
                }

                if( !empty( $where ) ) {
                    $query      .= ' WHERE ';
                    $where_arr  = [];
                    foreach( $where as $colName => $colValue ) {
                        $where_str  = ' ' . $this->dbp( $colName ) . ' = ';
                        if( in_array( $colValue[0], [ 's!', 'i!', 'd!', 'b!' ] ) ) {
                            $params[]   = [ rtrim( $colValue[0], '!' ), $colValue[1] ];
                            $where_str  .= '?';
                        } else {
                            $where_str  .= $this->dbp( $colValue[0] );
                        }
                        $where_arr[]    = $where_str;
                    }
                    $query .= implode( ' AND ', $where_arr );
                }

                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                if( !empty( $params ) )
                $stmt->bind_param( implode( array_column( $params, 0 ) ), ...array_column( $params, 1 ) );
                $e = $stmt->execute();
                $stmt->close();

                return $e;
            break;
        }
    }

}
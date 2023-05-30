<?php

namespace dev;

class filters {

    private static $filters = [];

    public function add_filter( $filter, $value, int $priority = 99 ) {
        if( is_array( $filter ) ) {
            foreach( $filter as $f ) {
                self::add_filter( $f, $value, $priority );
            }
            return ;
        }

        if( !isset( self::$filters[$filter] ) ) {
            self::$filters[$filter] = [];
        }

        self::$filters[$filter][] = [ 'priority' => $priority, 'value' => $value ];
    }
    
    public function do_filter( string $filter, $default, ...$atts ) {
        if( isset( self::$filters[$filter] ) ) {
            uasort( self::$filters[$filter], function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            foreach( self::$filters[$filter] as $ftr ) {
                $default = !is_string( $ftr['value'] ) && is_callable( $ftr['value'] ) ? call_user_func( $ftr['value'], $filter, $default, ...$atts ) : $ftr['value'];
            }
        }

        return ( !is_string( $default ) && is_callable( $default ) ? call_user_func( $default, ...$atts ) : $default );
    }

    public function do_filter_string( string $filter, $default ) {
        if( isset( self::$filters[$filter] ) ) {
            uasort( self::$filters[$filter], function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            foreach( self::$filters[$filter] as $ftr ) {
                $default = $ftr['value'];
            }
        }
        return $default;
    }

    public function value( string $filter, $default, ...$atts ) {
        if( isset( self::$filters[$filter] ) ) {
            uasort( self::$filters[$filter], function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            $value      = current( self::$filters[$filter] );
            $default    = !is_string( $value['value'] ) && is_callable( $value['value'] ) ? call_user_func( $value['value'], $filter, $default, ...$atts ) : $value['value'];
        }
        return ( !is_string( $default ) && is_callable( $default ) ? call_user_func( $default, ...$atts ) : $default );
    }

    public function filter_exists( string $filter ) {
        if( isset( self::$filters[$filter] ) ) {
            return true;
        }
        return false;
    }

    public function get_filters( string $filter ) {
        if( isset( self::$filters[$filter] ) ) {
            return self::$filters[$filter];
        }
        return false;
    }

    public function change_filter( string $filter, $value, int $priority = 99 ) {
        self::remove_filter( $filter );
        self::add_filter( $filter, $value, $priority );
    }

    public function remove_filter( sring $filter ) {
        if( isset( self::$filters[$filter] ) )
        unset( self::$filters[$filter] );
    }

}
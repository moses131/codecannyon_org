<?php

namespace dev;

class actions {

    private static $actions = [];

    public function add_action( string $action, callable $value, int $priority = 99 ) {

        if( is_array( $action ) ) {
            foreach( $action as $f ) {
                self::add_action( $a, $value, $priority );
            }
            return ;
        }

        if( !isset( self::$actions[$action] ) ) {
            self::$actions[$action] = [];
        }

        self::$actions[$action][] = [ 'priority' => $priority, 'value' => $value ];

    }

    public function do_action( string $action, ...$atts ) {

        if( isset( self::$actions[$action] ) ) {

            uasort( self::$actions[$action], function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            $export = '';
            
            foreach( self::$actions[$action] as $act ) {
                $export .= is_callable( $act['value'] ) ? call_user_func( $act['value'], $action, ...$atts ) : $act['value'];
            }

            return $export;

        }

    }

    public function action_exists( string $action ) {

        if( isset( self::$actions[$action] ) ) {
            return true;
        }

        return false;

    }

    public function change_action( string $action, callable $value, int $priority = 99 ) {

        self::remove_action( $filter );
        self::add_action( $action, $value, $priority );

    }

    public function remove_action( string $action ) {
        
        if( isset( self::$actions[$action] ) )

        unset( self::$actions[$action] );

    }

}
<?php

namespace dev;

class routes {

    private static $routes          = [];
    private static $admin_routes    = [];
    private $last_route;

    public function add_route( $route, $callback, int $priority = 99 ) {

        if( is_array( $route ) ) {
            foreach( $route as $r ) {
                self::add_route( $r, $callback, $priority );
            }
            return ;
        }

        self::$routes[$route] = [ 'priority' => $priority, 'value' => $callback ];

    }

    public function add_admin_route( $route, $callback, int $priority = 99 ) {

        if( is_array( $route ) ) {
            foreach( $route as $r ) {
                self::add_admin_route( $r, $callback, $priority );
            }
            return ;
        }

        self::$admin_routes[$route] = [ 'priority' => $priority, 'value' => $callback ];

    }
    
    public function do_route( string $route, ...$atts ) {

        if( isset( self::$routes[$route] ) ) {

            return call_user_func( self::$routes[$route]['value'], $route, ...$atts );

        }

        return false;

    }

    public function do_admin_route( string $route, ...$atts ) {

        if( isset( self::$admin_routes[$route] ) ) {

            return call_user_func( self::$admin_routes[$route]['value'], $route, ...$atts );

        }

        return false;

    }

    public function check_route( string $path, ...$atts ) {

        if( !empty( self::$routes ) ) {
            uasort( self::$routes, function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            foreach( self::$routes as $route => $options ) {
                if( preg_match( $route, $path, $matches ) ) {
                    $this->last_route = [ 
                        'callback'  => $options['value'], 
                        'params'    => [ $route, $matches, $atts ] 
                    ];
                    return true;
                }
            }
        }

        return false;

    }

    public function check_admin_route( string $path, ...$atts ) {

        if( !empty( self::$admin_routes ) ) {
            uasort( self::$admin_routes, function( $a, $b ) {
                if( (double) $a['priority'] === (double) $b['priority'] ) return 0;
                return ( (double) $a['priority'] < (double) $b['priority'] ? -1 : 1 );
            } );

            foreach( self::$admin_routes as $route => $options ) {
                if( preg_match( $route, $path, $matches ) ) {
                    $this->last_route = [ 
                        'callback'  => $options['value'], 
                        'params'    => [ $route, $matches, $atts ] 
                    ];
                    return true;
                }
            }
        }

        return false;

    }

    public function do_last_route() {
        return call_user_func_array( $this->last_route['callback'], $this->last_route['params'] );
    }

    public function route_exists( string $route ) {

        if( isset( self::$routes[$route] ) ) {
            return true;
        }

        return false;

    }

    public function admin_route_exists( string $route ) {

        if( isset( self::$admin_routes[$route] ) ) {
            return true;
        }

        return false;

    }

    public function remove_route( $route ) {
        
        if( is_array( $route ) ) {
            foreach( $route as $r ) {
                self::remove_route( $r );
            }
            return ;
        }

        if( isset( self::$routes[$route] ) )

        unset( self::$routes[$route] );

    }

    public function remove_admin_route( $route ) {
        
        if( is_array( $route ) ) {
            foreach( $route as $r ) {
                self::remove_admin_route( $r );
            }
            return ;
        }

        if( isset( self::$admin_routes[$route] ) )

        unset( self::$admin_routes[$route] );

    }

}
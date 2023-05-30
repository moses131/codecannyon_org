<?php

namespace dev;

class ajax {

    private static $ajax_calls  = [];
    private static $ajax_url;

    function __construct() {
        self::$ajax_url = esc_url( site_url( [ 'ajax' ] ) );
    }

    public function add_call( $call, $value, ...$atts ) {
        if( is_array( $call ) ) {
            foreach( $call as $c ) {
                self::add_call( $c, $value, ...$atts );
            }
            return ;
        }
        self::$ajax_calls[$call] = $value;
    }

    public function do_call( string $call, ...$atts ) {
        if( isset( self::$ajax_calls[$call] ) && is_callable( self::$ajax_calls[$call] ) ) {
            actions()->do_action( 'ajax-call-' . $call, ...$atts );
            return call_user_func( self::$ajax_calls[$call], $call, ...$atts );
        }
        return false;
    }

    public function do_call_ind( string $call, ...$atts ) {
        require_once implode( '/', [ DIR, INCLUDES_DIR, 'site', 'ajax.php' ] );
        return $this->do_call( $call, ...$atts );
    }

    public function set_ajax_url( string $url ) {
        self::$ajax_url = $url;
    }

    public function get_call_url( string $call, array $atts = [] ) {
        actions()->do_action( 'ajax-url-' . $call, $atts );
        return self::$ajax_url . '?' . http_build_query( array_merge( [ 'token' => \util\security::csrf_token(), 'action' => $call ], $atts ), '', '&' );
    }

}
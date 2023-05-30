<?php

namespace site;

class payments {

    private $methods;

    function __construct() {
        $this->methods = filters()->do_filter( 'deposit-methods' );
    }

    public function setMethod( string $method ) {
        if( !isset( $this->methods[$method] ) ) {
            return false;
        }

        return call_user_func( $this->methods[$method]['class'] );
    }

    public static function paypal( array $opts = [] ) {
        return new \services\PayPal( $opts );
    }

    public static function stripe( array $opts = [] ) {
        return new \services\Stripe( $opts );
    }
    
}
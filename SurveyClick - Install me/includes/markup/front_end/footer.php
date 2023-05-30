<?php

namespace markup\front_end;

class footer {

    function __construct() {

        actions()->do_action( 'before_init_footer' );

        echo "\n" . implode( "\n", filters()->do_filter( 'footer', [ $this, 'markup' ] ) );

    }

    public function markup() {

        $lines = [];

        if( ( $footer_lines = filters()->do_filter( 'in_footer', [] ) ) ) {
            $lines += $footer_lines;
        }

        return $lines;

    }

}
<?php

namespace markup\back_end;

class footer {

    function __construct() {

        actions()->do_action( 'before_init_admin_footer' );

        echo "\n" . implode( "\n", filters()->do_filter( 'admin_footer', [ $this, 'markup' ] ) );

    }

    public function markup() {

        $lines = [];

        if( ( $footer_lines = filters()->do_filter( 'in_admin_footer', [] ) ) ) {
            $lines += $footer_lines;
        }
            
        return $lines;

    }

}
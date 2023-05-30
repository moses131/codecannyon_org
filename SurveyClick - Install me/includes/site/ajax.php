<?php

if( isset( $_GET['action'] ) && 
    isset( $_GET['token'] ) && 
    \util\security::check_csrf( $_GET['token'] ) ) {

        if( isset( $_GET['bl'] ) ) {
            $file = implode( '/', [ DIR, INCLUDES_DIR, 'site', 'ajax', 'ajax_help', $_GET['bl'] . '.php' ] );
            if( file_exists( $file ) )
            require_once $file;
        }

    echo ajax()->do_call( $_GET['action'] );
}
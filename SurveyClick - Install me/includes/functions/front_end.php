<?php

// Header
function getHeader() {
    new \markup\front_end\header;
}

// Footer
function getFooter() {
    new \markup\front_end\footer;
}

// Using ajax
if( filters()->do_filter( 'use_ajax', true ) ) {
    // include scripts url
    filters()->add_filter( 'in_footer', function( $filter, $lines ) {
        $lines['script_utils']  = '<script>var utils = {ajax_url: \'' . esc_url( site_url( [ 'ajax' ] ) ) . '?token=' . md5( $_SESSION['csrf_token'] ) . '\'};</script>';
        $lines['ajax_scripts']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'ajax.js' ] ) ) . '"></script>';
        return $lines;
    }, 10 );
}
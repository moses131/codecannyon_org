<?php

require 'includes/functions.php';

filters()->add_filter( 'site_languages', function( $filter, $langs ) {
    unset( $langs['ro_RO'] );
    $langs['nb_NO'] = [ 
        'short'     => 'NO',
        'name'      => 'Norwegian',
        'name_en'   => 'Norwegian', 
        'locale_e'  => 'nb_NO',
        'locale'    => 'nb_NO.utf8',
        'direction' => 'ltr'
    ];
    return $langs;
} );

// Build the header
filters()->add_filter( 'in_header', function( $filter, $header = [] ) {
    $header['fontawesome_css']  = '<link href="' . assets_url( 'css/fontawesome-all.min.css' ) . '" media="all" rel="stylesheet">';
    $header['style_css']        = '<link href="' . theme_url( 'assets/css/style.css' ) . '" media="all" rel="stylesheet">';
    $header['responsive_css']   = '<link href="' . theme_url( 'assets/css/responsive.css' ) . '" media="all" rel="stylesheet">';
    if( getLangDirection() == 'rtl' )
    $header['rtl_css']          = '<link href="' . theme_url( 'assets/css/rtl.css', true ) . '" media="all" rel="stylesheet">';
    $header['google_font']      = '
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">';
    $header['jquery_js']        = '<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';
    return $header;
}, 99 );

// Build the footer
filters()->add_filter( 'in_footer', function( $filter, $header = [] ) {
    $header['functions_js']     = '<script src="' . theme_url( 'assets/js/functions.js' ) . '"></script>';
    return $header;
}, 99 );

/** Load theme classes */
spl_autoload_register( function ( $cn ) {
    $type   = strstr( $cn, '\\', true );
    $cn     = str_replace( '\\', '/', $cn );

    if( $type == 'theme' ) {
        $file = __DIR__ . substr( $cn, strpos( $cn, '/' ) ) . '.php';
        if( file_exists( $file ) ) 
        require_once $file;
    }
} );

// Text domain
developer()->addTextDomain( 'def-theme', __DIR__ . '/locale' );
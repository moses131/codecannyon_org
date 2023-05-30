<?php

filters()->add_filter( 'in_survey_header', function( $filert, $header = [] ) {
    $header['fontawesome_css']  = '<link href="' . assets_url( 'css/fontawesome-all.min.css' ) . '" media="all" rel="stylesheet" />';
    $header['style_css']        = '<link href="' . survey_template_url( 'assets/css/style.css' ) . '" media="all" rel="stylesheet" />';
    $header['responsive_css']   = '<link href="' . survey_template_url( 'assets/css/responsive.css' ) . '" media="all" rel="stylesheet" />';
    if( getLangDirection() == 'rtl' )
    $header['rtl_css']          = '<link href="' . survey_template_url( 'assets/css/rtl.css', true ) . '" media="all" rel="stylesheet">';
    $header['main_font']        = '<link href="//fonts.googleapis.com/css?family=Quicksand:500,600,700" rel="stylesheet">';
    $header['jquery_js']        = '<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';
    return $header;
}, 99 );

filters()->add_filter( 'in_survey_footer', function( $filert, $header = [] ) {
    $header['functions_js']     = '<script src="' . survey_template_url( 'assets/js/functions.js' ) . '"></script>';
    return $header;
}, 99 );
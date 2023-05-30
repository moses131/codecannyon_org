<?php

function t( string $str, string $textdomain = 'main', int $count = NULL, string $plural = NULL ) {
    textdomain( $textdomain );
    if( $plural != NULL && $count != NULL )
    return ngettext( $str, $plural, $count );
    return gettext( $str );
}

function t_e( string $str, string $textdomain = 'main', int $count = NULL, string $plural = NULL ) {
    echo t( $str, $textdomain, $count, $plural );
}
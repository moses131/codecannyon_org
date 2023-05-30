<?php

filters()->add_filter( 'get_box', function( $filter, $box ) {
    require_once 'filters_help/boxes.php';

    new boxes;

    $boxes      = [];
    $options    = \util\etc::formFilterOptions();
    $boxes      = filters()->do_filter( 'boxes_' . me()->viewAs, [] );

    if( isset( $boxes[$box] ) ) {
        if( is_callable( $boxes[$box] ) )
        return call_user_func( $boxes[$box], $options );
        return $boxes[$box];
    }
});
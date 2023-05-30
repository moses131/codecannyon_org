<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

filters()->add_filter( 'in_admin_header', function( $filert, $header = [] ) {
    $header['fontawesome_css']  = '<link href="' . assets_url( 'css/fontawesome-all.min.css', true ) . '" media="all" rel="stylesheet">';
    $header['style_css']        = '<link href="' . admin_url( 'assets/css/style.css', true ) . '" media="all" rel="stylesheet">';
    $header['responsive_css']   = '<link href="' . admin_url( 'assets/css/responsive.css', true ) . '" media="all" rel="stylesheet">';
    if( getLangDirection() == 'rtl' )
    $header['rtl_css']          = '<link href="' . admin_url( 'assets/css/rtl.css', true ) . '" media="all" rel="stylesheet">';
    $header['google_font']      = '
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">';
    $header['jquery_js']        = '<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';
    $header['plugins_js']       = '<script src="' . admin_url( 'assets/js/plugins.js', true ) . '"></script>';
    $header['functions_js']     = '<script src="' . admin_url( 'assets/js/functions.js', true ) . '"></script>';
    return $header;
}, 10 );

require_once 'extends/ajax.php';
require_once 'extends/content_helper.php';
require_once 'includes/ajax/user.php';
require_once 'includes/ajax/visitor.php';
require_once 'includes/ajax/admin.php';
require_once 'includes/ajax/shop.php';

function viewAsSwitch() {
    if( isset( $_GET['viewAs'] ) ) {
        setcookie( 'viewAs', (int) $_GET['viewAs'], strtotime( '+1 year' ), '/' );
        header( 'Location: ' . admin_url() );
        die;
    }
}

function notLogged() {
    if( !me() ) {
        header( 'Location:' . admin_url( 'login' ) );
        die;
    }
}

if( me() ) {
    if( me()->viewAs == 'respondent' )
    if( !me()->isVerified() )
    filters()->add_filter( 'respondent_head_left_nav', function( $f, $links ) {
        $links['upgrade'] = [
            'type'      => 'link', 
            'url'       => admin_url( 'upgrade' ), 
            'html_label'=> '<span class="ctab clr2 bs">' . t( 'verify your account' ) . '</span>', 
            'position'  => 3,
            'parent_id' => false, 
            'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'identity-verification' ] ]
        ];

        return $links;
    } );
    else
    filters()->add_filter( 'surveyor_head_nav', function( $f, $links ) {
        $links['subscription'] = [
            'type'      => 'link', 
            'url'       => '#', 
            'icon'      => '<i class="far fa-star"></i>',
            'label'     => esc_html( me()->myLimits()->getPlanName() ), 
            'position'  => 1.1,
            'parent_id' => 'profile', 
            'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'my-subscription' ] ]
        ];

        return $links;
    } );

    if( me()->viewAs == 'surveyor' )
    if( me()->myLimits()->isFree() )
    filters()->add_filter( 'surveyor_head_left_nav', function( $f, $links ) {
        $links['upgrade'] = [
            'type'      => 'link', 
            'url'       => admin_url( 'upgrade' ), 
            'html_label'=> '<span class="ctab">' . t( 'upgrade' ) . '</span>', 
            'position'  => 1,
            'parent_id' => false, 
            'attrs'     => [ 'data-to' => 'upgrade' ]
        ];

        return $links;
    } );
    else
    filters()->add_filter( 'surveyor_head_nav', function( $f, $links ) {
        $links['subscription'] = [
            'type'      => 'link', 
            'url'       => '#', 
            'icon'      => '<i class="far fa-star"></i>',
            'label'     => esc_html( me()->myLimits()->getPlanName() ), 
            'position'  => 1.1,
            'parent_id' => 'profile', 
            'attrs'     => [ 'data-popup' => 'user-options', 'data-options' => [ 'action' => 'my-subscription' ] ]
        ];

        return $links;
    } );
}
<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

// Not user
if( !me() ) {
    require_once 'login.php';
    return ;

// Session requires confirmation
} else if( !me()->loginConfirmed ) {
    require_once 'verify-login.php';
    return ;

// Require email confirmation
} else if( !me()->hasEmailVerified() && ( (bool) get_option( 'femail_verify', false ) || isset( $_GET['verify'] ) ) ) {
    require_once 'verify-email.php';
    return ;

// Banned
} else if( me()->isBanned() ) {
    require_once 'banned.php';
    return ;  
}

getHeader(); ?>

<body>

<div class="main-container">

<?php require_once 'nav.php'; ?>

<div class="page-container">

<div class="page-head">

<?php $nav = new \admin\markup\nav( 'head-left' );
echo $nav->markup( 'hnav btnset ml0', 'btnset' ); ?>

<?php $nav = new \admin\markup\nav( 'head' );
echo $nav->markup( 'hnav btnset', 'btnset' ); ?>

</div>

<div class="content-container">

<?php echo showAlerts(); ?>

<div class="content">
<?php

/** START SESSION */
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/** INCLUDE CONSTANTS / CONFIG */
require_once 'config.php';

/** SET DEFAULT TIMEZONE */
date_default_timezone_set( DEFAULT_TIMEZONE );

/** CONNECT TO DATABASE */
try {
    $db = @new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

    // For older PHP versions
    if( $db->connect_errno ) {
        if( file_exists( DIR . '/install/index.php' ) )
        header( 'Location: ' .  rtrim( dirname( $_SERVER['PHP_SELF'] ), '/' ) . '/install' );
        die( 'Failed to connect to MySQL (' . $db->connect_errno . ') ' . $db->connect_error );
    }

    $db ->set_charset( DB_CHARSET );
    $db ->query( "SET time_zone='" . date( 'P' ) . "'" );
}

catch( \Exception $e ) {
    if( file_exists( DIR . '/install/index.php' ) )
    header( 'Location: ' .  rtrim( dirname( $_SERVER['PHP_SELF'] ), '/' ) . '/install' );
    die( 'Failed to connect to MySQL (' . $db->connect_errno . ') ' . $db->connect_error );
}

/** */

spl_autoload_register( function ( $cn ) {
    $type   = strstr( $cn, '\\', true );
    $cn     = str_replace( '\\', '/', $cn );

    if( $type == 'admin' ) {
        // nothing here
    } else
        if( file_exists( ( $file = DIR . '/' . INCLUDES_DIR . '/' . $cn . '.php' ) ) )
        require_once $file;
} );

/** */

$main_class = new site\main;
$main_class->init_site();

$db->close();
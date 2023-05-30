<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

if( empty( item()->params[0] ) || empty( item()->params[1] ) || empty( item()->params[2] ) ) {
    header( 'Location: ' . admin_url() ); 
    die;
}

switch( item()->params[0] ) {
    case 'CSV':
        require_once 'export/includes/export_csv.php';
        $export = new \export_csv;
        $export ->setType( item()->params[1] );
        $export ->setId( (int) item()->params[2] );
        $export ->setSettings( (array) ( $_POST['data'] ?? [] ) );
        $export = $export->export();

        if( gettype( $export ) == 'NULL' ) {
            header( 'Location: ' . admin_url() );
        }

        die( $export );
    break;

    case 'print':
        require_once 'export/includes/export_print.php';
        $print  = new \export_print;
        $print  ->setType( item()->params[1] );
        $print  ->setId( (int) item()->params[2] );
        $print  ->setSettings( (array) ( $_POST['data'] ?? [] ) );
        $print  = $print->export();

        if( gettype( $print ) == 'NULL' ) {
            header( 'Location: ' . admin_url() );
        }

        die( $print );
    break;
}
<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

if( empty( item()->params[0] ) || empty( item()->params[1] ) ) {
    header( 'Location: ' . admin_url() ); 
    die;
}

switch( item()->params[0] ) {
    case 'invoice':
        require_once 'export/includes/view_invoice.php';
        $export = new \view_invoice( (int) item()->params[1] );
        $print  = $export->view();
        if( $print ) die( $print );
    break;

    case 'receipt':
        require_once 'export/includes/view_receipt.php';
        $export = new \view_receipt( (int) item()->params[1] );
        $print  = $export->view();
        if( $print ) die( $print );
    break;
}
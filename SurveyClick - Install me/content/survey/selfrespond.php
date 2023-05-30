<?php 

// Check if the collector exists, check if it is visible and live
if( !( $response = getSelfResponse() ) ) return ; 

// Check if response exists
if( $response ) {
    switch( response()->getStatus() ) {
        // running
        case 1: return require_once survey_template_dir( 'self/complete.php' ); break;
        case 2:
        case 3: return require_once survey_template_dir( 'messages/completed.php' ); break;
    }

    // this should never happen :)
    die;
}

require_once survey_template_dir( 'survey/start.php' ); ?>
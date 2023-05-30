<?php 

// Check if the collector exists, check if it is visible and live
if( !getCollector() || !collector()->isVisible() || ( !( $response = getResponse() ) && survey()->getStatus() != 4 ) ) return ; 

require_once survey_template_dir( 'functions.php' );

// If the collector is paid, make sure it has enough budget :)
if( !$response && collector()->getType() == 1 ) {
    if( survey()->getBudget() < collector()->getCPA() ) {
        // The survey will be finished due to low balance
        survey()->actions()->updateStatus( 3 );
        return ;
    }
    
    // CtA: Login required
    if( !me() ) {
        return require_once survey_template_dir( 'fallback/login.php' );
    }

    // Profile not completed
    if( !collector()->setUserOptions( false ) ) {
        return require_once survey_template_dir( 'fallback/complete_profile.php' );
    }

    // Options cannot be verified
    if( !collector()->checkOptions() ) {
        return require_once survey_template_dir( 'fallback/not_available.php' );
    }
}

// If the collector is not paid
if( collector()->getType() == 0 ) {
    // Require password
    if( !$response && requirePasswordCheck() ) {
        return require_once survey_template_dir( 'fallback/password.php' );
    }

    // Require decryption
    if( !$response && requireDecryptionCheck() ) {
        return require_once survey_template_dir( 'fallback/invalid_key.php' );
    }
}

// Check if response exists
if( $response ) {
    switch( response()->getStatus() ) {
        case 0:
        // expired
        if( !response()->getFinishedTime() ) return require_once survey_template_dir( 'messages/expired.php' );
        // disqualified/rejected
        else return require_once survey_template_dir( 'messages/rejected.php' ); 
        break;
        // running
        case 1: return require_once survey_template_dir( 'survey/complete.php' ); break;
        // finished (2 - not approved yet, 3 - approved)
        case 2:
        case 3: return require_once survey_template_dir( 'messages/completed.php' ); break;
    }

    // this should never happen :)
    die;
}

require_once survey_template_dir( 'survey/start.php' ); ?>
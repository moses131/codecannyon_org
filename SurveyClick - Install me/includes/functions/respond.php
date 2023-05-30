<?php

/** 
 * 
 * MARKUP FUNCTIONS 
 * 
*/

function getSurveyHeader() {
    $lines              = [];
    $lines['begin']     = "<!DOCTYPE html>\n<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />";
    $lines['viewport']  = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" />";
    $lines['title']     = "<title>" . ( $title = esc_html( getMeta( 'meta_title', survey()->getName() ) ) ) . "</title>";
    $lines['og_title']  = "<meta property=\"og:title\" content=\"" . $title . "\" />";
    $lines['desc']      = '<meta name="description" content="' . ( $description = esc_html( getMeta( 'meta_desc', '' ) ) ) . '" />';
    $lines['og_desc']   = '<meta property="og:description" content="' . $description . '" />';
    if( ( $imageId = getMeta( 'meta_image', false ) ) && ( $imageURL = mediaLinks( $imageId )->getItemURL() ) ) {
        $lines['og_image'] = '<meta property="og:image" content="' . esc_url( $imageURL ) . '" />';
    }
    $lines['robots']    = "<meta name=\"robots\" content=\"noimageindex, noindex, nofollow\" />";
    $favicon                = ( $image = get_option_json( 'front_end_favicon', false ) ) ? current( $image ) : '';
    if( ( $image = filters()->do_filter( 'image_meta', $favicon ) ) )
    $lines['favicon']       = '<link rel="icon" type="image/png" href="' . esc_url( $favicon ) . '" sizes="50x50">';

    if( ( $header_lines = filters()->do_filter( 'in_survey_header', [] ) ) && !empty( $header_lines ) ) {
        $lines = $lines + $header_lines;
    }

    $lines['end']       = "</head>";

    return implode( "\n", $lines );
}

function getSurveyFooter() {
    $lines = [];

    $lines['script_utils']  = '<script>var utils = {ajax_url: \'' . esc_url( site_url( [ 'ajax' ] ) ) . '?token=' . md5( $_SESSION['csrf_token'] ) . '\'};</script>';
    $lines['ajax_scripts']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'ajax.js' ] ) ) . '"></script>';
    $lines['form_scripts']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'form.js' ] ) ) . '"></script>';
    $lines['sends_scripts'] = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'send_survey.js' ] ) ) . '"></script>';

    if( ( $footer_lines = filters()->do_filter( 'in_survey_footer', [] ) ) ) {
        $lines += $footer_lines;
    }

    return "\n" . implode( "\n", $lines );
}

/**
 * 
 * RESPONSE FUNCTIONS
 * 
 */


// Get response
function getResponse() {
    item()->response = NULL;

    // Get response from collector
    if( ( $response = collector()->getResponse() ) ) {

        // Get the response class, current step & results
        item()->response    = new \survey\response( $response, item()->survey );
        item()->response    ->currentStep();
        item()->results     = item()->response->getResponses();

        // Shortcodes
        item()->shortcodes  = new \site\shortcodes;
        item()->shortcodes  ->setCustomCallback( function( $sh, $full, $content, $attrs, $text ) {
            if( ( $v = questionValue( $sh ) ) )
            return str_replace( $full, $v, $text );
            return $text;
        } )
        ->setCustom( [
            'points' => response()->getResponsePoints()
        ] );

    }

    return item()->response;
}

// Get self response
function getSelfResponse() {
    item()->response = NULL;

    // Require user authentification & param ID
    if( me() && isset( item()->params[0] ) ) {

        // Get results from param ID
        $result     = new \query\survey\results( (int) item()->params[0] );
        $resultObj  = $result->getObject();

        // Return if the result doesn't exist
        if( !$resultObj ) return;

        // Get the survey
        item()->survey  = $result->getSurvey();
        
        // Get the user
        item()->user    = item()->survey->getUser();

        // Return if the survey doesn't exist
        if( !item()->survey->getObject() ) return ;

        // Get the response class, current step & results
        item()->response    = new \survey\response( $resultObj, item()->survey, true );
        item()->response    ->currentStep();
        item()->results     = item()->response->getResponses();

        // Shortcodes
        item()->shortcodes  = new \site\shortcodes;
        item()->shortcodes  ->setCustomCallback( function( $sh, $full, $content, $attrs, $text ) {
            if( ( $v = questionValue( $sh ) ) ) {
                return str_replace( $full, $v, $text );
            }
            return $text;
        } )
        ->setCustom( [
            'points' => response()->getResponsePoints()
        ] );

    }

    return item()->response;
}

// Response
function response() {
    return item()->response;
}

// Get settings
function getSetting( string $setting ) {
    return item()->setting[$setting] ?? NULL;
}

// Get response id
function getResponseId() {
    return item()->response->getId();
}

// Get translated text
function getTextMsg( string $id, string $fallback ) {
    return item()->survey->getText( $id, $fallback );
}

/**
 * 
 * COLLECTOR FUNCTIONS
 * 
 */

// Get collector
function getCollector() {
    if( isset( item()->params[0] ) && ( $collectors = paidSurveys() )->getObject( item()->params[0] ) ) {
        item()->collector   = $collectors;
        item()->setting     = $collectors->getSetting();
        item()->survey      = $collectors->getSurveyObject();
        item()->user        = item()->survey->getUser();
        return $collectors;
    }

    return false;
}

// Collector
function collector() {
    return item()->collector;
}

/**
 * 
 * SURVEY FUNCTIONS
 * 
 */

// Survey
function survey() {
    return item()->survey;
}

// Get meta
function getMeta( string $key, string $default ) {
    return item()->survey->meta()->get( $key, $default );
}

// Get the page
function getPage( string $page_id ) {
    return getSurveyPage( item()->survey->getId(), $page_id );
}

// Get inline content
function getInlineContent( string $text ) {
    item()->shortcodes->setInlineContent( esc_html( $text ) );
    return item()->shortcodes->inlineMarkup();
}

// Get content
function getContent( string $text ) {
    item()->shortcodes->setContent( $text );
    item()->shortcodes->setCustom( [
        'points' => response()->getResponsePoints()
    ] );

    $vars   = results()->answerSelect( 'value_str' )->getAnswer( 9, item()->response->getId(), item()->survey->getId() );
    if( isset( $vars->value_str ) )
    $vars   = [ $vars ];

    if( !empty( $vars ) )
    foreach( $vars as $var ) {
        item()->shortcodes->setVariables( json_decode( $var->value_str, true ) );
    }

    item()->shortcodes->setPoints( response()->getResponsePoints() );
    return item()->shortcodes->toMarkup();
}

/**
 * 
 * QUESTION FUNCTIONS
 * 
 */

// Get questons
function getQuestions() {
    return item()->response->getQuestions()->setVisible( 2 );
}

// Get question
function getQuestion( object $info ) {
    item()->current_question = questions();
    item()->current_question ->setObject( $info );
    return item()->current_question;
}

// Question
function question() {
    return item()->current_question;
}

// Question's title
function questionTitle() {
    item()->shortcodes->setInlineContent( esc_html( item()->current_question->getTitle() ) );
    return item()->shortcodes->inlineMarkup();
}

// Question's info
function questionInfo() {
    item()->shortcodes->setInlineContent( esc_html( item()->current_question->getInfo() ) );
    return item()->shortcodes->inlineMarkup();
}

// Question's markup
function questionMarkup() {
    if( !isset( item()->current_question ) ) return ;
    return survey_types()->getMarkup( item()->current_question->getType() );
}

// Get settings
function getSettings() {
    item()->current_question->settings = item()->current_question->getSetting();
    if( item()->current_question->settings )
    return item()->current_question->settings;
    return NULL;
}

// Setting
function setting( string $setting ) {
    if( !isset( item()->current_question->settings ) )
    getSettings();
    return item()->current_question->settings[$setting] ?? NULL;
}

// Value
function value( $def = '' ) {
    return item()->results['st'][item()->response->currentStep()]['vl'][item()->current_question->getId()] ?? $def;
}

// Question's value
function questionValue( $question, string $def = '' ) {
    return item()->results['sv'][$question] ?? $def;
}

/**
 * 
 * SETTINGS FUNCTIONS
 * 
 */

// Require a password
function requirePassword() {
    return ( !empty( item()->setting['password'] ) ? true : false );
}

// Require & check password
function requirePasswordCheck() {
    $password = item()->setting['password'] ?? false;
    if( !$password ) return false;

    if( $_SERVER['REQUEST_METHOD'] != 'POST' || isset( $_POST['check_password'] ) && $_POST['check_password'] != $password ) {
        return true;
    }

    return false;
}

// Require decryption
function requireDecryption() {
    $enckey = item()->setting['enckey'] ?? false;
    $allowe = item()->setting['allowe'] ?? false;
    if( $enckey ) {
        if( ( !$allowe || !empty( $_GET['trackId'] ) ) )
        return true;
    }

    return false;
}

// Require & check decryption
function requireDecryptionCheck() {
    $enckey = item()->setting['enckey'] ?? false;
    $allowe = item()->setting['allowe'] ?? false;
    if( $enckey ) {
        if( $allowe && empty( $_GET['trackId'] ) )
        return false;
        if( !isset( $_GET['trackId'] ) || !isset( $_GET['key'] ) || md5( $enckey . $_GET['trackId'] ) != $_GET['key'] )
        return true;
    }

    return false;
}

/** 
 * 
 * USER & LIMITS
 * 
*/

function limits() {
    return item()->user->myLimits();
}

/**
 * 
 * THEME SETTINGS
 * 
 */

function survey_template_url( string $str ) {
    $themes     = filters()->do_filter( 'survey-themes', [] );
    $c_theme    = item()->survey->getTemplate();
    $theme      = $themes[$c_theme]['path'] ?? $themes['t1']['path'];
    return themes()->getSiteURL() . str_replace( DIR, '', $theme ) . '/' . $str;
}

function survey_template_dir( string $str ) {
    $themes     = filters()->do_filter( 'survey-themes', [] );
    $c_theme    = item()->survey->getTemplate();
    $theme      = $themes[$c_theme]['path'] ?? $themes['t1']['path'];
    return implode( '/', [ $theme, $str ] );
}
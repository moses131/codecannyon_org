<?php

/** GENERAL */

// Get a website option
function get_option( string $opt, $default = false, $callback = false ) {
    return $GLOBALS['options']->get_option( $opt, $default, $callback );
}

// Get a website option unserialized
function get_option_array( string $opt, $default = [], $callback = false ) {
    return $GLOBALS['options']->get_option_array( $opt, $default, $callback );
}

// Get a website option json decoded
function get_option_json( string $opt, $default = [], $callback = false ) {
    return $GLOBALS['options']->get_option_json( $opt, $default, $callback );
}

// Get multiple website options
function get_options( array $opts, bool $keepKeys = false ) {
    return $GLOBALS['options']->get_options( $opts, $keepKeys );
}

// Get a theme_option
function get_theme_option( string $opt, $default = false ) {
    global $theme_options;
    
    if( $theme_options === NULL )
    $theme_options = get_option_json( 'to_' . themes()->getThemeName() );

    if( isset( $theme_options[$opt] ) )
    return $theme_options[$opt];

    return $default;
}

// Get all theme_option
function get_theme_options() {
    global $theme_options;
    
    if( $theme_options === NULL )
    $theme_options = get_option_json( 'to_' . themes()->getThemeName() );

    return $theme_options;
}

// Get website URL
function site_url( $params = [], array $q_params = [] ) {
    if( is_string( $params ) ) {
        $params = [ $params ];
    }
    return themes()->getSiteURL() . ( !empty( $params ) ? '/' . implode( '/', array_filter( $params ) ) : '/' ) .
    ( !empty( $q_params ) ? '?' . http_build_query( $q_params, '', '&' ) : '' );
}

// Get custom URL
function custom_url( string $url, $params = [], array $q_params = [] ) {
    if( is_string( $params ) ) {
        $params = [ $params ];
    }
    return rtrim( $url, '/' ) . ( !empty( $params ) ? '/' . implode( '/', array_filter( $params ) ) : '/' ) .
    ( !empty( $q_params ) ? '?' . http_build_query( $q_params, '', '&' ) : '' );
}

// Get current theme's name
function current_theme_name() {
    return themes()->getThemeName();
}

// Get theme's URL
function theme_url( string $str = '' ) {
    return themes()->getSiteURL() . '/' . implode( '/', [ THEMES_DIR, current_theme_name(), $str ] );
}

// Get theme's local path
function theme_dir( string $str = '' ) {
    return implode( '/', [ DIR, THEMES_DIR, current_theme_name(), $str ] );
}

// Get survey's URL
function survey_url( string $str = '' ) {
    return themes()->getSiteURL(). '/' . implode( '/', [ SURVEY_DIR, $str ] );
}

// Get survey's local path
function survey_dir( string $str = '' ) {
    return implode( '/', [ DIR, SURVEY_DIR, $str ] );
}

// Get current admin theme's name
function current_admin_theme_name() {
    return get_option( 'admin_theme_name' );
}

// Get admin's URL
function admin_url( string $str = '', bool $real_path = false ) {
    if( $real_path ) {
        return site_url( [ ADMIN_DIR, ADMIN_THEMES_DIR, current_admin_theme_name(), $str ] );
    }
    return site_url( [ filters()->do_filter( 'admin_custom_path', ADMIN_LOC ), $str ] );
}

// Get admin's local path
function admin_dir( string $str = '' ) {
    return implode( '/', [ DIR, ADMIN_DIR, ADMIN_THEMES_DIR, current_admin_theme_name(), $str ] );
}

// Assets URL
function assets_url( string $str = '' ) {
    return site_url( [ 'assets', $str ] );
}

// Escape string
function esc_html( $str ) {
    return htmlspecialchars( (string) $str );
}

function esc_html_e( $str ) {
    echo htmlspecialchars( (string) $str );
}

function esc_url( $str ) {
    return htmlspecialchars( (string) $str );
}

function esc_url_e( $str ) {
    echo htmlspecialchars( (string) $str );
}

function html_decode( $str ) {
    return htmlspecialchars_decode( (string) $str );
}

// Check if get param exists
function _get_v( string $str, $def = '' ) {
    return ( isset( $_GET[$str] ) ? $_GET[$str] : $def );
}

// Add params to URL
function _get_update( array $arr, string $url = '' ) {
    $url_params = [];
    if( empty( $url ) )
    $url = $_SERVER['REQUEST_URI'];
    $exp_params = explode( '?', $url );
    $url = $exp_params[0];
    if( isset( $exp_params[1] ) )
    parse_str( $exp_params[1], $url_params ); 
    return rtrim( $url, '/' ) . '/?' . http_build_query( array_merge( $url_params, $arr ), '', '&' );
}

// Change URL but keep the query
function _get_change_url( string $url, array $arr = [] ) {
    $url_params = [];
    $exp_params = explode( '?', $_SERVER['REQUEST_URI'] );
    if( isset( $exp_params[1] ) )
    parse_str( $exp_params[1], $url_params ); 
    if( !empty( $arr ) )
    $url_params = array_intersect_key( $url_params, array_flip( $arr ) );
    return rtrim( $url, '/' ) . '/?' . http_build_query( $url_params, '', '&' );
}

// Change query params for an URL
function _get_url_params( array $arr = [], string $url = '' ) {
    if( empty( $url ) )
    $url = $_SERVER['REQUEST_URI'];
    return rtrim( $url, '/' ) . '/?' . http_build_query( $arr, '', '&' );
}

/** USER */

// Current user info
function me() {
    return $GLOBALS['me'];
}

/** SITE */

// Site class info
function site() {
    return $GLOBALS['site'];
}

// Media manager
function media( array $files ) {
    return new \site\media\servers( $files );
}

// Media manager from id
function mediaFromId( int $id ) {
    return new \site\media\servers( [], $id );
}

// Media servers
function mediaServers() {
    return filters()->do_filter( 'media_servers', [] );
}

// Media Links
function mediaLinks( int $id = NULL, int $type_id = NULL ) {
    return new \site\media\links( $id, $type_id );
}

// Media URL
function mediaURL( string $path, string $server ) {
    $servers    = mediaServers();
    $server     = $servers[$server] ?? NULL;

    if( isset( $server ) )
    return call_user_func( $server['file_url'], $path );

    return ;
}

/** DATABASE */
function db_update( string $table, array $columns, array $where ) {
    $db = new \util\db;
    return $db->db_action( 'UPDATE', $table, $columns, $where );
}

/** HELPERS */

// Get current object
function item() {
    global $item;
    return $item;
}

/** DEVELOPERS */

// Options
function options() {
    return $GLOBALS['options'];
}

// Filters
function filters() {
    return $GLOBALS['filters'];
}

// Actions
function actions() {
    return $GLOBALS['actions'];
}

// Ajax
function ajax() {
    return $GLOBALS['ajax'];
}

// Routes
function routes() {
    return $GLOBALS['routes'];
}

// Themes
function themes() {
    return $GLOBALS['themes'];
}

// Plugins
function plugins() {
    return $GLOBALS['plugins'];
}

// Menus
function menus() {
    return $GLOBALS['menus'];
}

// App
function app() {
    return $GLOBALS['app'];
}

// Build URL path
function URLBP( array $accepted = [], $path = '' ) {
    return new \util\build_url_path( $accepted, $path );
}

// Developer
function developer() {
    global $developer;
    return $developer;
}

// Create a survey template
function newSurveyTemplate( string $id, array $options ) {
    return site()->templates->newTemplate( $id, $options );
}

// Custom time
function custom_time( $datetime_str = NULL, int $just_str = 0, string $format = '', object $date = NULL ) {
    // If date object is not set, use the global setting
    if( !$date ) {
        global $date_time;
        // Clone the settings
        $date = $date_time;
    }

    if( empty( $datetime_str ) )
    $datetime_str = time();

    // Set a new timestamp or modify it
    if( !empty( $datetime_str ) ) {
        if( $just_str < 0 ) {
            if( is_numeric( $datetime_str ) )
                $date   ->modifyTimestamp( $datetime_str );
            else
                $date   ->modifyDate( $datetime_str );
            $just_str   *= -1;
        } else {
            if( is_numeric( $datetime_str ) )
                $date   ->setTimestamp( $datetime_str );
            else
                $date   ->setDate( $datetime_str );
        }
    }
    
    switch( $just_str ) {
        case 1: return $date->formatStr(); break; // eg: 2 m / 59 s
        case 2: return $date->format( $format ); break; // date/time format
        case 3: return $date->getTimestamp(); break; // unixtimestamp
        case 4: return $date->toServerTime()->format( $format ); break; // user to server time: date/time format
        case 5: return $date->toServerTime()->getTimestamp(); break; // user to server time: unistimestamp
        case 6: return $date->toServerTime()->format2Way( $format ); break; // user to server time: unixtimestamp
        default: return $date->format2Way( $format ); // both formats
    }
}

function user_time( $time = NULL ) {
    global $date_time;
    // Clone the settings
    $date = $date_time;
    if( is_numeric( $time ) ) $date->setTimestamp( $time );
    else if( is_string( $time ) ) $date->setDate( $time );
    return $date->toServerTime();
}

// Filters
function nav_active( string $menu ) {
    filters()->add_filter( 'admin-nav-active', function( $filter, $menus = [] ) use ( $menu ) {
        $menus[$menu] = '';
        return $menus;
    } );
}

// The shop
function shop() {
    global $shop;
    return $shop;
}

/** UTIL FUNCTIONS */
function survey_types( string $type = '' ) {
    return filters()->do_filter( 'question-types', $type );
}

function cms_json_encode( array $array, ...$attrs ) {
    if( !isset( $attrs[0] ) )
    $attrs[0] = JSON_INVALID_UTF8_IGNORE;
    return json_encode( $array, ...$attrs );
}

/** ENCRYPTION */

// Encrypt
function em_encrypt( string $str ) {
    return openssl_encrypt( $str, 'AES-128-ECB', ENC_PASS );
}

// Decrypt
function em_decrypt( string $str ) {
    return openssl_decrypt( $str, 'AES-128-ECB', ENC_PASS );
}

/** GLOBAL CLASSES */

function surveys( int $id = 0 ) {
    return new \query\survey\surveys( $id );
}

function surveyResults( int $id = 0 ) {
    return new \survey\report( $id );
}

function steps( int $id = 0 ) {
    return new \query\survey\steps( $id );
}

function surveyMeta( int $id ) {
    return new \query\survey\meta( $id );
}

function questions( int $id = 0 ) {
    return new \query\survey\questions( $id );
}

function results( int $id = 0 ) {
    return new \query\survey\results( $id );
}

function paidSurveys( int $id = 0 ) {
    return new \query\survey\custom( $id );
}

function collectors( int $id = 0 ) {
    return new \query\collectors( $id );
}

function responses() {
    return new \query\results;
}

function teams( int $id, int $user = 0 ) {
    return new \query\team\teams( $id, $user );
}

function alerts( int $id = 0 ) {
    return new \query\alerts( $id );
}

function pages( int $id = 0 ) {
    return new \query\pages( $id );
}

function users( int $id = 0 ) {
    return new \query\users( $id );
}

function categories( int $id = 0 ) {
    return new \query\categories( $id );
}

function vouchers( int $id = 0 ) {
    return new \query\vouchers( $id );
}

function subscriptions( int $id = 0 ) {
    return new \query\subscriptions( $id );
}

function transactions( int $id = 0 ) {
    return new \query\transactions( $id );
}

function stats() {
    return new \query\stats\transactions;
}

function usersStats() {
    return new \query\stats\users;
}

function surveysStats() {
    return new \query\stats\surveys;
}

function earningsStats() {
    return new \query\stats\earnings;
}

function responsesStats() {
    return new \query\stats\responses;
}

function subscriptionsStats() {
    return new \query\stats\subscriptions;
}

function pricingPlans( int $plan = NULL ) {
    $plans = new \query\plans\plans;
    if( $plan ) $plans->setId( $plan );
    return $plans;
}

function getFreeSubscription() {
    $limits = new \query\user_limits;
    $limits ->setFreeSubscription();
    return $limits;
}

function textFormatting( string $text = '' ) {
    return new \util\text( $text );
}

function getCountries() {
    return filters()->do_filter( 'site_countries', site()->countries );
}

function getCountry( $country = NULL ) {
    if( $country && ( $countries = getCountries() ) )
    return ( $countries[$country] ?? NULL );

    global $site;
    return $site->user_country->currentCountry;
}

function getLanguages() {
    return filters()->do_filter( 'site_languages', site()->languages );
}

function getLanguage( $language = NULL ) {
    if( $language && ( $languages = getLanguages() ) )
    return ( $languages[$language] ?? NULL );

    global $site;
    return $site->user_language->currentLanguage;
}

function getUserLanguage( string $str = '' ) {
    global $site;
    if( $str != '' && isset( $site->user_language->currentLanguage[$str] ) )
    return $site->user_language->currentLanguage[$str];
    return $site->user_language->currentLanguage; 
}

function getLangDirection() {
    $direction = getUserLanguage( 'direction' );
    if( is_string( $direction ) )
    return $direction;
    return 'ltr';
}

function showAlerts() {
    global $site;
    $markup = '';
    if( !empty( $site->alerts ) ) {
        foreach( $site->alerts as $alert ) {
            switch( $alert['status'] ) {
                case 'success':
                    $tagClass = ' success';
                break;

                case 'fail':
                case 'error':
                    $tagClass = ' error';
                break;

                case 'alert':
                    $tagClass = ' alert';
                break;

                case 'info':
                    $tagClass = ' info';
                break;

                default:
                    $tagClass = '';
            }
            $markup .= '<div class="msg big' . $tagClass . '">' . $alert['text'] . '<a href="#" class="close"><i class="fas fa-times"></i></a></div>';
        }
    }
    return $markup;
}

function page_alert( string $text, string $style = 'info' ) {
    switch( $style ) {
        case 'success':
            $tagClass = ' success';
        break;

        case 'fail':
        case 'error':
            $tagClass = ' error';
        break;

        case 'alert':
            $tagClass = ' alert';
        break;

        case 'info':
            $tagClass = ' info';
        break;
    }
    
    return '<div class="msg big' . $tagClass . '">' . $text . '<a href="#" class="close"><i class="fas fa-times"></i></a></div>';
}

/** SURVEY FUNCTIONS */

function getSurveyPages( int $survey = NULL ) {
    $pages  = filters()->do_filter( 'survey_pages', [] );
    if( $survey === NULL )
    return $pages;
    else {
        $meta = surveyMeta( $survey );
        array_walk( $pages, function( &$v, $k ) use ( $meta ) {
            if( ( $value = $meta->getValue( 'p:' . $k ) ) ) {
                $v['content']   = $value;
                $v['isDefault'] = false;
            }
        } );

        return $pages;
    }
}

function getSurveyPage( int $survey, string $page_id ) {
    $pages  = filters()->do_filter( 'survey_pages', [] );
    $page   = $pages[$page_id] ?? NULL;

    if( !$page ) {
        return false;
    }

    $meta   = surveyMeta( $survey );
    if( ( $value = $meta->getValue( 'p:' . $page_id ) ) ) {
        $page['content']   = $value;
        $page['isDefault'] = false;
    }

    return $page;
}

/** USEFUL FUNCTIONS */

function generate_uname( $pre = '' ) {
    return implode( '-', [ $pre, bin2hex( random_bytes( 10 ) ), time() ] );
}

/** LOAD OTHER FUNCTIONS */

require_once 'locale.php';

/** MONEY FORMAT */

function cms_money( float $number ) {
    $country    = getCountry();
    $sep        = filters()->do_filter( 'money_separator', explode( '-', $country->mseparator ) );
    $dec        = 2;
    if( ( $number - (int) $number ) == 0 ) $dec = 0;
    return number_format( $number, $dec, $sep[0], $sep[1] );
}

function cms_money_format( float $number ) {
    $country    = getCountry();
    $format     = filters()->do_filter( 'money_format', $country->mformat );
    return str_replace( [ '%s', '%a' ], [ PAYMENT_SYMBOL, cms_money( $number ) ], $format );
}

/** ETC */

function get_transaction_str( int $id = NULL, float $amount ) {
    $types = filters()->do_filter( 'transaction_types', [
        1   => [ 'title' => t( 'Deposit' ), 'sign' => '+' ],
        2   => [ 'title' => t( 'Budget updated' ), 'sign' => '+' ],
        3   => [ 'title' => t( 'Budget updated' ), 'sign' => ( $amount < 0 ? '+' : '-' ) ],
        4   => [ 'title' => t( 'Withdrawn' ), 'sign' => '-' ],
        5   => [ 'title' => t( 'Subscription' ), 'sign' => '-' ],
        6   => [ 'title' => t( 'Commission' ), 'sign' => '+' ],
        7   => [ 'title' => t( 'Voucher' ), 'sign' => '+' ],
        8   => [ 'title' => t( 'Website commission' ), 'sign' => '+' ],
    ] );
    
    if( !$id ) {
        return $types;
    }

    return $types[$id] ?? [ 'title' => '-', 'sign' => '' ];
}

/** POLYFILL FOR OLD PHP VERSIONS */

if( !function_exists( 'array_is_list' ) ) {
    function array_is_list( array $a ) {
        return $a === [] || (array_keys($a) === range(0, count($a) - 1));
    }
}
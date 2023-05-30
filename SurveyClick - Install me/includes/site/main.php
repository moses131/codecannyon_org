<?php

namespace site;

class main extends main_helper {

    function __construct() {
        parent::__construct();
        
        global $me, $shop, $actions, $filters, $ajax, $routes, $options, $site, $date_time, $developer;

        $site                   = (object) [];
        $site->alerts           = [];
        $site->debug            = [];
        $site->user_language    = (object) [];
        $site->user_country     = (object) [];
        $developer              =  new \dev\developer;

        require_once DIR . '/lib/vendor/autoload.php';
        require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'globals.php' ] );

        $me             = $this->me         = false;
        $options        = new \site\options;
        $actions        = new \dev\actions;
        $date_time      = new \util\date_time;
        $filters        = $this->filters    = new \dev\filters;
        $routes         = $this->routes     = new \dev\routes;
        $countries      = new \query\countries;
        $countries      ->select( [ 'iso_3166', 'name', 'hour_format', 'date_format', 'timezone', 'firstday', 'language', 'mformat', 'mseparator' ] );
        
        if( !isset( $_SESSION['csrf_token'] ) )
        $_SESSION['csrf_token'] = bin2hex( random_bytes( 32 ) );

        $site->app          = new \site\app;
        $site->templates    = new \site\templates;
    
        new \site\menus;
        $theme = new \site\themes;
        new \site\plugins;

        $ajax = new \dev\ajax;

        $site->languages= filters()->do_filter( 'site_languages', $this->getDefaultLanguages() );
        $site->countries= filters()->do_filter( 'site_countries', $countries->fetch( -1 ) );

        $users = new \query\users;
        if( ( $logged = $users->checkUserBySession() ) && $users->setId( $logged->user_id ) && $users->getObject() ) {
            $me     = $users;
            $me     ->loginConfirmed    = $logged->conf;
            $me     ->sessionValid      = $logged->valid;
            $me     ->lastAction        = strtotime( $me->getLastAction() );
            $type   = isset( $_COOKIE['viewAs'] ) ? (int) $_COOKIE['viewAs'] : 0;
            if( $type && ( $perms = $users->getPermsArray() ) && isset( $perms[$type] ) ) {
                $types  = [
                    0   => 'respondent',
                    1   => 'moderator',
                    2   => 'admin',
                    3   => 'owner',
                    4   => 'surveyor'
                ];
                if( isset( $types[$type] ) ) {
                    $me  ->viewAs    = $types[$type];
                    $me  ->viewAsId  = $type;
                } else {
                    $me  ->viewAs    = 'player';
                    $me  ->viewAsId  = 0;
                }
            } else {
                $me  ->viewAs    = 'respondent';
                $me  ->viewAsId  = 0;
            }

            $this->me = $me;
        }

        // use user's settings
        if( $me ) {
            // custom user's date
            $date_time->currentUser( 
                (string) $me->getDFormat(), 
                (string) $me->getHFormat(), 
                (string) $me->getTz() 
            );

            // set user's language
            $site->user_language->current   = $me->getLanguageId();

            // set user's country
            $site->user_country->current    = $me->getCountryId();

            // check if last action is too old, update it in case it is
            if( $me && strtotime( -UPDATE_HIT_INTVAL . ' seconds' ) > $me->lastAction )
                $me->updateLastAction();
        
            // init loyalty points shop only for logged users
            $shop = new \user\shop;
            
        // use visitor's settings
        } else {
            // check if site's country is saved in cookies
            if( isset( $_COOKIE['site_country'] ) )
                $site->user_country->current    = $_COOKIE['site_country'];
            else {
                // try to find user's country
                $this->autodetectCountry( $site );
            }

            // check if site's language is saved in cookies
            if( isset( $_COOKIE['site_lang'] ) )
                $site->user_language->current   = $_COOKIE['site_lang'];
        }
            
        $site->user_language->currentLanguage   = $site->languages[$site->user_language->current] ?? $site->languages[DEFAULT_LANGUAGE];
        $site->user_country->currentCountry     = $site->countries[$site->user_country->current] ?? $site->countries[DEFAULT_COUNTRY];

        // for visitors use the auto detected country, or the default one
        if( !$me ) {
            $date_time->currentUser( 
                $site->user_country->currentCountry->date_format, 
                $site->user_country->currentCountry->hour_format, 
                $site->user_country->currentCountry->timezone 
            );
        }

        if( !defined( 'LC_MESSAGES' ) )
            $site->debug['error'][] = 'setlocale issue. LC_MESSAGES is undefined. PHP message: available if PHP was compiled with libintl';
        else if( !function_exists( 'gettext' ) )
            $site->debug['error'][] = 'gettext is not installed';
        else {
            if( !setlocale( LC_MESSAGES, $site->user_language->currentLanguage['locale'] ) )
                $site->debug['error'][] = sprintf( 'Locale "%s" is invalid', esc_html( $site->user_language->currentLanguage['locale_e'] ) );
        }

        $filters->add_filter( 'in_admin_footer', function( $filter, $lines ) {
            $lines['script_utils']  = '<script>var utils = {ajax_url: \'' . site_url( [ 'ajax' ] ) . '?token=' . md5( $_SESSION['csrf_token'] ) . '\'};</script>';
            $lines['ajax_scripts']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'ajax.js' ] ) ) . '"></script>';
            $lines['form_scripts']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'form.js' ] ) ) . '"></script>';
            return $lines;
        });

        require_once admin_dir( 'functions.php' );

        if( $me )
        require_once implode( '/', [ DIR, INCLUDES_DIR, 'dev/user_utils', $me->viewAs . '.php' ] );

        $site   ->app->filters2();
        $theme  ->loaded();

        $actions->do_action( 'load' );
    }

    // Font end header
    private function header() {
        global $developer;
        $developer->inFrontEnd();

        require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'front_end.php' ] );

        if( $this->filters->do_filter( 'show_header', true ) ) {
            if( $this->me )
                $header_page = $this->filters->do_filter( 'header_page_logged',     theme_dir( 'header.php' ) );
            else
                $header_page = $this->filters->do_filter( 'header_page_not_logged', theme_dir( 'header.php' ) );

            require_once $header_page;
        }
    }

    // Front end footer
    private function footer() {
        if( $this->filters->do_filter( 'show_footer', true ) ) {
            if( $this->me )
                $footer_page = $this->filters->do_filter( 'footer_page_logged',     theme_dir( 'footer.php' ) );
            else
                $footer_page = $this->filters->do_filter( 'footer_page_not_logged', theme_dir( 'footer.php' ) );

            require_once $footer_page;
        }
    }

    // Back end header
    private function admin_header() {
        global $developer;
        $developer->inBackEnd();

        if( $this->filters->do_filter( 'show_admin_header', true ) ) {
            if( $this->me )
                $header_page = $this->filters->do_filter( 'admin_header_page_logged',     admin_dir( 'header.php' ) );
            else
                $header_page = $this->filters->do_filter( 'admin_header_page_not_logged', admin_dir( 'header_not_logged.php' ) );

            require_once $header_page;
        }
    }

    // Back end footer
    private function admin_footer() {
        if( $this->filters->do_filter( 'show_admin_footer', true ) ) {
            if( $this->me )
                $footer_page = $this->filters->do_filter( 'admin_footer_page_logged',     admin_dir( 'footer.php' ) );
            else
                $footer_page = $this->filters->do_filter( 'admin_footer_page_not_logged', admin_dir( 'footer_not_logged.php' ) );

            require_once $footer_page;
        }
    }

    // Index page
    private function index_page() {
        if( $this->me )
            $index_page = $this->filters->do_filter( 'index_page_logged',       theme_dir( 'index.php' ) );
        else
            $index_page = $this->filters->do_filter( 'index_page_not_logged',   theme_dir( 'index.php' ) );

        ob_start();
        require_once $index_page;
        $page_content = ob_get_contents();
        ob_end_clean();

        $this->header();
        echo $page_content;
        $this->footer();
    }

    // Ajax page
    private function ajax_page() {
        require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'ajax.php' ] );
        require_once implode( '/', [ DIR, INCLUDES_DIR, 'site', 'ajax.php' ] );
    }

    // Survey page
    private function survey_page( string $content ) {
        require_once survey_dir( 'header.php' );
        echo $content;
        require_once survey_dir( 'footer.php' );
    }

    // This will be the page
    private function the_page( array $page ) {
        global $site, $item;

        $item       = (object) [];
        $permalinks = [
            filters()->do_filter( 'path:category',      'category' )    => [ 'page' => 'category',      'functions' => 'categories.php'                         ], 
            filters()->do_filter( 'path:survey',        'survey' )      => [ 'page' => 'survey',        'functions' => 'surveys.php'                            ],
            filters()->do_filter( 'path:search',        'search' )      => [ 'page' => 'search',        'functions' => 'search.php'                             ],
            filters()->do_filter( 'path:respond',       'r' )           => [ 'page' => 'respond',       'functions' => 'respond.php',   'location' => 'survey'  ],
            filters()->do_filter( 'path:selfrespond',   's' )           => [ 'page' => 'selfrespond',   'functions' => 'respond.php',   'location' => 'survey'  ]
        ];

        $path   = strtok( current( $page ), '.' );
        $dir    = 'theme';

        if( isset( $permalinks[$path] ) && ( $ppage = $permalinks[$path] ) ) {
            $dir    = $ppage['location'] ?? $dir;
            switch( $dir ) {
                case 'survey':
                    $page_loc   = survey_dir( $ppage['page'] . '.php' );
                break; 

                default:
                    $page_loc   = theme_dir( $ppage['page'] . '.php' );
            }
        } else
            $page_loc   = theme_dir( strtok( implode( '/', $page ), '.' ) . '.php' );

        ob_start();

        if( file_exists( $page_loc ) ) {

            array_shift( $page );

            $item->pageType     = 'template';
            $item->mainPage     = $path;
            $item->params       = $page;

            if( isset( $ppage['functions'] ) )
            require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', $ppage['functions'] ] );
            require_once $page_loc;

        } else if( ( $page_loc = theme_dir( '_' . $path . '.php' ) ) && file_exists( $page_loc ) ) {

            array_shift( $page );

            $item->pageType     = 'dynamic';
            $item->mainPage     = $path;
            $item->params       = $page;

            require_once $page_loc;

        } else if( $this->routes->check_route( implode( '/', $page ) ) ) {

            $item->pageType     = 'route';
            echo $this->routes->do_last_route();

        } else if( ( $upage = pages() ) && $upage->setSlug( implode( '/', $page ) ) && $upage->getObject() ) {

            $item->object       = $upage;
            $item->pageType     = 'created'; 
            $template           = theme_dir( 'page.php' );

            if( ( $ptemplate = $upage->getTemplate() ) && file_exists( theme_dir( $ptemplate ) ) )
            $template = theme_dir( $ptemplate );

            $template = $this->filters->do_filter( 'page-template', $template, $upage );

            require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'pages.php' ] );
            require_once $template;

        }

        $page_content = ob_get_contents();
        ob_end_clean();

        if( empty( $page_content ) )
            $page_content = $this->page_404();
        else if( $dir == 'survey' )
            return $this->survey_page( $page_content );

        $this->header();
        echo $page_content;
        $this->footer();
    }

    // Load admin includes
    private function admin_includes() {
        spl_autoload_register( function ( $cn ) {
            $type   = strstr( $cn, '\\', true );
            $cn     = str_replace( '\\', '/', $cn );
            if( $type == 'admin' ) {
                if( file_exists( ( $file = admin_dir( 'includes' . substr( $cn, strpos( $cn, '/' ) ) . '.php' ) ) ) )
                require_once $file;
            }
        } );
    }

    // Admin page
    private function admin_page( array $page ) {
        ob_start();

        global $item;
        $item                   = (object) [];

        if( ( $page_loc = admin_dir( strtok( implode( '/', $page ), '.' ) . '.php' ) ) && file_exists( $page_loc ) ) {

            $item->pageType     = 'template';
            $item->mainPage     = current( $page );
            require_once $page_loc;

        } else if( ( $page_loc = admin_dir( '_' . strtok( current( $page ), '.' ) . '.php' ) ) && file_exists( $page_loc ) ) {

            $item->pageType     = 'dynamic';
            $item->mainPage     = array_shift( $page );
            $item->params       = $page;
            require_once $page_loc;

        } else if( $this->routes->check_admin_route( implode( '/', $page ) ) ) {

            $item->pageType     = 'route';
            echo $this->routes->do_last_route();

        }

        $page_content = ob_get_contents();
        ob_end_clean();

        if( empty( $page_content ) ) {
            $page_content = $this->admin_page_404();
        }

        $this->admin_header();
        echo $page_content;
        $this->admin_footer();
    }

    private function page_404() {
        ob_start();
        require_once theme_dir( '404.php' );
        $page_content = ob_get_contents();
        ob_end_clean();

        return $page_content;
    }

    private function admin_page_404() {
        ob_start();
        require_once admin_dir( '404.php' );
        $page_content = ob_get_contents();
        ob_end_clean();

        return $page_content;
    }

    // Initiate the website
    public function init_site() {
        $this   ->admin_includes();
        $path   = [];

        if( isset( $_GET['path'] ) && is_string( $_GET['path'] ) ) {
            $str_path   = $_GET['path'];
            $path       = explode( '/', $this->filters->do_filter_string( 'url_path', $str_path ) );
            $path       = array_filter( $path );
        }

        if( !isset( $path[0] ) ) {

            $this->index_page();

        } else {

            switch( $path[0] ) {
                // admin path
                case $this->filters->do_filter( 'admin_custom_path', ADMIN_LOC ):
                    require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'back_end.php' ] );

                    if( count( $path ) == 1 )
                    $path[] = $this->filters->do_filter( 'admin_index_file', 'index' ); 

                    $this->admin_page( array_slice( $path, 1 ) );
                break;

                // ajax
                case 'ajax':
                    $this->ajax_page();
                break;

                // any other page
                default:
                    require_once implode( '/', [ DIR, INCLUDES_DIR, 'functions', 'front_end.php' ] );
                    $this->the_page( $path );
            }

        }
    }

}
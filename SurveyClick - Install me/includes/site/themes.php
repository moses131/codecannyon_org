<?php

namespace site;

class themes extends \dev\themes {

    private $websiteURL;
    private $themeName;
    private $themeInfo;
    private $themeHasjson;
    private $info           = [];
    private $hasjson        = false;
    private $themesList     = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;

    function __construct() {
        global $themes;
        $themes = $this;
        $this   ->readCurrentTheme();
    }

    public function getThemeName() {
        return $this->themeName;
    }

    public function getThemeInfo() {
        return $this->themeInfo;
    }

    public function themeHasJsonFile() {
        return $this->themeHasjson;
    }

    public function getId() {
        return $this->info['id'];
    }

    public function getPath() {
        return $this->info['path'];
    }

    public function getName() {
        return ( isset( $this->info['name'] ) ? $this->info['name'] : $this->getId() );
    }

    public function getAuthor() {
        return ( isset( $this->info['author'] ) ? $this->info['author'] : false );
    }

    public function getPreview() {
        return ( isset( $this->info['preview'] ) ? site_url( [ THEMES_DIR, $this->getId(), $this->info['preview'] ] ) : false );
    }

    public function getPreviewMarkup() {
        $preview = $this->getPreview();

        if( !$preview ) {
            $preview = filters()->do_filter( 'default_theme_preview', '<div class="avt avt-' . strtoupper( $this->getName()[0] ) . '"><span>' . strtoupper( $this->getName()[0] ) . '</span></div>' );
        }

        return filters()->do_filter( 'default_theme_preview', $preview );
    }

    public function getURL() {
        return ( isset( $this->info['url'] ) ? $this->info['url'] : false );
    }

    public function getVersion() {
        return ( isset( $this->info['version'] ) ? $this->info['version'] : false );
    }

    public function getDescription() {
        return ( isset( $this->info['description'] ) ? $this->info['description'] : false );
    }

    public function getListLink() {
        return ( isset( $this->info['list_link'] ) ? $this->info['list_link'] : '#' );
    }

    public function getRequiresPHPVersion() {
        return ( isset( $this->info['requires_php'] ) ? $this->info['requires_php'] : false );
    }

    public function getInfo() {
        return $this->info;
    }

    public function hasJsonFile() {
        return $this->hasjson;
    }

    public function isActivated() {
        return ( $this->info['id'] == $this->themeName );
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject() {
        return $this->info;
    }
    
    private function setPagination( $pagination ) {
        $this->pagination = $pagination;
        return $this;
    }

    public function getPagination() {
        return $this->pagination;
    }

    public function setPage( int $page ) {
        $this->current_page = $page;
        return $this;
    }

    public function setItemsPerPage( int $items = 10 ) {
        $this->items_per_page = $items;
        return $this;
    }

    public function itemsPerPage() {
        return filters()->do_filter( 'themes_per_page', $this->items_per_page );
    }

    public function count() {
        return $this->count;
    }

    public function pagination() {
        $pagination = new \markup\front_end\pagination( 
            $this->pagination['total_pages'], 
            $this->pagination['items_per_page'], 
            $this->pagination['current_page'] 
        );
        return $pagination;
    }

    public function themeExists( $theme ) {
        $theme = rtrim( DIR, '/' ) . '/' . rtrim( THEMES_DIR, '/' ) . '/' . $theme;
        if( file_exists( $theme ) )
        return $theme;
        return false;
    }

    public function getThemes() {
        $themes         = glob( rtrim( DIR, '/' ) . '/' . rtrim( THEMES_DIR, '/' ) . '/*',  GLOB_ONLYDIR );
        $this->count    = count( $themes );
        $themesList     = [];

        foreach( $themes as $file ) {
            $fName = basename( $file );
            $themesList[$fName] = array_merge( [ 'id' => $fName, 'activated' => ( $this->themeName == $fName ?: false ), 'path' => $file ], $this->getTempThemeInfo() );
        }
        
        $this->themesList  = filters()->do_filter( 'themes_list', $themesList );

        return $this;
    }

    public function getTheme( $theme ) {
        $this->info     = [];
        $this->hasjson  = false;

        $fileName       = rtrim( DIR, '/' ) . '/' . rtrim( THEMES_DIR, '/' ) . '/' . $theme . '/functions-dev.php';
        if( !file_exists(  $fileName ) )
        return ;
        
        if( ( $path = $this->themeExists( $theme ) ) ) {
            if( ( $jsonf = glob( $path . '/*.json' ) ) && isset( $jsonf[0] ) ) {
                $this->info     = json_decode( file_get_contents( $jsonf[0] ), true );
                $this->hasjson  = true;
            }

            $this->info['id']       = $theme;
            $this->info['activated']= ( $this->themeName == $fileName ?: false );
            $this->info['path']     = $path;
            
            $this->newTempTheme( $theme );

            require $fileName;

            return true;
        }

        return false;
    }

    public function readTheme( string $theme ) {
        $this->info     = [];
        $this->hasjson  = false;

        if( ( $path = $this->themeExists( $theme ) ) ) {

            $this->info['id']       = $theme;
            $this->info['activated']= ( $this->themeName == $theme ?: false );
            $this->info['path']     = $path;

            if( ( $jsonf = glob( $path . '/*.json' ) ) && isset( $jsonf[0] ) ) {
                $this->info     = array_merge( $this->info, json_decode( file_get_contents( $jsonf[0] ), true ) );
                $this->hasjson  = true;
            }
            
            return true;
        }

        return false;
    }

    private function readCurrentTheme() {
        $this->websiteURL   = rtrim( \get_option( 'site_url' ), '/' );
        $this->themeName    = \get_option( 'theme_name' );

        if( ( $path = $this->themeExists( $this->themeName ) ) ) {
            if( ( $jsonf = glob( $path . '/*.json' ) ) && isset( $jsonf[0] ) ) {
                $this->themeHasjson = true;
                $this->themeInfo    = json_decode( file_get_contents( $jsonf[0] ), true );
            }

            require_once theme_dir( 'functions-dev.php' );

            return true;
        }
    }

    /** ACTIONS */

    public function install() {
        if( !extension_loaded( 'zip' ) )
        throw new \Exception( t( 'Zip extension not installed on your server' ) );
        
        $path       = rtrim( DIR, '/' ) . '/' . rtrim( THEMES_DIR, '/' );

        if( !is_writable( $path ) )
        throw new \Exception( sprintf( t( 'Directory "%s" is not writable' ), $path ) );

        $form   = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:install-theme', [ 
            'theme'  => [ 'type' => 'file', 'label' => t( 'Theme' ), 'category' => '', 'accept' => '.zip' ] ] 
        ) );
        if( !empty( $_POST['data'] ) )
        $form   ->setValues( $_POST['data'] );
        $form   ->build();

        $rfiles = $form->getFileRequests();

        if( empty( $rfiles ) || ( ( $theme = current( $rfiles['data[theme]'] ) ) && empty( $theme['tmp_name'] ) ) )
        throw new \Exception( t( 'Invalid archive' ) );
        
        $archive    = new \ZipArchive;
        $open       = $archive->open( $theme['tmp_name'] );

        if( $open !== TRUE )
        throw new \Exception( t( 'Invalid archive' ) );

        $themeName  = NULL;
        $themePath  = '';
        $aFiles     = [];

        for( $i = 0; $i < $archive->count(); $i++ ) {
            $file   = $archive->statIndex( $i );
            $eFile  = explode( '/', $file['name'] );
            $fName  = array_pop( $eFile );
            if( $fName == 'functions-dev.php' && !$themeName ) {
                $themeName = end( $eFile );
                $themePath = implode( '/', $eFile );
            }
            $aFiles[]   = $file['name'];
        }

        if( $themeName === NULL ) {
            $archive    ->close();
            throw new \Exception( t( 'Functions file is missing' ) );
        } else if( !$themeName ) {
            $themeName  = uniqid( 'theme_' );
            $pFiles     = $aFiles;
        } else {
            if( file_exists( $path . '/' . $themeName ) ) {
                $archive    ->close();
                throw new \Exception( t( 'This theme is already installed' ) );
            }
            $pFiles = [];
            array_map( function( $v ) use ( $themeName, $themePath, &$pFiles ) {
                if( strpos( $v, $themePath . '/' ) === 0 )
                $pFiles[] = $v;
            }, $aFiles );
        }

        $path       = rtrim( DIR, '/' ) . '/' . rtrim( THEMES_DIR, '/' );
        $target     = $path . '/' . $themeName;

        mkdir( $target );

        foreach( $pFiles as $file ) {
            $fileinfo = pathinfo( $file );
            if( empty( $fileinfo['extension'] ) ) {
                if( $fileinfo['dirname'] == '.' )
                    mkdir( $target . '/' . $fileinfo['basename'] );
                else if( $themePath !== $fileinfo['dirname'] . '/' . $fileinfo['basename'] ) {
                    mkdir( $target . '/' . str_replace( $themePath, '', $fileinfo['dirname'] ) . '/' . $fileinfo['basename'] );
                }
            }
        }

        foreach( $pFiles as $file ) {
            $fileinfo = pathinfo( $file );
            if( !empty( $fileinfo['extension'] ) )
            copy( 'zip://' . $theme['tmp_name'] . '#' . $file, $target . '/' . str_replace( $themePath, '', $file ) ); 
        }

        $archive    ->close();

        require $target . '/functions-dev.php';

        $this->isInstall();

        return true;
    }

    public function activate() {
        if( $this->isActivated() )
        return true;

        $save = me()->website_options()->saveOption( 'theme_name', $this->getId() );

        if( $save )
        $this->isActivate();

        return $save;
    }

    public function deactivate() {
        return $this->isDeactivate();
    }

    public function delete() {
        if( $this->isActivated() )
        return true;

        $this->checkUninstall();

        $d = \util\etc::delete_directory( $this->info['path'] );

        if( $d )
        $this->isUninstall();

        return $d;
    }

    /** GLOBAL VARIABLES */

    public function getSiteURL() {
        return $this->websiteURL;
    }

    /** FETCH THEMES */

    public function fetch( int $max = 0, bool $pagination = true ) {
        if( $max && $pagination ) {
            $this->count = $max;
        }

        $count = $this->count();
            
        if( !$count ) return [];

        $per_page       = $this->itemsPerPage();
        $total_pages    = ceil( $count / $per_page );
        $current_page   = ( $this->current_page !== false ? $this->current_page : ( !empty( $_GET['page'] ) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1 ) );
        $current_page   = min( $current_page, $total_pages );

        $this->pagination = [
            'items_per_page'=> $per_page,
            'total_pages'   => $total_pages,
            'current_page'  => $current_page
        ];

        $this->setPagination( $this->pagination );

        if( $max === 0 || ( $max > 0 && $pagination ) )
            $this->themesList = array_slice( $this->themesList, ( ( $current_page - 1 ) * $per_page ), $per_page );
        else if( $max > 0 && !$pagination )
            $this->themesList = array_splice( $this->themesList, $max );

        return $this->themesList;
    }

}
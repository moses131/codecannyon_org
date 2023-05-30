<?php

namespace site;

class plugins extends \dev\plugins {

    private $info           = [];
    private $pluginsList    = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;

    function __construct() {
        global $plugins;
        $plugins    = $this;
        $this       ->requirePlugins();
    }

    public function getId() {
        return ( $this->info['id'] ?? false );
    }

    public function getName() {
        return ( $this->info['name'] ?? $this->getId() );
    }
    
    public function getPath() {
        return ( $this->info['path'] ?? false );
    }

    public function getAuthor() {
        return ( $this->info['author'] ?? false );
    }

    public function getPreview() {
        return ( isset( $this->info['preview'] ) ? site_url( [ THEMES_DIR, $this->getId(), $this->info['preview'] ] ) : false );
    }

    public function getPreviewMarkup() {
        $preview = $this->getPreview();

        if( !$preview ) {
            $preview = filters()->do_filter( 'default_plugin_preview', '<div class="avt avt-' . strtoupper( $this->getName()[0] ) . '"><span>' . strtoupper( $this->getName()[0] ) . '</span></div>' );
        }

        return filters()->do_filter( 'default_plugin_preview', $preview );
    }

    public function getAuthorURL() {
        return ( $this->info['author_url'] ?? false );
    }

    public function getVersion() {
        return ( $this->info['version'] ?? false );
    }

    public function getDescription() {
        return ( $this->info['description'] ?? false );
    }

    public function getListLink() {
        return ( isset( $this->info['list_link'] ) ? $this->info['list_link'] : '#' );
    }

    public function getRequiresPHPVersion() {
        return ( $this->info['php_version'] ?? false );
    }

    public function isActivated() {
        return $this->info['activated'];
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
        return filters()->do_filter( 'plugins_per_page', $this->items_per_page );
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

    public function pluginExists( $plugin ) {
        $dir = rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' );
        if( file_exists( $dir . '/' . $plugin ) || file_exists( $dir . '/' . $plugin . '-activated' ) )
        return true;
        return false;
    }

    public function getPlugins() {
        $plugins        = glob( rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' ) . '/*',  GLOB_ONLYDIR );
        $this->count    = count( $plugins );
        $pluginsList    = [];

        foreach( $plugins as $file ) {
            $fName = basename( $file );

            if( preg_match( '/(.*)-activated$/i', $fName, $name ) ) {
                $id = $name[1];
                $ac = true;
            } else {
                $id = $fName;
                $ac = false;
            }

            $this->newTempPlugin( $id );

            require $file . '/functions.php';

            $pluginsList[$id] = array_merge( [ 'id' => $fName, 'name' => $id, 'activated' => $ac, 'path' => $file ], $this->getTempPluginInfo() );
        }
        
        $this->pluginsList  = filters()->do_filter( 'plugins_list', $pluginsList );

        return $this;
    }

    public function requirePlugins() {
        $plugins    = glob( rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' ) . '/*-activated/functions.php' );
        $plugins    = filters()->do_filter( 'active_plugins_list', $plugins );

        foreach( $plugins as $file ) {
            $fName = basename( dirname( $file ) );

            preg_match( '/(.*)-activated$/i', $fName, $name );

            $this->cursorIn( $fName );
            require_once $file;

            $this->info = array_merge( [ 'id' => $name[1], 'name' => $fName, 'activated' => 1, 'path' => dirname( $file ) ], $this->getTempPluginInfo() );

            $this->isActive();
            $this->cursorOut();
        }
    }

    public function getPlugin( string $plugin ) {
        $fileName       = rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' ) . '/' . $plugin . '/functions.php';
        if( !file_exists(  $fileName ) )
        return ;

        if( preg_match( '/(.*)-activated$/i', $plugin, $name ) ) {
            $id = $name[1];
            $ac = true;
        } else {
            $id = $fileName;
            $ac = false;
        }

        $this->newTempPlugin( $id );

        require $fileName;

        $this->info = array_merge( [ 'id' => $plugin, 'name' => $id, 'activated' => $ac, 'path' => dirname( $fileName ) ], $this->getTempPluginInfo() );

        return true;
    }

    /** ACTIONS */

    public function install() {
        if( !extension_loaded( 'zip' ) )
        throw new \Exception( t( 'Zip extension not installed on your server' ) );

        $path       = rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' );

        if( !is_writable( $path ) )
        throw new \Exception( sprintf( t( 'Directory "%s" is not writable' ), $path ) );

        $form   = new \markup\front_end\form_fields( filters()->do_filter( 'form:fields:install-plugin', [ 
            'plugin'  => [ 'type' => 'file', 'label' => t( 'Plugin' ), 'category' => '', 'accept' => '.zip' ] ] 
        ) );
        if( !empty( $_POST['data'] ) )
        $form   ->setValues( $_POST['data'] );
        $form   ->build();

        $rfiles = $form->getFileRequests();

        if( empty( $rfiles ) || ( ( $plugin = current( $rfiles['data[plugin]'] ) ) && empty( $plugin['tmp_name'] ) ) )
        throw new \Exception( t( 'Invalid archive' ) );
        
        $archive    = new \ZipArchive;
        $open       = $archive->open( $plugin['tmp_name'] );

        if( $open !== TRUE )
        throw new \Exception( t( 'Invalid archive' ) );

        $pluginName = NULL;
        $pluginPath = '';
        $aFiles     = [];

        for( $i = 0; $i < $archive->count(); $i++ ) {
            $file   = $archive->statIndex( $i );
            $eFile  = explode( '/', $file['name'] );
            $fName  = array_pop( $eFile );
            if( $fName == 'functions.php' && !$pluginName ) {
                $pluginName = end( $eFile );
                $pluginPath = implode( '/', $eFile );
            }
            $aFiles[]   = $file['name'];
        }

        if( $pluginName === NULL ) {
            $archive    ->close();
            throw new \Exception( t( 'Functions file is missing' ) );
        } else if( !$pluginName ) {
            $pluginName = uniqid( 'plugin_' );
            $pFiles     = $aFiles;
        } else {
            if( file_exists( $path . '/' . $pluginName ) ) {
                $archive    ->close();
                throw new \Exception( t( 'This plugin is already installed' ) );
            }
            $pFiles = [];
            array_map( function( $v ) use ( $pluginName, $pluginPath, &$pFiles ) {
                if( strpos( $v, $pluginPath . '/' ) === 0 )
                $pFiles[] = $v;
            }, $aFiles );
        }

        $path       = rtrim( DIR, '/' ) . '/' . rtrim( PLUGINS_DIR, '/' );
        $target     = $path . '/' . $pluginName;

        mkdir( $target );

        foreach( $pFiles as $file ) {
            $fileinfo = pathinfo( $file );
            if( empty( $fileinfo['extension'] ) ) {
                if( $fileinfo['dirname'] == '.' ) {
                    // main directory
                } else if( $pluginPath !== $fileinfo['dirname'] . '/' . $fileinfo['basename'] )
                    mkdir( $target . '/' . str_replace( $pluginPath, '', $fileinfo['dirname'] ) . '/' . $fileinfo['basename'] );
            }
        }

        foreach( $pFiles as $file ) {
            $fileinfo = pathinfo( $file );
            if( !empty( $fileinfo['extension'] ) )
            copy( 'zip://' . $plugin['tmp_name'] . '#' . $file, $target . '/' . str_replace( $pluginPath, '', $file ) ); 
        }

        $archive    ->close();

        require $target . '/functions.php';

        $this->isInstall();

        return true;
    }

    public function activate() {
        if( $this->isActivated() )
        return true;

        $r = rename( $this->info['path'], $this->info['path'] . '-activated' );

        if( $r ) 
        $this->isActivate();

        return $r;
    }

    public function deactivate() {
        if( !$this->isActivated() )
        return true;

        $r = rename( $this->info['path'], substr( $this->info['path'], 0, strripos( $this->info['path'], '-activated' ) ) );

        if( $r ) 
        $this->isDeactivate();

        return $r;
    }

    public function delete() {
        $this->checkUninstall();

        $d = \util\etc::delete_directory( $this->info['path'] );

        if( $d )
        $this->isUninstall();

        return $d;
    }

    /** FETCH PLUGINS */

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
            $this->pluginsList = array_slice( $this->pluginsList, ( ( $current_page - 1 ) * $per_page ), $per_page );
        else if( $max > 0 && !$pagination )
            $this->pluginsList = array_splice( $this->pluginsList, $max );

        return $this->pluginsList;
    }

}
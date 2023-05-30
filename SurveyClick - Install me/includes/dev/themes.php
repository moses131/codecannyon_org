<?php

namespace dev;

class themes {

    private     $theme;
    private     $temp_theme;
    protected   $options;
    private     $on_load; 

    protected function newTempTheme( string $theme ) {
        $this->theme        = $theme;
        $this->temp_theme   = NULL;
    }

    public function setInfo( array $info ) {
        $this->temp_theme['info']  = $info;
        return $this;
    }

    public function setRequiresPHPVersion( string $php_version ) {
        $this->temp_theme['info']['php_version'] = $php_version;
        return $this;
    }

    public function onInstall( callable $callback ) {
        $this->temp_theme['onInstall'] = $callback;
        return $this;
    }

    protected function isInstall() {
        if( isset( $this->temp_theme['onInstall'] ) )
        return call_user_func( $this->temp_theme['onInstall'] );
    }

    public function onUninstall( callable $callback ) {
        $this->temp_theme['onUninstall'] = $callback;
        return $this;
    }

    public function onUninstallRequest( callable $callback ) {
        $this->temp_theme['checkUninstall'] = $callback;
        return $this;
    }

    protected function isUninstall() {
        if( isset( $this->temp_theme['onUninstall'] ) )
        return call_user_func( $this->temp_theme['onUninstall'] );
    }

    protected function checkUninstall() {
        if( isset( $this->temp_theme['checkUninstall'] ) )
        return call_user_func( $this->temp_theme['checkUninstall'] );
    }

    public function onActivate( callable $callback ) {
        $this->temp_theme['onActivate'] = $callback;
        return $this;
    }

    protected function isActivate() {
        if( isset( $this->temp_theme['onActivate'] ) )
        return call_user_func( $this->temp_theme['onActivate'] );
    }

    public function onDeactivate( callable $callback ) {
        $this->temp_theme['onDeactivate'] = $callback;
        return $this;
    }

    public function isDeactivate() {
        if( isset( $this->temp_theme['onDeactivate'] ) )
        return call_user_func( $this->temp_theme['onDeactivate'] );
    }

    public function onLoad( callable $callback ) {
        $this->on_load[] = $callback;
        return $this;
    }

    public function options( string $name, callable $callback, int $position = 99 ) {
        $this->options[$name] = [ 'callback' => $callback, 'position' => $position ];
    }

    public function getOptions( string $label ) {
        if( !isset( $this->options[$label] ) )
        return;

        $defOptions = $this->options[$label] ?? [];

        return call_user_func( $this->options[$label]['callback'], $defOptions );
    }

    public function buildOptions() {
        filters()->add_filter( [ 'admin_nav', 'owner_nav' ], function( $f, $nav ) {
            if( !$this->options )
            return $nav;
            
            $nav['ws_theme_options'] = [ 
                'type'      => 'link', 
                'url'       => admin_url(), 
                'label'     => t( 'Theme options' ), 
                'position'  => 3,
                'parent_id' => 'ws_themes'
            ];

            foreach( $this->options as $name => $v ) {
                $nav['theme_options_'  . $name] = [ 
                    'type'      => 'link', 
                    'url'       => admin_url(), 
                    'label'     => esc_html( $name ), 
                    'position'  => $v['position'],
                    'parent_id' => 'ws_theme_options',
                    'attrs'     => [ 'data-popup' => 'website-actions', 'data-data' => [ 'action' => 'theme-options', 'label' => esc_html( $name ) ] ],                    
                ];
            }

            return $nav;
        } );
    }

    public function getTempThemeInfo() {
        return ( $this->temp_theme['info'] ?? [] );
    }

    public function loaded() {
        if( $this->on_load )
        foreach( $this->on_load as $callback ) {
            call_user_func( $callback );
        }

        $functions = theme_dir( 'functions.php' );
        if( file_exists( $functions ) )
        require_once $functions;
    }

}
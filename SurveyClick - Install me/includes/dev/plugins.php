<?php

namespace dev;

class plugins {

    private $plugin; // current plugin
    private $temp_plugin;
    protected $plugins = [];

    protected function cursorIn( string $plugin ) {
        $this->plugin   = $plugin;
        return $this;
    }

    protected function cursorOut() {
        $this->plugins[$this->plugin] = $this->temp_plugin;
        $this->plugin       = NULL;
        $this->temp_plugin  = NULL;
        return $this;
    }

    protected function newTempPlugin( string $plugin ) {
        $this->plugin       = $plugin;
        $this->temp_plugin  = NULL;
    }

    public function setInfo( array $info ) {
        $this->temp_plugin['info']  = $info;
        return $this;
    }

    public function setName( string $name ) {
        $this->temp_plugin['info']['name'] = $name;
        return $this;
    }

    public function setVersion( string $version ) {
        $this->temp_plugin['info']['version'] = $version;
        return $this;
    }

    public function setDescription( string $description ) {
        $this->temp_plugin['info']['description'] = $description;
        return $this;
    }

    public function setListLink( string $link ) {
        $this->temp_plugin['info']['list_link'] = $link;
        return $this;
    }

    public function setAuthor( string $author ) {
        $this->temp_plugin['info']['author'] = $author;
        return $this;
    }

    public function setAuthorURL( string $author_url ) {
        $this->temp_plugin['info']['author_url'] = $author_url;
        return $this;
    }

    public function setRequiresPHPVersion( string $php_version ) {
        $this->temp_plugin['info']['php_version'] = $php_version;
        return $this;
    }

    public function active( callable $callback ) {
        $this->temp_plugin['active'] = $callback;
        return $this;
    }

    protected function isActive() {
        if( isset( $this->temp_plugin['active'] ) )
        return call_user_func( $this->temp_plugin['active'] );
    }

    public function onInstall( callable $callback ) {
        $this->temp_plugin['onInstall'] = $callback;
        return $this;
    }

    protected function isInstall() {
        if( isset( $this->temp_plugin['onInstall'] ) )
        return call_user_func( $this->temp_plugin['onInstall'] );
    }

    public function onUninstall( callable $callback ) {
        $this->temp_plugin['onUninstall'] = $callback;
        return $this;
    }

    public function onUninstallRequest( callable $callback ) {
        $this->temp_plugin['checkUninstall'] = $callback;
        return $this;
    }

    protected function isUninstall() {
        if( isset( $this->temp_plugin['onUninstall'] ) )
        return call_user_func( $this->temp_plugin['onUninstall'] );
    }

    protected function checkUninstall() {
        if( isset( $this->temp_plugin['checkUninstall'] ) )
        return call_user_func( $this->temp_plugin['checkUninstall'] );
    }

    public function onActivate( callable $callback ) {
        $this->temp_plugin['onActivate'] = $callback;
        return $this;
    }

    protected function isActivate() {
        if( isset( $this->temp_plugin['onActivate'] ) )
        return call_user_func( $this->temp_plugin['onActivate'] );
    }

    public function onDeactivate( callable $callback ) {
        $this->temp_plugin['onDeactivate'] = $callback;
        return $this;
    }

    public function isDeactivate() {
        if( isset( $this->temp_plugin['onDeactivate'] ) )
        return call_user_func( $this->temp_plugin['onDeactivate'] );
    }

    public function getTempPluginInfo() {
        return ( $this->temp_plugin['info'] ?? [] );
    }

    public function allActivePlugins() {
        return $this->plugins;
    }

}
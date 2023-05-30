<?php

namespace dev;

class developer extends builder {

    private $text_domains = [];

    function __construct() {
        $this->addTextDomain( 'main' );
    }

    public function addTextDomain( string $location, string $dir = '' ) {
        if( $dir == '' )
        $dir = DIR . '/locale';

        if( is_dir( $dir ) ) {
            $this->text_domains[$location] = $dir;
            $dir = bindtextdomain( $location, $dir );
            bind_textdomain_codeset( $location, 'UTF-8' );
            return $dir;
        }

        return false;
    }

    public function getTextDomains() {
        return $this->text_domains;
    }

}
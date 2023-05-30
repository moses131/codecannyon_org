<?php

namespace util;

class connector {

    private $URL;
    private $Headers;
    private $HTTPCode;
    private $GETOptions     = [];
    private $Content;
    private $TimeOut        = 4;
    private $ReturnT        = true;
    private $SSLVerify      = false;
    private $Method         = 'GET';
    private $PostFields     = [];

    function __construct( string $URL, bool $useDefaults = true ) {
        $this->URL          = $URL;
        if( $useDefaults )
        $this->Headers[]    = 'Content-Type: application/x-www-form-urlencoded';
    }

    public function changeURL( string $URL ) {
        $this->URL          = $URL;
        return $this;
    }

    public function changeTimeOut( int $timeout ) {
        $this->TimeOut      = $timeout;
        return $this;
    }

    public function setAuthorization( string $auth ) {
        $this->Headers[]    = 'Authorization: ' . $auth;
        return $this;
    }

    public function setPostFields( array $fields ) {
        $this->PostFields   = array_merge( $this->PostFields, $fields );
        return $this;
    }

    public function setMethod( string $method ) {{
        $this->Method       = $method;
        return $this;
    }} 

    public function resetHeaders() {
        $this->Headers      = [];
        return $this;
    }

    public function returnTransfer( bool $return ) {
        $this->ReturnT      = $return;
        return $this;
    }

    public function SSLVerify( bool $verify ) {
        $this->SSLVerify    = $verify;
        return $this;
    }

    public function setGETOptions( array $options ) {
        $this->GETOptions   = array_merge( $this->GETOptions, $options );
        return $this;
    }

    public function getHTTPCode() {
        return $this->HTTPCode;
    }

    public function getContent() {
        return $this->Content;
    }

    public function getContentJson() {
        if( $this->Content && ( $content = json_decode( $this->Content, true ) ) )
        return $content;
    }

    public function Open() {
        $conn = filters()->value( 'web-connector', _get_update( $this->GETOptions, $this->URL ), [
            'post'              => $this->PostFields,
            'method'            => $this->Method,
            'timeout'           => $this->TimeOut,
            'headers'           => $this->Headers,
            'ssl_verify'        => $this->SSLVerify,
            'return_transfer'   => $this->ReturnT
        ] );

        if( isset( $conn['http_code'] ) )
        $this->HTTPCode = $conn['http_code'];

        if( isset( $conn['content'] ) ) {
            $this->Content  = $conn['content'];
            return true;
        }

        return false;
    }

}
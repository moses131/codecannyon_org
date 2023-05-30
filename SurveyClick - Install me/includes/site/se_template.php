<?php

namespace site;

class se_template {

    private $language;
    private $templates_dir;
    private $current_template;
    private $is_editable;
    private $is_base64_encoded;

    function __construct() {
        $this->language      = getUserLanguage( 'locale_e' );
        $this->templates_dir = implode( '/', [ DIR, MAIL_TMPS_DIR ] );
    }

    public function setCurrent( array $current ) {
        $this->current_template = $current;
        return $this;
    }

    public function setLanguage( string $language ) {
        $this->language = $language;
        return $this;
    }

    public function setTemplatesDir( string $dir ) {
        $this->templates_dir = $dir;
        return $this;
    }

    public function getCurrent() {
        return $this->current_template;
    }

    public function getName() {
        return $this->parseLine( $this->current_template['name'] );
    }

    public function getDescription() {
        if( !isset( $this->current_template['description'] ) )
        return false;
        return $this->parseLine( $this->current_template['description'] );
    }

    public function getLanguage() {
        if( !isset( $this->current_template['language'] ) )
        return false;
        return $this->parseLine( $this->current_template['language'] );
    }

    public function getFromName() {
        if( !isset( $this->current_template['email']['from_name'] ) )
        return false;
        return $this->parseLine( $this->current_template['email']['from_name'] );
    }

    public function getFromEmailAddress() {
        if( !isset( $this->current_template['email']['from_email'] ) )
        return false;
        return $this->parseLine( $this->current_template['email']['from_email'] );
    }

    public function getSubject() {
        if( !isset( $this->current_template['email']['subject'] ) )
        return ;
        return $this->parseLine( $this->current_template['email']['subject'] );
    }

    public function getBody() {
        if( !isset( $this->current_template['email']['body'] ) )
        return false;
        return $this->parseLine( $this->current_template['email']['body'] );
    }

    public function setFromName( $string ) {
        $this   ->getFromName();
        if( !$this->isEditable() )
        return $this;
        $this   ->current_template['email']['from_name'] = ( $this->isEditable() ? '@@edt ' : '' ) . ( $this->isBase64Encoded() ? '@@b64 ' : '' ) . ( $this->isBase64Encoded() ? base64_encode( $string ) : $string );
        return $this;
    }

    public function setFromEmailAddress( $string ) {
        $this   ->getFromEmailAddress();
        if( !$this->isEditable() )
        return $this;
        $this   ->current_template['email']['from_email'] = ( $this->isEditable() ? '@@edt ' : '' ) . ( $this->isBase64Encoded() ? '@@b64 ' : '' ) . ( $this->isBase64Encoded() ? base64_encode( $string ) : $string );
        return $this;
    }

    public function setSubject( string $string ) {
        $this   ->getSubject();
        if( !$this->isEditable() )
        return $this;
        $this   ->current_template['email']['subject'] = ( $this->isEditable() ? '@@edt ' : '' ) . ( $this->isBase64Encoded() ? '@@b64 ' : '' ) . ( $this->isBase64Encoded() ? base64_encode( $string ) : $string );
        return $this;
    }

    public function setBody( string $string ) {
        $this   ->getBody();
        if( !$this->isEditable() )
        return $this;
        $this   ->current_template['email']['body'] = ( $this->isEditable() ? '@@edt ' : '' ) . ( $this->isBase64Encoded() ? '@@b64 ' : '' ) . ( $this->isBase64Encoded() ? base64_encode( $string ) : $string );
        return $this;
    }

    public function isEditable() {
        return $this->is_editable;
    }

    public function isBase64Encoded() {
        return $this->is_base64_encoded;
    }

    public function getShortcodesMap() {
        $map = [];
        foreach( $this->current_template['s_map'] as $key => $val ) {
            $map[$key] = $this->parseLine( $val );
        }
        return $map;
    }

    private function parseLine( string $line ) {
        preg_match_all( '/@@([^\s]*)/', $line, $array );

        $this->is_editable = $this->is_base64_encoded = false;

        if( !empty( $array[1] ) ) {
            $line = preg_replace( '/(@@[^\s]*)\s+/', '', $line );

            foreach( $array[1] as $opt ) {
                switch( $opt ) {
                    case 'edt':
                        $this->is_editable = true;
                    break;

                    case 'b64':
                        $line = base64_decode( $line );
                        $this->is_base64_encoded = true;
                    break;
                }
            }
        }

        return $line;
    }

    private function getTemplateFiles( string $type = '*' ) {
        $files  = glob( rtrim( $this->templates_dir, '/' ) . '/' . $type . '.se_template' );
        $files  = array_merge( $files, filters()->do_filter( 'email-templates', [] ) );
        $f      = [];

        foreach( $files as $file ) {
            $file_path = pathinfo( $file );
            $f[$file_path['filename']] = $file;
        }
        
        return $f;
    }

    public function getTemplates( string $type = '*' ) {
        $tpls   = [];
        $tlist  = $this->getTemplateFiles( $type );

        array_walk( $tlist, function( $v, $k ) use ( &$tpls ) {
            if( file_exists( $v ) ) {
                $c          = file_get_contents( $v );
                $c          = ( !empty( $c ) ? json_decode( $c, true ) : false );
                if( $c ) {
                    $c['URL']   = $v;
                    $tpls[$k]   = $c;
                }
            }
        } );
        
        return $tpls;
    }

    public function getTemplate( string $template, bool $strict = false ) {
        $file   = '';
        $files  = $this->getTemplateFiles();

        if( !$strict && isset( $files[$template . '_' . $this->language] ) ) {
            $file = $files[$template . '_' . $this->language];
        } else if( isset( $files[$template] ) ) {
            $file = $files[$template];
        }

        if( $file == '' || !file_exists( $file ) )
        throw new \Exception( t( 'Template cannot be found' ) );

        $c  = file_get_contents( $file );
        $c  = ( !empty( $c ) ? json_decode( $c, true ) : false );

        if( !$c )
        throw new \Exception( t( 'Template cannot be found' ) );

        $c['URL'] = $file;
        $this->setCurrent( $c );

        return true;
    }

    public function save() {
        if( !$this->current_template )
        throw new \Exception( t( 'No template selected' ) );
        if( !isset( $this->current_template['URL'] ) || !is_writable( $this->current_template['URL'] ) )
        throw new \Exception( t( 'This template cannot be modified' ) );

        $URL = $this->current_template['URL'];
        unset( $this->current_template['URL'] );

        if( file_put_contents( $URL, cms_json_encode( $this->current_template ) ) )
        return true;
     
        throw new \Exception( t( 'Unexpected' ) );
    }

}
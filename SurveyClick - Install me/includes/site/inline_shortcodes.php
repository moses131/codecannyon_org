<?php

namespace site;

class inline_shortcodes {

    private $content;
    private $shortcodes;
    private $cshortcodes = [];
    private $custom_cb;

    function __construct( string $content = NULL ) {
        $this->shortcodes   = filters()->do_filter( 'inline_shortcodes_list', [
            'b'     => [ 'build' => [ $this, 'sh_b' ] ],
            'u'     => [ 'build' => [ $this, 'sh_u' ] ],
            'i'     => [ 'build' => [ $this, 'sh_i' ] ]
        ] );
        if( $content )
        $this->content  = $content;
    }

    public function setInlineContent( string $content ) {
        $this->content  = $content;
        return $this;
    }

    private function sh_b( $full, $content, $attrs, $text ) {
        return str_replace( $full, '<strong>' . $this->build( $content ) . '</strong>', $text );
    }

    private function sh_u( $full, $content, $attrs, $text ) {
        return str_replace( $full, '<u>' . $this->build( $content ) . '</u>', $text );
    }

    private function sh_i( $full, $content, $attrs, $text ) {
        return str_replace( $full, '<i>' . $this->build( $content ) . '</i>', $text );
    }

    public function setCustom( array $custom ) {
        $this->cshortcodes = array_merge( $this->cshortcodes, $custom );
        return $this;
    }

    public function removeCustom() {
        $this->cshortcodes = NULL;
        return $this;
    }

    public function removeCustomShortcode( string $key ) {
        unset( $this->cshortcodes[$key] );
        return $this;
    }

    public function setCustomCallback( callable $custom_cb ) {
        $this->custom_cb = $custom_cb;
        return $this;
    }

    public function removeCustomCallback() {
        $this->custom_cb = NULL;
        return $this;
    }

    private function checkCustom( $sh, $full, $content, $attrs, $text ) {
        if( isset( $this->cshortcodes[$sh] ) )
        return str_replace( $full, $this->cshortcodes[$sh], $text );
        else if( $this->custom_cb )
        return call_user_func( $this->custom_cb, $sh, $full, $content, $attrs, $text );
        else return $text;
    }

    private function build( $text ) {
        preg_match_all( '/\[\b([\w\d\-\_\,]+)\b(\=\&quot;(.*?)\&quot;)?(.*?)?\]((.*?)\[\/\1\])?/is', $text, $findings );
        if( !empty( $findings[1] ) ) {
            foreach( $findings[1] as $i => $value ) {
                if( isset( $this->shortcodes[$findings[1][$i]] ) )
                $text   = call_user_func( $this->shortcodes[$findings[1][$i]]['build'], $findings[0][$i], $findings[6][$i], $findings[4][$i], $text );
                else
                $text   = $this->checkCustom( $findings[1][$i], $findings[0][$i], $findings[6][$i], $findings[4][$i], $text );
            }
        }

        return $text;
    }

    public function inlineMarkup() {
        return $this->build( $this->content );
    }

}
<?php

namespace site;

class templates {

    private $templates      = [];
    private $defTemplates   = NULL;

    public function newTemplate( string $id, array $options ) {
        $this->templates[$id] = $options;
        return $this;
    }

    public function getTemplates( string $lang = NULL ) {
        if( !$this->defTemplates )
        $this->defTemplates = site()->app->defaultTemplates();

        $templates = array_merge( $this->defTemplates, $this->templates );

        if( $lang ) {
            foreach( $templates as $id => $template ) {
                if( isset( $template['lang'] ) && ( ( is_array( $template['lang'] ) && !in_array( $lang, $template['lang'] ) ) || ( is_string( $template['lang'] ) && $template['lang'] != $lang ) ) )
                unset( $templates[$id] );
            }
        }

        uasort( $templates, function( $a, $b ) {
            if( !isset( $a['importance'] ) ) $a['importance'] = 99;
            if( !isset( $b['importance'] ) ) $b['importance'] = 99;
            if( (double) $a['importance'] === (double) $b['importance'] ) return 0;
            return ( (double) $a['importance'] < (double) $b['importance'] ? -1 : 1 );
        } );

        return $templates;
    }

    public function getTemplate( string $id ) {
        if( !$this->defTemplates )
        $this->defTemplates = site()->app->defaultTemplates();

        $templates = array_merge( $this->defTemplates, $this->templates );
        return ( $templates[$id] ?? NULL );
    }

}
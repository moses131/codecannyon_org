<?php

namespace util;

class build_url_path {

    private $values     = [];
    private $accepted   = [];

    function __construct( array $accepted = [], $path = '' ) {
        $this->accepted = $accepted;
        if( !empty( $path ) )
        $this->update_path( $path );
    }

    private function update_path( $path ) {
        if( is_array( $path ) ) {
            $path = array_values( $path );
        } else if( is_string( $path ) ) {
            $path = explode( '/', $path );
        } else return ;

        $i = 0;

        foreach( $this->accepted as $id => $type ) {
            if( $type == 'dir' ) {
                if( isset( $path[$i] ) )
                $this->values[$id] = $path[$i];
            } else if( $type == 'int' && ( $val = array_search( $id, $path ) ) !== false && isset( $path[++$val] ) ) {
                $this->values[$id] = (int) $path[$val];
            } else if( $type == 'string' && ( $val = array_search( $id, $path ) ) !== false && isset( $path[++$val] ) ) {
                $this->values[$id] = esc_html( $path[$val] );
            } else if( $type == 'list_int' && ( $val = array_search( $id, $path ) ) !== false && isset( $path[++$val] ) ) {
                $this->values[$id] = array_map( 'intval', explode( ',', $path[$val] ) );
            } else if( $type == 'list_string' && ( $val = array_search( $id, $path ) ) !== false && isset( $path[++$val] ) ) {
                $this->values[$id] = array_map( 'esc_html', explode( ',', $path[$val] ) );
            }

            $i++;
        }
    }

    public function getValues() {
        return $this->values;
    }

    public function getValuesJson() {
        return cms_json_encode( $this->values );
    }

    public function build() {
        return esc_url( implode( '/', $this->values ) );
    }

}
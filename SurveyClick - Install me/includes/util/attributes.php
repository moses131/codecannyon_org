<?php

namespace util;

class attributes {

    public static function add_classes( array $classes = [] ) {
        $classes = array_filter( $classes );
        $markup = '';
        if( !empty( $classes ) ) {
            $markup .= ' class="' . implode( ' ', $classes ) . '"';
        }
        return $markup;
    }

    public static function add_attributes( array $attrs = [], string $before = ' ' ) {
        $markup = '';
        if( !empty( $attrs ) ) {
            $markup .= $before;
            foreach( $attrs as $attr_name => $attr ) {
                $markup .= ' ';
                $markup .= $attr_name . ( is_array( $attr ) ? '=\'' . cms_json_encode( $attr ) . '\'' : '="' .  $attr . '"' );
            }
        }
        return $markup;
    }

    public static function parse_attributes( string $text ) {
        preg_match_all( '/([a-z0-9-_]+)=(["]|[\']|&quot;)(.*?)\\2/i', $text, $results );
        $exp_res = [];
        foreach( $results[1] as $i => $key ) {
            $exp_res[$key] = $results[3][$i];
        }
        return $exp_res;
    }

}
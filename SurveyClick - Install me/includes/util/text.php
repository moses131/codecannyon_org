<?php

namespace util;

class text {

    private $text           = '';
    private $bbcodes;
    private $BBcodesAsList  = [];

    function __construct( string $text = '' ) {
        if( $text !== '' ) {
            $this->changeText( $text );
        }
        $this->bbcodes = $this->getAvailableBBCodes();
    }

    public function getAvailableBBCodes() {
        return filters()->do_filter( 'bb-codes', [
            'strong'    => [ 'callback' => [ $this, 'bbcode_strong'     ] ],
            'i'         => [ 'callback' => [ $this, 'bbcode_i'          ] ],
            'u'         => [ 'callback' => [ $this, 'bbcode_u'          ] ],
            'quote'     => [ 'callback' => [ $this, 'bbcode_quote'      ] ],
            'code'      => [ 'callback' => [ $this, 'bbcode_code'       ] ],
            'p'         => [ 'callback' => [ $this, 'bbcode_p'          ] ],
            'ul'        => [ 'callback' => [ $this, 'bbcode_ul'         ] ],
            'ol'        => [ 'callback' => [ $this, 'bbcode_ol'         ] ],
            'li'        => [ 'callback' => [ $this, 'bbcode_li'         ] ],
            'h'         => [ 'callback' => [ $this, 'bbcode_h'          ] ],
            'img'       => [ 'callback' => [ $this, 'bbcode_img'        ] ],
        ] );
    }

    public function changeText( string $text ) {
        $this->text     = filters()->do_filter( 'text-before-formatting', $text );
        return $this;
    }

    private function bbcode_strong( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '<strong>' . $content . '</strong>', $text );
    }

    private function bbcode_i( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '<i>' . $content . '</i>', $text );
    }

    private function bbcode_u( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '<u>' . $content . '</u>', $text );
    }

    private function bbcode_p( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $attrs = \util\attributes::parse_attributes( $attributes );

        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => $attrs ];

        $div_attrs = [];

        if( !empty( $attrs['align'] ) ) {
            $div_attrs[] = 'text-align:' . $attrs['align'];
        }
        if( !empty( $attrs['indent'] ) ) {
            $div_attrs[] = 'text-indent:' . $attrs['indent'];
        }
        if( !empty( $attrs['decoration'] ) ) {
            $div_attrs[] = 'text-decoration:' . $attrs['decoration'];
        }
        if( !empty( $attrs['color'] ) ) {
            $div_attrs[] = 'color:' . $attrs['color'];
        }
        if( !empty( $attrs['style'] ) ) {
            $div_attrs[] = 'font-style:' . $attrs['style'];
        }
        if( !empty( $attrs['size'] ) ) {
            $div_attrs[] = 'font-size:' . $attrs['size'];
        }

        return str_replace( $str, '</div><div' . ( !empty( $div_attrs ) ? ' style="' .implode( ';', $div_attrs ). '"' : '' ) . '><div>' . $this->buildBB( $content, $key, ...$atts ) . '</div></div><div>', $text );
    }

    private function bbcode_quote( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $attrs = \util\attributes::parse_attributes( $attributes );

        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => $attrs ];

        return str_replace( $str, '</div><div class="quote"><div>' . $content . '</div>' . ( !empty( $attrs['author'] ) ? '<div class="mt10">&mdash; ' . $attrs['author'] . '</div>' : '' ) . '</div><div>', $text );
    }

    private function bbcode_code( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '</div><div class="code"><div>' . $content . '</div></div><div>', $text );
    }

    private function bbcode_ul( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '</div><div><ul><div>' . $this->buildBB( $this->trimContent( $content ), $key, ...$atts ) . '</div></ul></div><div>', $text );
    }

    private function bbcode_ol( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '</div><div><ol><div>' . $this->buildBB( $this->trimContent( $content ), $key, ...$atts ) . '</div></ol></div><div>', $text );
    }

    private function bbcode_li( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => \util\attributes::parse_attributes( $attributes ) ];

        return str_replace( $str, '</div><li><div>' . $this->buildBB( $content, $key, ...$atts ) . '</div></li><div>', $text );
    }

    private function bbcode_h( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $attrs = \util\attributes::parse_attributes( $attributes );

        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => $attrs ];

        $h_attrs = [];

        if( !empty( $attrs['color'] ) ) {
            $h_attrs[] = 'color:' . $attrs['color'];
        }
        if( !empty( $attrs['align'] ) ) {
            $h_attrs[] = 'text-align:' . $attrs['align'];
        }

        return str_replace( $str, '</div><div><h2' . ( !empty( $h_attrs ) ? ' style="' .implode( ';', $h_attrs ). '"' : '' ) . '>' . $this->buildBB( $this->trimContent( $content ), $key, ...$atts ) . '</h2></div><div>', $text );
    }

    private function bbcode_img( $key, $text, $str, $value, $content, $attributes, $asArray, ...$atts ) {
        $attrs = \util\attributes::parse_attributes( $attributes );

        $this->BBcodesAsList[] = [ 'type' => $key, 'content' => $content, 'value' => $value, 'attributes' => $attrs ];

        return str_replace( $str, '</div><div class="img"><img src="' . $value . '" alt="" />' . ( !empty( $attrs['source'] ) ? '<div class="source">' . sprintf( t( 'Source: %s' ), $attrs['source'] ) . '</div>' : '' ) . '</div><div>', $text );
    }

    private function trimContent( string $content ) {
        return preg_replace( '/\<div\>(\n{1,})?(\s{1,})?\<\/div\>/', '', $content );
    }

    private function buildBB( string $text, ...$atts ) {
        preg_match_all( '/\[\b(' . implode( '|', array_keys( $this->bbcodes ) ) . ')\b(\=\&quot;(.*?)&quot;)?(\s{1,}.*?)?\]((?:[^[]|\[(\=\&quot;(.*?)&quot;)?(?!\/?\1])|(?R))+)?\[\/\1\]/is', $text, $findings );
        if( !empty( $findings[1] ) ) {
            foreach( $findings[1] as $i => $value ) {
                $text = call_user_func( $this->bbcodes[$findings[1][$i]]['callback'], $findings[1][$i], $text, $findings[0][$i], $findings[3][$i], $findings[5][$i], $findings[4][$i], false, ...$atts );
            }
        }

        return $text;
    }

    private function buildBBArray( string $text, ...$atts ) {
        $this->BBcodesAsList    = [];
        $textL                  = $text;
        preg_match_all( '/\[\b(' . implode( '|', array_keys( $this->bbcodes ) ) . ')\b(\=\&quot;(.*?)&quot;)?(\s{1,}.*?)?\]((?:[^[]|\[(\=\&quot;(.*?)&quot;)?(?!\/?\1])|(?R))+)?\[\/\1\]/is', $text, $findings );

        if( !empty( $findings[1] ) ) {
            foreach( $findings[1] as $i => $value ) {
                $pos    = mb_strpos( $textL, $findings[0][$i] );
                $cont   = mb_substr( $textL, 0, $pos );
                if( trim( $cont ) !== '' ) {
                    $this->BBcodesAsList[] = [ 'type' => 'inline', 'content' => $cont ];
                }
                call_user_func( $this->bbcodes[$findings[1][$i]]['callback'], $findings[1][$i], $text, $findings[0][$i], $findings[3][$i], $findings[5][$i], $findings[4][$i], true, ...$atts );
                $textL  = mb_substr( $textL, ( $pos + mb_strlen( $findings[0][$i] ) ) );
            }
        }

        if( trim( $textL ) !== '' ) {
            $this->BBcodesAsList[] = [ 'type' => 'inline', 'content' => $textL ];
        }

        return $this->BBcodesAsList;
    }

    public function toBB() {
        return filters()->do_filter( 'text-to-bb-formatting', $this->text );
    }

    public function HTMLToBB() {
        $this->text = preg_replace( [
            '/\<div\>(.*?)(\<br\>)?\<\/div\>/is',
            '/\<br\>/is', 
            '/\n\n+/is',
            '/\<(strong|i|u)\>(.*?)\<\/\\1\>/is' ],
        [ 
            '${1}' . "\n",
            "\n",
            "\n",
            '[${1}]${2}[/${1}]'
        ], htmlspecialchars_decode( $this->text ) );

        return $this;
    }

    public function fromBB( ...$attrs ) {
        $etext = $this->buildBB( esc_html( $this->text ), ...$attrs );

        $text = '<div>';
        $text .= $etext;
        $text = preg_replace( '/\n/', '</div><div>', trim( $text ) );
        $text .= '</div>';

        return filters()->do_filter( 'text-after-formatting', $this->trimContent( $text ) );
    }

    public function fromBBArray( ...$attrs ) {
        return $this->buildBBArray( $this->text, ...$attrs );
    }

    public function updateText( string $text ) {
        $this->text = $text;
        return $this;
    }

    public function getText() {
        return $this->text;
    }

    public function getBBArray() {
        return $this->BBcodesAsList;
    }

}
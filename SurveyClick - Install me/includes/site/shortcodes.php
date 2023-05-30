<?php

namespace site;

class shortcodes extends inline_shortcodes {

    private $content;
    private $shortcodes;
    private $points;
    private $variables = [];
    private $allow;

    function __construct( string $content = NULL ) {
        parent::__construct();
        $this->shortcodes   = filters()->do_filter( 'shortcodes_list', [
            'title'     => [ 'build_array' => [ $this, 'title' ], 'build_markup' => [ $this, 'title_markup' ], 'build_shortcode' => [ $this, 'title_shortcode' ] ],
            'h'         => [ 'build_array' => [ $this, 'headline' ], 'build_markup' => [ $this, 'headline_markup' ], 'build_shortcode' => [ $this, 'headline_shortcode' ] ],
            'p'         => [ 'build_array' => [ $this, 'paragraph' ], 'build_markup' => [ $this, 'paragraph_markup' ], 'build_shortcode' => [ $this, 'paragraph_shortcode' ] ],
            'icon'      => [ 'build_array' => [ $this, 'icon' ], 'build_markup' => [ $this, 'icon_markup' ], 'build_shortcode' => [ $this, 'icon_shortcode' ] ],
            'buttons'   => [ 'build_array' => [ $this, 'buttons' ], 'build_markup' => [ $this, 'buttons_markup' ], 'build_shortcode' => [ $this, 'buttons_shortcode' ] ],
            'bigtext'   => [ 'build_array' => [ $this, 'bigtext' ], 'build_markup' => [ $this, 'bigtext_markup' ], 'build_shortcode' => [ $this, 'bigtext_shortcode' ] ]
        ] );
        if( $content )
        $this->content  = esc_html( $content );
    }

    public function setContent( string $content ) {
        $this->content  = esc_html( $content );
        return $this;
    }

    private function title( $content, $value, $attr ) {
        return [ 'type' => 'title', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function title_markup( $content, $attr ) {
        if( $this->checkConditions( $content, $attr ) )
        return '<h2 class="title">' . $this->setInlineContent( $content )->inlineMarkup() . '</h2>';
    }

    private function title_shortcode( $content, $attrs ) {
        return '[title' . \util\attributes::add_attributes( $attrs ) . ']' . $content . '[/title]';
    }

    private function headline( $content, $value, $attr ) {
        return [ 'type' => 'h', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function headline_markup( $content, $attr ) {
        if( $this->checkConditions( $content, $attr ) )
        return '<h2>' . $this->setInlineContent( $content )->inlineMarkup() . '</h2>';
    }

    private function headline_shortcode( $content, $attrs ) {
        return '[h' . \util\attributes::add_attributes( $attrs ) . ']' . $content . '[/h]';
    }

    private function paragraph( $content, $value, $attr ) {
        return [ 'type' => 'p', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function paragraph_markup( $content, $attr ) {
        if( $this->checkConditions( $content, $attr ) )
        return '<p>' . $this->setInlineContent( $content )->inlineMarkup() . '</p>';
    }

    private function paragraph_shortcode( $content, $attrs ) {
        return '[p' . \util\attributes::add_attributes( $attrs ) . ']' . $content . '[/p]';
    }

    private function icon( $content, $value, $attr ) {
        return [ 'type' => 'icon', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function icon_markup( $content, $attr ) {
        if( $this->checkConditions( $content, $attr ) )
        return '<div class="ico"><i class="' . $content . '"></i></div>';
    }

    private function icon_shortcode( $content, $attrs ) {
        return '[icon' . \util\attributes::add_attributes( $attrs ) . ']' . $content . '[/icon]';
    }

    private function buttons( $content, $value, $attr ) {
        return [ 'type' => 'buttons', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function buttons_markup( $content, $attr ) {
        $buttons = '';
        foreach( explode( "\n", $content ) as $content ) {
            if( $this->checkConditions( $content, $attr ) ) {
                $button = explode( '|', $content );
                if( count( $button ) >= 2 )
                $buttons .= '<a href="' . esc_url( trim( $button[1] ) ) . '" class="btn">' . $this->setInlineContent( trim( $button[0] ) )->inlineMarkup() . '</a>';
            }
        }
        if( $buttons != '' )
        return '<div class="btns">' . $buttons . '</div>';
    }

    private function buttons_shortcode( $content, $attrs ) {
        return '[buttons' . \util\attributes::add_attributes( $attrs ) . ']' . $this->setInlineContent( $content )->inlineMarkup() . '[/buttons]';
    }

    private function bigtext( $content, $value, $attr ) {
        return [ 'type' => 'bigtext', 'content' => $content, 'attrs' => \util\attributes::parse_attributes( $attr ) ];
    }

    private function bigtext_markup( $content, $attr ) {
        if( $this->checkConditions( $content, $attr ) )
        return '<p class="big">' . $this->setInlineContent( $content )->inlineMarkup() . '</p>';
    }

    private function bigtext_shortcode( $content, $attrs ) {
        return '[bigtext' . \util\attributes::add_attributes( $attrs ) . ']' . $content . '[/bigtext]';
    }


    public function setPoints( int $points ) {
        $this->points = $points;
        return $this;
    }

    public function setVariables( array $vars ) {
        $this->variables = array_merge( $this->variables, $vars );
        return $this;
    }

    public function toMarkupFromArray( array $lines ) {
        $content = '';
        foreach( $lines as $line ) {
            if( isset( $line['type'] ) || isset( $line['content'] ) )
            $content .= call_user_func( $this->shortcodes[$line['type']]['build_markup'], $line['content'], ( $line['attrs'] ?? [] ) );
        }
        return $content;
    }

    public function toShortcodeFromArray( array $lines ) {
        $content = '';
        foreach( $lines as $line ) {
            if( isset( $line['type'] ) && isset( $line['content'] ) && isset( $this->shortcodes[$line['type']] ) && ( !$this->allow || isset( $this->allow[$line['type']]) ) )
            $content .= call_user_func( $this->shortcodes[$line['type']]['build_shortcode'], $line['content'], ( $line['attr'] ?? [] ) );
        }
        return $content;
    }

    public function toMarkup() {
        return $this->toMarkupFromArray( $this->toArray() );
    }

    public function toArray() {
        $List   = [];
        $textL  = (string) $this->content;
        preg_match_all( '/\[\b(' . implode( '|', array_keys( $this->shortcodes ) ) . ')\b(\=\"(.*?)\")?(.*?)?\](.*?)\[\/\1\]/is', $textL, $findings );

        if( !empty( $findings[1] ) ) {
            foreach( $findings[1] as $i => $value ) {
                $pos    = mb_strpos( $textL, $findings[0][$i] );
                $cont   = mb_substr( $textL, 0, $pos );
                if( trim( $cont ) !== '' ) {
                    $List[] = [ 'type' => 'p', 'content' => trim( $cont ) ];
                }
                $List[] = call_user_func( $this->shortcodes[$findings[1][$i]]['build_array'], trim( $findings[5][$i] ), trim( $findings[3][$i] ), trim( $findings[4][$i] ) );
                $textL  = mb_substr( $textL, ( $pos + mb_strlen( $findings[0][$i] ) ) );
            }
        }

        if( trim( $textL ) !== '' ) {
            $List[] = [ 'type' => 'p', 'content' => trim( $textL ), 'attrs' => [] ];
        }

        return $List;
    }

    private function checkConditions( &$content, $attr ) {
        preg_match_all( '/\{\{([?!+-])?(.*?)\}\}/is', $content, $findings );
        if( !empty( $findings[0] ) ) {
            foreach( $findings[0] as $k => $full_str ) {
                list( $sign, $str ) = [ $findings[1][$k], $findings[2][$k] ];
                switch( $sign ) {
                    case '+':
                        if( !empty( $this->variables ) && isset( $this->variables[$str] ) ) {
                            $content = str_replace( $full_str, '', $content );
                        } else
                        return false;
                    break;

                    case '-':
                        if( !empty( $this->variables ) && !isset( $this->variables[$str] ) ) {
                            $content = str_replace( $full_str, '', $content );
                        } else
                        return false;
                    break;

                    case '?':
                        if( !empty( $this->variables ) && isset( $this->variables[$str] ) ) {
                            $content = str_replace( $full_str, esc_html( $this->variables[$str] ), $content );
                        } else {
                            $content = str_replace( $full_str, '', $content );
                        }
                    break;

                    default:
                    if( !empty( $this->variables ) && isset( $this->variables[$str] ) ) {
                        $content = str_replace( $full_str, esc_html( $this->variables[$str] ), $content );
                    } else
                    return false;
                }
            }
        }

        if( $this->points && isset( $attr['sign'] ) && isset( $attr['points'] ) ) {
            $points = (int) $attr['points'];
            $sign   = htmlspecialchars_decode( $attr['sign'] );
            switch( $sign ) {
                case '>':

                    if( $this->points <= $points )
                    return false;
                break;

                case '>=':
                    if( $this->points < $points )
                    return false;
                break;

                case '=':
                    if( $this->points != $points )
                    return false;
                break;

                case '<':
                    if( $this->points >= $points )
                    return false;
                break;

                case '<=':
                    if( $this->points > $points )
                    return false;
                break;
            }
        }

        return true;
    }

}
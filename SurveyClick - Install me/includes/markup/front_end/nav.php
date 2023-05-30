<?php

namespace markup\front_end;

class nav {

    protected $nav      = [];
    protected $classes  = [];
    protected $menu;
    protected $selected;

    private function recursion( array $list, string $classes ) {
        $markup = '';

        foreach( $list as $k => $v ) {
            if( !isset( $v['attrs'] ) )
            $v['attrs'] = [];

            if( isset( $v['childs'] ) ) {
                uasort( $v['childs'], function( $a, $b ) {
                    if( !isset( $a['position'] ) ) $a = [ 'position' => 99 ];
                    if( !isset( $b['position'] ) ) $b = [ 'position' => 99 ];
                    if( (double) $a['position'] === (double) $b['position'] ) return 0;
                    return ( (double) $a['position'] < (double) $b['position'] ? -1 : 1 );
                } );
            }

            if( $v['type'] == 'label' ) {
                $this->classes[] = 'nav_' . $k;
                $markup .= '
                <li class="label lab_' . $k . '">
                    <div class="labelt">
                    <span>' . ( !empty( $v['html_label'] ) ? $v['html_label'] : esc_html( $v['label'] ) ) . '</span>';
                    if( !empty( $v['min'] ) ) {
                        $markup .= '
                        <div class="lnk">
                            <a href="#"><i class="fas fa-expand-arrows-alt"></i></a>
                        </div>';
                    }
                    $markup .= '
                    </div>' . "\n";

                if( isset( $v['childs'] ) ) {
                    $markup .= '<ul>';
                    $markup .= $this->recursion( $v['childs'], $classes );
                    $markup .= '</ul>';
                }
            } else {
                if( isset( $v['childs'] ) ) {
                    $markup2 = '<i class="fas fa-chevron-down aic"></i></a>' . "\n";

                    $markup2 .= '<ul class="' . $classes . '">';
                    $markup2 .= $this->recursion( $v['childs'], $classes );
                    $markup2 .= '</ul>';

                    $v['hasDD'] = true;
                    $v['attrs'] += [ 'class' => 'dd' ];
                } else
                    $markup2 = "</a>\n";

                $attrs = $v['list_attr'] ?? [];

                if( !isset( $attrs['id'] ) )
                $attrs['id'] = '';

                if( !empty( $v['id'] ) )
                $attrs['id'] = ' ' . $v['id'];
                else
                $attrs['id'] = ' l-' . str_replace( ' ', '_', $k );

                if( isset( $v['hasDD'] ) ) {
                    if( !isset( $attrs['class'] ) )
                    $attrs['class'] = '';
                    $attrs['class'] = 'dd';   
                }

                if( isset( $v['class'] ) ) {
                    if( !isset( $attrs['class'] ) )
                    $attrs['class'] = '';
                    $attrs['class'] .= ' ' . $v['class'];
                }

                if( $v['selected'] ) {
                    if( !isset( $attrs['class'] ) )
                    $attrs['class'] = '';
                    $attrs['class'] .= ' active current';
                }

                if( !empty( $v['target'] ) )
                $v['attrs']['target'] = $v['target'];

                $markup .= '<li' . ( !empty( $attrs ) ? \util\attributes::add_attributes( array_map( 'trim', $attrs ) ) : '' ) . '><a href="' . esc_url( $v['url'] ) . '"' . ( isset( $v['attrs'] ) ? \util\attributes::add_attributes( $v['attrs'] ) : '' ) . '>' . ( isset( $v['icon'] ) ? ( filter_var( $v['icon'], FILTER_VALIDATE_URL ) ? '<img src="' . esc_url( $v['icon'] ) . '" alt="" />' : $v['icon'] ) : '' ) . '<span class="elp">' . ( !empty( $v['html_label'] ) ? $v['html_label'] : esc_html( $v['label'] ) ) . '</span>';
                if( isset( $v['after'] ) ) {
                    $markup .= $v['after'];
                }
                $markup .= $markup2;
            }

            $markup .= '</li>';
        }

        return $markup;
    }

    private function selectedChilds( array $childs ) {
        foreach( $childs as $child ) {
            if( $child['selected'] )
            return true;
            else if( isset( $child['childs'] ) )
            $this->selectedChilds( $child['childs'] );
        }

        return false;
    }

    protected function linksTree( array $links = NULL ) {
        $childs = [];
        $links  = $links ?? $this->nav;

        uasort( $links, function( $a, $b ) {
            if( !isset( $a['position'] ) ) $a = [ 'position' => 99 ];
            if( !isset( $b['position'] ) ) $b = [ 'position' => 99 ];
            if( (double) $a['position'] === (double) $b['position'] ) return 0;
            return ( (double) $a['position'] < (double) $b['position'] ? -1 : 1 );
        } );

        foreach( $links as $k => &$item ) {
            if( empty( $item['parent_id'] ) )
            $item['parent_id']  = false;
            $item['selected']   = ( $this->selected && ( ( isset( $item['id'] ) && isset( $this->selected[$item['id']] ) ) || isset( $this->selected[$k] ) ) ? true : false );
            $childs[$item['parent_id']][$k] = &$item;
        }
    
        unset( $item );
    
        foreach( $links as $k => &$item ) {
            if( isset( $childs[$k] ) ) {
                if( $this->selectedChilds( $childs[$k] ) )
                $item['selected']   = true;
                $item['childs']     = $childs[$k];
            }
        }

        return ( isset( $childs[0] ) ? $childs[0] : [] );
    }

    public function markup( string $classes = 'nav', string $sclasses = 'drop-down' ) {
        $this->classes  = [ $classes ];
        $this->selected = filters()->do_filter( 'admin-nav-active', NULL );
        $menu_markup    = $this->recursion( $this->linksTree(), $sclasses );

        $markup = "\n<ul class=\"" . implode( ' ', $this->classes ) . "\">\n";
        $markup .= $menu_markup;
        $markup .= '</ul>';
    
        return $markup;
    }
    
}
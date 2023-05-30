<?php

namespace admin\markup;

class stats_box {

    private $before = [];
    private $after  = [];
    private $rows   = [];

    public function add( string $id, string $title, string $value, string $icon = '', bool $waitForIt = false, bool $refresh = false, bool $staticValue = true, int $delay = 10000 ) {
        $markup = ' 
        <div>';
            if( $icon !== '' ) {
                $markup .= '<i class="' . esc_html( $icon ) . '"></i>';
            }
            $markup .= '
            <div>';
            $markup .= '<span class="elp">' . esc_html( $title ) . '</span>';
            
            if( $staticValue && !empty( $value ) ) {
                $markup .= '<span>' . esc_html( $value ) . '</span>';
            } else if( $waitForIt ) {
                $markup .= '<span data-req="' . esc_html( $id ) . '"' . ( $refresh ? ' data-ref="' . $delay . '"' : '' ) . '><i class="fas fa-sync fa-spin"></i></span>';
            } else {
                $markup .= '<span' . ( $refresh ? ' data-ref="' . $delay . '"' : '' ) . '>' . esc_html( $value ) . '</span>';
            }
            $markup .= '
            </div>
        </div>';

        if( empty( $id ) )
        $this->rows[] = $markup;
        else
        $this->rows[$id] = $markup;

        return $this;
    }

    public function title( string $title ) {
        $this->rows[] = '<h2>' . esc_html( $title ) . '</h2>';
        return $this;
    }

    public function description( string $description ) {
        $this->rows[] = '<h5 class="desc">' . esc_html( $description ) . '</h5>';
        return $this;
    }

    public function before( string $markup ) {
        $this->before[] = $markup;
        return $this;
    }

    public function after( string $markup ) {
        $this->after[] = $markup;
        return $this;
    }

    public function markup( string $class = '' ) {
        $markup = '<div class="boxes' . ( $class !== '' ? ' ' . $class : '' ) . '">';
        $markup .= implode( "\n", $this->before );
        foreach( $this->rows as $row ) {
            $markup .= $row;
        }
        $markup .= implode( "\n", $this->after );
        $markup .= '</div>';

        return $markup;
    }

}
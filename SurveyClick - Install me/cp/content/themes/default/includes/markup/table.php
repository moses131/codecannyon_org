<?php

namespace admin\markup;

class table {

    private $title;
    private $after_title;
    private $before = [];
    private $after  = [];
    private $count  = 0;
    private $head   = [];
    private $row    = [];
    private $rows   = [];
    private $pholder= false;

    function __construct( array $head = [] ) {
        $this->count    = count( $head );
        $this->head     = $head;
    }

    public function add( string $value, string $class = '' ) {
        $this->row[] = [ $value, $class ];
        return $this;
    }

    public function save( string $class = '' ) {
        $this->rows[]   = [ 'list' => $this->row, 'class' => $class ];
        $this->row      = [];
        return $this;
    }

    public function newLine( array $line, string $class = '' ) {
        $this->rows[]   = [ 'list' => $line, 'class' => $class ];
        return $this;
    }

    public function title( string $title ) {
        $this->title = $title;
        return $this;
    }

    public function afterTitle( string $after_title ) {
        $this->after_title = $after_title;
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

    public function placeholder( bool $ph ) {
        $this->pholder = $ph;
        return $this;
    }

    public function markup( string $class = '' ) {
        $markup = '<div class="table-container">
        <div class="table' . ( $class !== '' ? ' ' . $class : '' ) . '">
        <div class="th">';

        if( $this->title )
        $markup .= '<h2>' . $this->title . '</h2>';
        if( $this->after_title )
        $markup .= '<div>' . $this->after_title . '</div>';
        $markup .= '</div>';

        $markup .= implode( "\n", $this->before );
        $markup .= '<div class="tbody">';

        if( !empty( $this->head ) ) {
            $markup .= '<div class="tr">';
            array_walk( $this->head, function( $v, $k ) use ( &$markup ) {
                $markup .= '<div' . ( $v !== '' ? ' class="' . $v . '"' : '' ) . '>' . $k . '</div>';
            } );
            $markup .= '</div>';
        }

        if( $this->pholder ) {
            $markup .= '<div class="temp">';
            $markup .= filters()->do_filter( 'table_list_placeholder', '<img src="' . site_url( 'assets/icons/preloader.svg' ) . '" alt="preloader" />' );
            $markup .= '</div>';
        }

        foreach( $this->rows as $row ) {
            $markup .= '<div class="td' . ( isset( $row['class'] ) ? ' ' . $row['class'] : '' ) . '">';
            array_walk( $row['list'], function( $v, $k ) use ( &$markup ) {
                $markup .= '<div' . ( !empty( $v[1] ) ? ' class="' . $v[1] . '">' : '>' ) . $v[0] . '</div>';
            } );
            $markup .= '</div>';
        }

        $markup .= implode( "\n", $this->after );

        $markup .= '</div>
        </div>
        </div>';

        return $markup;
    }

}
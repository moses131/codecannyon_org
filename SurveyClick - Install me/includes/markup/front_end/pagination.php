<?php

namespace markup\front_end;

class pagination {

    private $total_pages;
    private $items_per_page;
    private $current_page;

    function __construct( int $total_pages, int $items_per_page, int $current_page = 1 ) {
        $this->total_pages      = $total_pages;
        $this->items_per_page   = $items_per_page;
        $this->current_page     = $current_page;
    }

    public function markup() {
        if( $this->total_pages < 2 ) return '';

        $markup = '
        <div class="pag mt40 mb40" data-tpages="' . $this->total_pages . '">
            <a href="?page=' . ( $this->current_page - 1 ) . '" class="btn' . ( $this->current_page <= 1 ? ' hidden' : '' ) . '" data-prev>
                <i class="fas fa-chevron-left"></i>
            </a>
            <a href="?page=' . ( $this->current_page + 1 ) . '" class="btn' . ( $this->current_page >= $this->total_pages ? ' hidden' : '' ) . '" data-next>
                <i class="fas fa-chevron-right"></i>
            </a>';
            if(  $this->total_pages > 2 ) {
                $markup .= '
                <div class="form">
                <span>' . t( 'Page:' ) . '</span>
                <form action="" method="GET">
                    <input type="number" name="page" min="1" max="' . $this->total_pages . '" value="' . $this->current_page . '"/>
                    <button class="btn" data-goto><i class="fas fa-angle-double-right"></i></button>
                </form>
                </div>';
            }
            $markup .= '
        </div>';

        return $markup;
    }
}
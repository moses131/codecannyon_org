<?php

namespace theme\helpers;

class pagination {

    private $classes;

    function __construct( string $classes = 'bg1' ) {
        $this->classes = $classes;
    }

    public function markup( $pagination, $links = 1 ) {
        if( !isset( $pagination['total_pages'] ) || (int) $pagination['total_pages'] <= 1 )
        return false;

        $markup     = '';

        $start      = ( ( $pagination['current_page'] - $links ) > 0 ) ? $pagination['current_page'] - $links : 1;
        $end        = ( ( $pagination['current_page'] + $links ) < $pagination['total_pages'] ) ? $pagination['current_page'] + $links : $pagination['total_pages'];

        $markup     .= '<div class="' . $this->classes . ' defp">
        <div class="main-wrapper tc">
        <ul class="pagination">';

        $markup     .= '<li' . ( ( $pagination['current_page'] == 1 ) ? ' class="selected"' : '' ) . '><a href="' . \_get_update( [ 'page' => ( $pagination['current_page'] == 1 ? 1 : ( $pagination['current_page'] - 1 ) ) ] ) . '"><i class="fas fa-arrow-left"></i><span>' . t( 'Prev', 'def-theme' ) . '</span></a></li>';

        if ( $start > 1 ) {
            $markup .= '<li><a href="' . \_get_update( [ 'page' => 1 ] ) . '">1</a></li>';
            if( ( $pagination['current_page'] - ($links+1 ) ) > 1 ) $markup   .= '<li>&hellip;</li>';
        }

        for ( $i = $start ; $i <= $end; $i++ ) {
            $markup .= '<li' . ( $pagination['current_page'] == $i ? ' class="selected"' : '' ) . '><a href="' . \_get_update( [ 'page' => $i ] ) . '">' . $i . '</a></li>';
        }

        if ( $end < $pagination['total_pages'] ) {
            if( ( $pagination['current_page'] + ($links+1) ) < $pagination['total_pages'] ) $markup .= '<li>&hellip;</li>';
            $markup .= '<li><a href="' . \_get_update( [ 'page' => $pagination['total_pages'] ] ) . '">' . $pagination['total_pages'] . '</a></li>';
        }

        $markup     .= '<li' . ( ( $pagination['current_page'] == $pagination['total_pages'] ) ? ' class="selected"' : '' ) . '><a href="' . \_get_update( [ 'page' => ( $pagination['current_page'] == $pagination['total_pages'] ? $pagination['total_pages'] : ( $pagination['current_page'] + 1 ) ) ] ) . '"><span>' . t( 'Next', 'def-theme' ) . '</span><i class="fas fa-arrow-right"></i></a></li>';

        $markup     .= '</ul>
        </div>
        </div>';

        return $markup;
    }

}
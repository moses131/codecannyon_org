<?php

/**
 * UTILS
 */

 // Message display builder
 function showMessage( string $title, string $text = '', string $icon = '', string $class = '' ) {
    $content    = '<div class="popup-alert' . ( $class !== '' ? ' ' . esc_html( $class ) : '' ) . '">';
    if( !empty( $icon ) )
    $content    .= '<div class="pa-icon">' . $icon . '</div>';
    $content    .= '<h2>' . $title . '</h2>';
    if( !empty( $text ) )
    $content    .= '<div class="pa-content">' . $text . '</div>';

    return $content;
}
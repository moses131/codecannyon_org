<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

echo '<div class="nav-left">';
echo '<div class="nav-top">
    ' . esc_html( get_option( 'website_name' ) ) . '
</div>';

echo '<div class="nav-container">';
$nav = new \admin\markup\nav;
echo $nav->markup();
echo '</div>';
echo '</div>';
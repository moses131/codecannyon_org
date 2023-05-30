<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="shop-categories" data-options=\'' . URLBP( [ 'search' => 'string', 'status' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
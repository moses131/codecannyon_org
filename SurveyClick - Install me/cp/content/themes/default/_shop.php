<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="shop" data-options=\'' . URLBP( [ 'search' => 'string', 'category' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
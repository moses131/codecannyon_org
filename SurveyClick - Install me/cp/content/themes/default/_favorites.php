<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="favorites" data-options=\'' . URLBP( [ 'search' => 'string', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
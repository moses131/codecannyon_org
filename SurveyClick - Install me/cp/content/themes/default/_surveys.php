<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="surveys" data-options=\'' . URLBP( [ 'search' => 'string', 'category' => 'int', 'status' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
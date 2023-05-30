<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="pages" data-options=\'' . URLBP( [ 'type' => 'dir', 'search' => 'string', 'category' => 'int', 'status' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
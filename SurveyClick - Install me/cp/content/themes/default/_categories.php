<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="categories" data-options=\'' . URLBP( [ 'type' => 'dir', 'search' => 'string', 'category' => 'int', 'lang' => 'string', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="menus" data-options=\'' . URLBP( [ 'menu' => 'string', 'lang' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
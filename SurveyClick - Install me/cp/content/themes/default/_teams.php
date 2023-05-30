<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="teams" data-options=\'' . URLBP( [ 'search' => 'string', 'view' => 'string', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
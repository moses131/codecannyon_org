<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="reportings" data-options=\'' . URLBP( [ 'dir' => 'dir', 'category' => 'int' ], item()->params )->getValuesJson() . '\'></div>';
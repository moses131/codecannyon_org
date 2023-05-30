<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="page" data-options=\'' . URLBP( [ 'id' => 'dir', 'type' => 'dir' ], item()->params )->getValuesJson() . '\'></div>';
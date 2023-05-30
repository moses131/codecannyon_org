<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="my-responses" data-options=\'' . URLBP( [ 'status' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
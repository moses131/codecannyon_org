<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="transactions" data-options=\'' . URLBP( [ 'status' => 'int', 'view' => 'list_int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="vouchers" data-options=\'' . URLBP( [ 'search' => 'string', 'exp' => 'int', 'status' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
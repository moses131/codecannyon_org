<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="actions" data-options=\'' . URLBP( [ 'by_user' => 'int', 'to_user' => 'int' ], item()->params )->getValuesJson() . '\'></div>';
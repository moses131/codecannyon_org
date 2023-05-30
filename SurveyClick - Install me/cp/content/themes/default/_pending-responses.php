<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="pending-responses" data-options=\'' . URLBP( [ 'survey' => 'int' ], item()->params )->getValuesJson() . '\'></div>';
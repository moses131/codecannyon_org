<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="subscriptions" data-options=\'' . URLBP( [ 'plan' => 'int', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
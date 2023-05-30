<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="kyc" data-options=\'' . URLBP( [ 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
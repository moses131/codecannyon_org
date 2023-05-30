<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="survey" data-options=\'' . URLBP( [ 'id' => 'dir', 'action' => 'dir', 'action2' => 'dir', 'status' => 'int', 'label' => 'int', 'report' => 'string', 'orderby' => 'string' ], item()->params )->getValuesJson() . '\'></div>';
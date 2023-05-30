<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

viewAsSwitch();

echo '<div data-load-to="index"></div>';
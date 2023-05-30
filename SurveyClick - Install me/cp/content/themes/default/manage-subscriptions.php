<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

echo '<div data-load-to="manage-subscriptions"></div>';
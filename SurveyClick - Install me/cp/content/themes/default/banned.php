<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

if( !me() || !me()->isBanned() ) {
    header( 'Location:' . esc_url( admin_url() ) );
    return ;
}

getHeader();

echo '<body>';

echo '<div class="fp df lrform">';

echo '<div class="box">';
echo '<div class="lks">
<h2>' . t( 'Temporary blocked') . '</h2>';
echo '

<div class="tc mt40">
    ' . sprintf( t( "Your access has been temporary blocked. You'll be able to access the service again on <strong>%s</strong>" ), custom_time( me()->getBannedUntil(), 2 ) ) . '
</div>

<div class="tc mt40">
    <a href="#" data-popup="logout">' . t( 'Logout' ) . '</a>
</div>

</div>';
echo '
</div>';

return ;
<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

if( me() ) {
    header( 'Location: ' . esc_url( admin_url() ) );
    die;
}

echo '<div class="fp df oa lrform">';

echo '<div class="box">';
echo '<div class="lks">
<h2>' . t( 'Reset your password' ) . '</h2>';
$forms = new \visitor\forms;
echo $forms->reset_password();
echo '

<div class="tc mt40">
    <a href="' . esc_url( admin_url( 'login' ) ) . '">' . t( 'Login' ) . '</a>
    &mdash;
    <a href="' . esc_url( admin_url( 'register' ) ) . '">' . t( 'Register' ) . '</a>
</div>

</div>
</div>';

echo '</div>';
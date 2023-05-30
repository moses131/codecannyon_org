<?php

// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Not logged
notLogged();

if( !me() || me()->loginConfirmed ) {
    header( 'Location:' . esc_url( admin_url() ) );
    return ;
}

if( isset( $_GET['code'] ) ) {
    try {
        me()->actions()->verifyCode( [ 'code' => $_GET['code'] ] );
        me()->actions()->confirmSession();
        header( 'Location:' . esc_url( admin_url() ) );
    }
    catch( \Exception $e ) { }
} else {
    me()->actions()->insertVerificationCode( 1 );
}

getHeader();

echo '<body>';

echo '<div class="fp df lrform">';

echo '<div class="box">';
echo '<div class="lks">
<h2>' . t( '2-Step Verification') . '</h2>';
$forms = new \visitor\forms;
echo $forms->twostepsauth();
echo '

<div class="tc mt40">
    ' . t( 'Check your inbox, enter the code we sent you in order to complete your authentification' ) . '
</div>

<div class="tc mt40">
    <a href="#" data-popup="logout">' . t( 'Logout' ) . '</a>
</div>

</div>';
echo '
</div>';

return ;
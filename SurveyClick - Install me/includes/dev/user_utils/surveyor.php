<?php

function teamActionUpdated() {
    if( !( $team = me()->myTeam() ) || ( me()->getId() === $team->getLastChatMessageUserId() || !( $my_la = me()->getTeamChatLastAction() ) || strtotime( $my_la ) > strtotime( $team->getLastChatMessage() ) ) ) {
        return false;
    }

    return true;
}

filters()->add_filter( 'in_admin_footer', function( $filter, $lines ) {
    if( me()->loginConfirmed && ( me()->hasEmailVerified() || !( (bool) get_option( 'femail_verify', false ) ) ) && !me()->isBanned() )
    $lines['surveyor_chat']  = '<script src="' . esc_url( site_url( [ SCRIPTS_DIR, 'surveyor_chat.js' ] ) ) . '"></script>';
    return $lines;
});

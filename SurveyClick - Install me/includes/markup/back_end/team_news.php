<?php

namespace markup\back_end;

class team_news {

    private $list;

    function __construct() {
        filters()->add_filter( 'team_news_builder_list', function( $f, $list ) {
            // Invitation sent
            $list['invs'] = [ $this, 'team_member_invited' ];
            // Invitation sent
            $list['inva'] = [ $this, 'invitation_approved' ];
            // Invitation canceled
            $list['invc'] = [ $this, 'invitation_canceled' ];
            // Member removed
            $list['udel'] = [ $this, 'member_removed' ];

            return $list;
        } );

        $this->list = filters()->do_filter( 'team_news_builder_list', [] );
    }

    private function team_member_invited( $data ) {
        $by = users( (int) $data['invited'] );
        if( !$by->getObject() ) $byName = '-';
        else $byName = esc_html( $by->getDisplayName() );
        $to = users( (int) $data['user'] );
        if( !$to->getObject() ) $toName = '-';
        else $toName = esc_html( $to->getDisplayName() );

        return sprintf( t( '<strong>%s</strong> was invited by <strong>%s</strong> to join the team' ), $toName, $byName );
    }

    private function invitation_approved( $data ) {
        $user = users( (int) $data['user'] );
        if( !$user->getObject() ) $userName = '-';
        else $userName = esc_html( $user->getDisplayName() );

        return sprintf( t( '<strong>%s</strong> joined the team' ), $userName );
    }

    private function invitation_canceled( $data ) {
        $user = users( (int) $data['user'] );
        if( !$user->getObject() ) $userName = '-';
        else $userName = esc_html( $user->getDisplayName() );

        return sprintf( t( "<strong>%s</strong>'s invitation has been canceled" ), $userName );
    }

    private function member_removed( $data ) {
        $user = users( (int) $data['user'] );
        if( !$user->getObject() ) $userName = '-';
        else $userName = esc_html( $user->getDisplayName() );

        return sprintf( t( "<strong>%s</strong> left the team" ), $userName );
    }

    public function readAlert( string $alert ) {
        $alert = json_decode( $alert, true );
        if( !$alert || !isset( $alert['type'] ) || !isset( $this->list[$alert['type']] ) || !is_callable( $this->list[$alert['type']] ) ) return '';
        return call_user_func( $this->list[$alert['type']], $alert );
    }

}
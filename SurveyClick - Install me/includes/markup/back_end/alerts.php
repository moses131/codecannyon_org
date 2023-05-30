<?php

namespace markup\back_end;

class alerts {

    private $list;

    function __construct() {
        filters()->add_filter( 'alerts_builder_list', function( $f, $list ) {
            // Invitation received
            $list['tinv'] = [ $this, 'invited_team' ];
            // Withdrawal request
            $list['swit'] = [ $this, 'withdrawal_request' ];
            // Withdrawal approved
            $list['awit'] = [ $this, 'withdrawal_approved' ];
            // Shared report
            $list['srep'] = [ $this, 'shared_report' ];
            // Message
            $list['msg'] = [ $this, 'message' ];

            return $list;
        } );

        $this->list = filters()->do_filter( 'alerts_builder_list', [] );
    }

    private function invited_team( $data ) {
        $by = users( (int) $data['invited'] );
        if( !$by->getObject() ) $byName = '-';
        else $byName = esc_html( $by->getDisplayName() );

        $teams = new \query\team\teams( $data['team'] );
        if( !$teams->getObject() ) $teamName = '-';
        else $teamName = esc_html( $teams->getName() );

        $markup = '<div>' . sprintf( t( '<strong>%s</strong> invited you to join <strong>%s</strong>' ), $byName, $teamName ) . '</div>';

        if( ( $membership = $teams->userIsMember( me()->getId() ) ) ) {
            if( $membership->approved == 0 )
                $markup .= '
                <div class="df lnks">
                    <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'approve-invitation', 'team' => (int) $data['team'], 'location' => 'alerts' ] ) ) . '\'>' . t( 'Join team' ) . '</a>
                    <span class="or">' . t( 'OR' ) . '</span>
                    <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'reject-invitation', 'team' => (int) $data['team'], 'location' => 'alerts' ] ) ) . '\'>' . t( 'Reject invitation' ) . '</a>
                </div>';
                else
                $markup .= '<div>' . t( 'Invitation approved' ) . '</div>';
        } else {
            $markup .= '<div>' . t( 'Invitation canceled' ) . '</div>';
        }

        return $markup;
    }

    private function withdrawal_request( $data ) {
        return sprintf( t( 'Your withdrawal <strong>(%s)</strong> has been received and is awaiting approval' ), cms_money_format( $data['amount'] ) );
    }

    private function withdrawal_approved( $data ) {
        return sprintf( t( 'Your withdrawal <strong>(%s)</strong> has been approved' ), cms_money_format( $data['amount'] ) );
    }

    private function shared_report( $data ) {
        $by = users( (int) $data['user'] );
        if( !$by->getObject() ) $byName = '-';
        else $byName = esc_html( $by->getDisplayName() );

        $reports = new \query\survey\saved_reports( $data['report'] );
        $markup  = '<div>' . sprintf( t( '<strong>%s</strong> shared a report' ), $byName ) . '</div>';

        if( $reports->getObject() ) {
            $markup .= '<a href="#" class="btn" data-popup="user-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'view-report', 'report' => $reports->getId() ] ) ) . '\'>' . t( 'View report' ) . '</a>';
        } else {
            $markup .= '<div>' . t( 'The report has been deleted' ) . '</div>';
        }

        return $markup;
    }

    private function message( $data ) {
        $shortcodes = new \site\shortcodes;
        $shortcodes ->setInlineContent( esc_html( $data['txt'] ) );
        return ( isset( $data['title'] ) ? '<h3>' . esc_html( $data['title'] ) . '</h3>' : '' ) . $shortcodes->inlineMarkup();
    }

    public function readAlert( string $alert ) {
        $alert = json_decode( $alert, true );
        if( !$alert || !isset( $alert['type'] ) || !isset( $this->list[$alert['type']] ) || !is_callable( $this->list[$alert['type']] ) ) return '';
        return call_user_func( $this->list[$alert['type']], $alert );
    }

}
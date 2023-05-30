<?php

namespace markup\back_end;

class admin_actions {

    private $list;

    function __construct() {
        filters()->add_filter( 'admin_actions_builder_list', function( $f, $list ) {
            // User added
            $list['adduser'] = [ $this, 'adduser' ];
            // User edited
            $list['edituser'] = [ $this, 'edituser' ];
            // User banned
            $list['ban'] = [ $this, 'ban' ];
            // User's password changed
            $list['passchanged'] = [ $this, 'passchanged' ];
            // Updated user's balance
            $list['ubalance'] = [ $this, 'ubalance' ];
            // Team edited
            $list['editteam'] = [ $this, 'editteam' ];
            // Edit survey status
            $list['editsurveystatus'] = [ $this, 'editsurveystatus' ];
            // Shop item added
            $list['addshopitem'] = [ $this, 'addshopitem' ];
            // Shop item edited
            $list['editshopitem'] = [ $this, 'editshopitem' ];
            // Edit shop item
            $list['editorderstatus'] = [ $this, 'editorderstatus' ];

            return $list;
        } );

        $this->list = filters()->do_filter( 'admin_actions_builder_list', [] );
    }

    private function adduser( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = users( $to_user );
        return sprintf( t( '<strong>%s</strong> added a new user (<strong>%s</strong> ID: %s)' ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getDisplayName() ) : '-' ), $to->getId() );
    }

    private function edituser( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = users( $to_user );
        return sprintf( t( '<strong>%s</strong> edited user (<strong>%s</strong> ID: %s)' ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getDisplayName() ) : '-' ), $to->getId() );
    }

    private function ban( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = users( $to_user );
        return sprintf( t( '<strong>%s</strong> banned user (<strong>%s</strong> ID: %s). Expiration: <strong>%s</strong>' ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getDisplayName() ) : '-' ), $to->getId(), custom_time( $data['expiration'], 2 ) );
    }

    private function passchanged( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = users( $to_user );
        return sprintf( t( "<strong>%s</strong> changed <strong>%s's</strong> password (ID: %s)" ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getDisplayName() ) : '-' ), $to->getId() );
    }

    private function ubalance( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = users( $to_user );
        return sprintf( t( "<strong>%s</strong> updated <strong>%s's</strong> balance (ID: %s). Old balance: %s, new balance: %s, old bonus balance: %s, new bonus balance: %s" ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getDisplayName() ) : '-' ), $to->getId(), cms_money_format( (double) $data['balance_old'] ), cms_money_format( (double) $data['balance_new'] ), cms_money_format( (double) $data['bonus_old'] ), cms_money_format( (double) $data['bonus_new'] ) );
    }

    private function editteam( $data, int $by_user, int $to_user ) {
        $by = users( $by_user );
        $to = teams( $to_user );
        return sprintf( t( '<strong>%s</strong> edited team (<strong>%s</strong> ID: %s)' ), ( $by->getObject() ? esc_html( $by->getDisplayName() ) : '' ), ( $to->getObject() ? esc_html( $to->getName() ) : '-' ), $to->getId() );
    }

    private function editsurveystatus( $data, int $by_user, int $to_user ) {
        $by         = users( $by_user );
        $survey     = surveys( (int) ( $data['id'] ?? 0  ) );
        $statuses   = [ 5 => t( 'Finished' ), 4 => t( 'Live' ), 3 => t( 'Paused' ), 2 => t( 'Waiting approval' ), 1 => t( 'Require setup' ), 0 => t( 'Rejected' ), -1 => t( 'Pending deletion' ) ];

        return sprintf( t( '<strong>%s</strong> edited survey (<strong>%s</strong> ID: %s), changed status from <strong>%s</strong> to <strong>%s</strong>' ), ( $by->getObject() ? esc_html( $by->getName() ) : '-' ), ( $survey->getObject() ? esc_html( $survey->getName() ) : '' ), $survey->getId(), $statuses[$data['old']], $statuses[$data['new']] );
    }

    private function addshopitem( $data, int $by_user, int $item ) {
        $by         = users( $by_user );

        return sprintf( t( '<strong>%s</strong> added a new shop item (ID: %s)' ), ( $by->getObject() ? esc_html( $by->getName() ) : '-' ), $item );
    }

    private function editshopitem( $data, int $by_user, int $item ) {
        $by         = users( $by_user );

        return sprintf( t( '<strong>%s</strong> edited shop item (ID: %s)' ), ( $by->getObject() ? esc_html( $by->getName() ) : '-' ), $item );
    }

    private function editorderstatus( $data, int $by_user, int $order ) {
        $by         = users( $by_user );
        $statuses   = [ 2 => t( 'Approved' ), 1 => t( 'Pending' ), 0 => t( 'Canceled' ) ];

        return sprintf( t( '<strong>%s</strong> edited shop order (ID: %s), changed status from <strong>%s</strong> to <strong>%s</strong>' ), ( $by->getObject() ? esc_html( $by->getName() ) : '-' ), $order, $statuses[$data['old']], $statuses[$data['new']] );
    }

    public function read( string $alert, int $by_user, int $to_user ) {
        $alert = json_decode( $alert, true );
        if( !$alert || !isset( $alert['type'] ) || !isset( $this->list[$alert['type']] ) || !is_callable( $this->list[$alert['type']] ) ) return '';
        return call_user_func( $this->list[$alert['type']], $alert, $by_user, $to_user );
    }

}
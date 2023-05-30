<?php

namespace markup\back_end;

class latest_results {

    private $result;
    private $countries;

    function __construct( array $result = [] ) {
        $this->setResult( $result );
    }

    public function setResult( array $result ) {
        if( !empty( $result ) ) {
            $this->result = $result;
        }
        return $this;
    }

    public function getMarkup( array $result ) {
        if( !empty( $result ) ) 
        $this->setResult( $result );

        if( empty( $this->result['id'] ) ) return ;

        $custom_markup = filters()->do_filter( 'custom_markup_latest_results', NULL, $this->result );
        if( $custom_markup ) return $custom_markup;

        // Answer disqualified
        if( $this->result['r_status'] == 0 ) {

            if( $this->result['r_country'] ) {
                $country    = $this->getCountry( $this->result['r_country'] );
                return '<div>' . sprintf( t( '%s from <strong>%s</strong> for <strong>%s</strong> (%s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'Rejected response (view)' ) . '</a>', ( $country ? t( esc_html( $country->name ) ) : '-' ), esc_html( $this->result['name'] ), ( $this->result['r_finish'] ? t( 'rejected' ) : t( 'abandoned' ) ) ) . '</div>';
            } else {
                return '<div>' . sprintf( t( '%s for <strong>%s</strong> (%s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'Rejected response (view)' ) . '</a>', esc_html( $this->result['name'] ), ( $this->result['r_finish'] ? t( 'rejected' ) : t( 'abandoned' ) ) ) . '</div>';
            }

        // Answer is on going
        } else if( $this->result['r_status'] == 1 ) {

            $spent      = time() - strtotime( $this->result['r_date'] );
            $duration   = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );

            if( $this->result['r_country'] ) {
                $country    = $this->getCountry( $this->result['r_country'] );
                return '<div>' . sprintf( t( 'New response from <strong>%s</strong> for <strong>%s</strong> is on the way. (survey duration: %s)' ), ( $country ? t( esc_html( $country->name ) ) : '-' ), esc_html( $this->result['name'] ), $duration ) . '</div>';
            } else {
                return '<div>' . sprintf( t( 'New response for <strong>%s</strong> is on the way. (survey duration: %s)' ), esc_html( $this->result['name'] ), $duration ) . '</div>';
            }

        // The answer has been finished but not approved yet
        } else if( $this->result['r_status'] == 2 ) {

            $duration   = $this->result['r_finish'] > 59 ? sprintf( t( '%s m' ), ceil( $this->result['r_finish'] / 60 ) ) : sprintf( t( '%s s' ), $this->result['r_finish'] );
            
            if( $this->result['r_country'] ) {
                $country    = $this->getCountry( $this->result['r_country'] );
                $markup     = '<div>' . sprintf( t( '%s from <strong>%s</strong> for <strong>%s</strong>. (survey duration: %s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'New response (view)' ) . '</a>', ( $country ? t( esc_html( $country->name ) ) : '-' ), esc_html( $this->result['name'] ), $duration) . '</div>';
            } else {
                $markup     = '<div>' . sprintf( t( '%s for <strong>%s</strong>. (survey duration: %s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'New response (view)' ) . '</a>', esc_html( $this->result['name'] ), $duration) . '</div>';
            }

            if( $this->result['team'] ) {

                $teams = me()->myTeam( $this->result['team'] );
                if( !$teams ) return $markup;

                $can = [];

                if( me()->manageTeam( 'approve-response' ) ) {
                    $can[] = '<a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'approve-response', 'response' => $this->result['r_id'] ] ) ) . '\'>' . t( 'Approve' ) . '</a>';
                }

                if( me()->manageTeam( 'reject-response' ) ) {
                    $can[] = '<a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'reject-response', 'response' => $this->result['r_id'] ] ) ) . '\'>' . t( 'Reject' ) . '</a>';    
                }

                if( empty( $can ) ) {
                    $markup .= '<div>' . t( 'Pending verification' ) . '</div>';
                } else {
                    $markup .= '
                    <div class="df lnks">';
                    $markup .= implode( '<span class="or">' . t( 'OR' ) . '</span>', $can );
                    $markup .= '
                    </div>';
                }

            } else {

                $markup .= '
                <div class="df lnks">
                    <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'approve-response', 'response' => $this->result['r_id'] ] ) ) . '\'>' . t( 'Approve' ) . '</a>
                    <span class="or">' . t( 'OR' ) . '</span>
                    <a href="#" class="btn" data-ajax="user-options3" data-data=\'' . ( cms_json_encode( [ 'action' => 'reject-response', 'response' => $this->result['r_id'] ] ) ) . '\'>' . t( 'Reject' ) . '</a>
                </div>';

            }

            return $markup;

        // All done
        } else if( $this->result['r_status'] == 3 ) {

            $duration = $this->result['r_finish'] > 59 ? sprintf( t( '%s m' ), ceil( $this->result['r_finish'] / 60 ) ) : sprintf( t( '%s s' ), $this->result['r_finish'] );

            if( $this->result['r_country'] ) {
                $country    = $this->getCountry( $this->result['r_country'] );
                return '<div>' . sprintf( t( '%s from <strong>%s</strong> for <strong>%s</strong>. (survey duration: %s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'New response (view)' ) . '</a>', ( $country ? t( esc_html( $country->name ) ) : '-' ), esc_html( $this->result['name'] ), $duration ) . '</div>';
            } else {
                return '<div>' . sprintf( t( '%s for <strong>%s</strong>. (survey duration: %s)' ), '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $this->result['r_id'] ] ) . '\'>' . t( 'New response (view)' ) . '</a>', esc_html( $this->result['name'] ), $duration ) . '</div>';
            }

        }

    }

    private function getCountry( string $country ) {
        $country = strtolower( $country );

        if( !$this->countries ) {
            $countries = new \query\countries;
            $this->countries = $countries->fetch( -1 );
        }

        if( isset( $this->countries[$country] ) )
        return $this->countries[$country];

        return ;
    }

}
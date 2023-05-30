<?php

namespace markup\back_end;

class latest_results_respondent {

    private $result;

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
        if( $this->result['status'] == 0 ) {

            return '<div>' . sprintf( t( 'Response to <strong>%s</strong> was rejected' ), esc_html( $this->result['s_name'] ) ) . '</div>';

        // Answer is on going
        } else if( $this->result['status'] == 1 ) {

            $finish     = time() - strtotime( $this->result['date'] );
            $duration   = $finish > 59 ? sprintf( t( '%s m' ), ceil( $finish / 60 ) ) : sprintf( t( '%s s' ), $finish );
            $comm       = [];

            if( ( $commission = ( $this->result['commission'] + $this->result['commission_bonus'] ) ) )
            $comm[]     = cms_money_format( ( $commission - $this->result['commission_p'] ) );

            if( $this->result['lpoints'] )
            $comm[]     = '<i class="fas fa-star cl3"></i> <strong>' . $this->result['lpoints'] . '</strong>';

            return '<div>' . sprintf( t( 'Response <strong>%s</strong> not finished. (duration: %s)%s' ), esc_html( $this->result['s_name'] ), $duration, ( !empty( $comm ) ? ' - <strong>' . implode( ', ', $comm ) . '</strong>' : '' ) ) . '</div>';

        // The answer has been finished but not approved yet
        } else if( $this->result['status'] == 2 ) {

            $finish     = strtotime( $this->result['fin'] ) - strtotime( $this->result['date'] );
            $duration   = $finish > 59 ? sprintf( t( '%s m' ), ceil( $finish / 60 ) ) : sprintf( t( '%s s' ), $finish );
            $comm       = [];

            if( ( $commission = ( $this->result['commission'] + $this->result['commission_bonus'] ) ) )
            $comm[]     = cms_money_format( ( $commission - $this->result['commission_p'] ) );

            if( $this->result['lpoints'] )
            $comm[]     = '<i class="fas fa-star cl3"></i> <strong>' . $this->result['lpoints'] . '</strong>';

            return '<div>' . sprintf( t( 'Responsed <strong>%s</strong>. (survey duration: %s)%s' ), esc_html( $this->result['s_name'] ), $duration, ( !empty( $comm ) ? ' - <strong>' . implode( ', ', $comm ) . '</strong>' : '' ) ) . '</div>
            <div>' . t( 'This response is awaiting approval' ) . '</div>';

        // All done
        } else if( $this->result['status'] == 3 ) {
            $finish     = strtotime( $this->result['fin'] ) - strtotime( $this->result['date'] );
            $duration   = $finish > 59 ? sprintf( t( '%s m' ), ceil( $finish / 60 ) ) : sprintf( t( '%s s' ), $finish );
            $comm       = [];

            if( ( $commission = ( $this->result['commission'] + $this->result['commission_bonus'] ) ) )
            $comm[]     = cms_money_format( ( $commission - $this->result['commission_p'] ) );

            if( $this->result['lpoints'] )
            $comm[]     = '<i class="fas fa-star cl3"></i> <strong>' . $this->result['lpoints'] . '</strong>';

            return '<div>' . sprintf( t( 'Responsed <strong>%s</strong>. (survey duration: %s)%s' ), esc_html( $this->result['s_name'] ), $duration, ( !empty( $comm ) ? ' - <strong>' . implode( ', ', $comm ) . '</strong>' : '' ) ) . '</div>';
        }

    }

}
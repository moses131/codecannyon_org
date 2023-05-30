<?php

namespace admin\markup;

class pricing_table {

    private $title;
    private $rows;

    function __construct( string $title = NULL ) {
        $this->title        = $title;
        $this->rows         = [];
        $this->rows['def']  = [
            'surveys'   => [
                'title'     => t( 'Surveys' ),
                'fields'    => [
                    'surveys'   => [ t( 'Surveys' ) ],
                    'questions' => [ t( 'Questions per survey' ) ],
                    'collectors'=> [ t( 'Collectors' ) ],
                    'space'     => [ t( 'Available space' ) ],
                    'reports'   => [ t( 'Reports' ), t( 'Unlimited' ) ],
                    'exports'   => [ t( 'Exports (CSV, print)' ), '<i class="fas fa-check"></i>' ],
                    'custom'    => [ t( 'Custom logo'), '<i class="fas fa-check"></i>' ],
                    'brand'     => [ t( 'Remove our brand' ), t( 'No' ) ]
                ]
            ],
            'responses' => [
                'title'     => t( 'Responses' ),
                'fields'    => [
                    'responses' => [ t( 'Responses per survey' ) ],
                    'filters'   => [ t( 'Unlimited filters' ), '<i class="fas fa-check"></i>' ],
                    'upload'    => [ t( 'File upload' ), '<i class="fas fa-check"></i>' ],
                    'exports'   => [ t( 'Exports (CSV, print)' ), '<i class="fas fa-check"></i>' ],
                    'advanced'  => [ t( 'Advanced fields' ), '<i class="fas fa-check"></i>' ]
                ]
            ],
            'teams'     => [
                'title'     => t( 'Teams' ),
                'fields'    => [
                    'members'   => [ t( 'Members' ) ],
                    'share'     => [ t( 'Share reports' ), '<i class="fas fa-check"></i>' ],
                    'chat'      => [ t( 'Team chat' ), '<i class="fas fa-check"></i>' ]
                ]
            ]
        ];
    }

    public function addFields( string $place, string $key, string $title, string $defValue = '' ) {
        if( isset( $this->rows['def'][$place]['fields'] ) )
        $this->rows['def'][$place]['fields'][$key] = $title;

        return $this;
    }

    public function addLabel( string $place, string $title ) {
        if( isset( $this->rows['def'][$place] ) )
        $this->rows['def'][$place]['title'] = $title;

        return $this;
    }

    public function markup( string $class = '' ) {
        $markup = '<div class="table pr-table' . ( $class !== '' ? ' ' . $class : '' ) . '">';
        $links  = '';

        if( !empty( $this->title ) )
        $markup .= '<h2>' . $this->title . '</h2>';
        $markup .= '<div class="tbody">';

        $links  .= '
        <div class="pbtm">
            <h2 class="price"><span class="aprice">' . t( 'Free' ) . '</span></h2>
        </div>';

        $markup .= '<div class="tr tdt">';
        $markup .= '<div><div></div></div>';
        $markup .= '<div><span>' . t( 'Free' ) . '</span></div>';

        $plans  = pricingPlans()
                ->setVisible()
                ->orderBy( 'price' );
        $fPlans = $plans->fetch( -1 );

        foreach( $fPlans as $plan ) {
            $plans  ->setObject( $plan );
            $offers = $plans->activeOffers();
            $offers ->orderBy( 'price' );
            $fOffers= $offers->fetch( -1 ); 
            $bOffer = current( $fOffers );

            $markup .= '<div><span>' . esc_html( $plans->getName() ) .  '</span>';
            if( !empty( $bOffer ) ) {
                $offers ->setObject( $bOffer );
                $markup .= '<div class="poff"><span class="ctab">' . sprintf( t( '%s OFF' ), round( ( 1 - $offers->getPrice() / $plans->getPrice() ) * 100 ) . '%' ) . '</span></div>';
            }
            $markup .= '</div>';

            $links  .= '
            <div class="pbtm">
                <h2 class="price">' . ( $bOffer ? '<span class="old">' . $plans->getPriceF() . '</span><span class="aprice">' . $offers->getPriceF() . '</span>' : '<span class="aprice">' . $plans->getPriceF() . '</span>' ) . '</h2>
                <div class="pinfo">' . t( '*per month' ) . '</div>
                <span>
                    <a href="#" data-popup="' . ajax()->get_call_url( 'upgrade', [ 'planId' => $plans->getId() ] ) . '" class="btn goldbtn">' . t( 'Subscribe' ) . '</a>
                </span>
            </div>';
        }
        
        $markup .= '</div>';

        foreach( $this->rows['def'] as $row ) {
            $markup .= '<span>' . $row['title'] . '</span>';
            foreach( $row['fields'] as $fkey => $field ) {
                $default    = $field[1] ?? '-';
                $markup     .= '<div class="td">';
                $markup     .= '<div>' . current( $field ) . '</div>';
                $markup     .= '<div><span>' . $plans->getValueFreePlan( $fkey, $default ) . '</span></div>';
                foreach( $fPlans as $plan ) {
                    $plans  ->setObject( $plan );
                    $markup .= '<div><span>' . $plans->getValue( $fkey, $default ) . '</span></div>';
                }
                $markup .= '</div>';
            }
        }

        $markup .= '<div class="tdt">';
        $markup .= '<div></div>';
        $markup .= $links;
        $markup .= '</div>';

        $markup .= '</div>
        </div>';

        return $markup;
    }

}
<?php

namespace theme\helpers;

class pricing_table {

    private $plans;
    private $individual;
    private $team;

    function __construct() {
        /** ALL PLANS */

        // Free plan
        $free = getFreeSubscription();
        $planInfo = [
            'isFree'        => true,
            'name'          => t( 'Free', 'def-theme' ),
            'surveys'       => $free->surveys(),
            'responses'     => $free->responses(),
            'questions'     => $free->questions(),
            'collectors'    => $free->collectors(),
            'team_members'  => $free->teamMembers(),
            'remove_brand'  => $free->removeBrand(),
            'space'         => \util\etc::mibToStr( $free->space() ),
            'price'         => 0,
            'priceF'        => cms_money_format( 0 )
        ];

        $this->plans[]      = $planInfo;
        $this->individual[] = $planInfo;

        // Paid plans
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

            $planInfo   = [
                'isFree'        => false,
                'name'          => $plans->getName(),
                'surveys'       => $plans->getSurveys(),
                'responses'     => $plans->getResponses(),
                'questions'     => $plans->getQuestions(),
                'collectors'    => $plans->getCollectors(),
                'team_members'  => $plans->getTeam(),
                'remove_brand'  => $plans->getRemoveBrand(),
                'space'         => \util\etc::mibToStr( $plans->getAvailableSpace() ),
                'price'         => $plans->getPrice(),
                'priceF'        => $plans->getPriceF()
            ];

            if( !empty( $bOffer ) ) {
                $offers->setObject( $bOffer );
                $planInfo['offer'] = [
                    'price'     => $offers->getPrice(),
                    'priceF'    => $offers->getPriceF(),
                    'months'    => $offers->getMinMonths(),
                    'expires'   => $offers->getEndDate(),
                    'discount'  => round( ( 1 - $offers->getPrice() / $plans->getPrice() ) * 100 )
                ];
            }

            // Add to all plans
            $this->plans[] = $planInfo;

            if( $plans->getTeam() ) {
                // Add to team plans
                $this->team[] = $planInfo;
            } else {
                // Add to individual plans
                $this->individual[] = $planInfo;
            }
        }
    }

    public function getPlans() {
        return $this->plans;
    }

    public function getIndividualPlans() {
        return $this->individual;
    }

    public function getTeamPlans() {
        return $this->team;
    }
    
}
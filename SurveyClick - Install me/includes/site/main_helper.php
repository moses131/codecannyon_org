<?php

namespace site;

class main_helper {

    function __construct() {
        // Save the referral Id
       if( isset( $_GET['ref'] ) && empty( $_COOKIE['ref'] ) )
       setcookie( 'ref',  (int) $_GET['ref'],   strtotime( '+2 months' ) );
    }

    public function getDefaultLanguages() {
        return [
            'en_US' => [ 
                'short'     => 'EN',
                'name'      => 'English',
                'name_en'   => 'English', 
                'locale_e'  => 'en_US',
                'locale'    => 'en_US.utf8',
                'direction' => 'ltr'
            ],
            'ro_RO' => [
                'short'     => 'RO',
                'name'      => 'Română',
                'name_en'   => 'Romanian', 
                'locale_e'  => 'ro_RO',
                'locale'    => 'ro_RO.utf8',
                'direction' => 'ltr'
            ]
        ];
    }

    protected function autodetectCountry( &$site ) {
        $country = \util\etc::userCountry();
        $canSave = false;

        if( $country ) {
            $country = strtolower( $country );
            if( isset( $site->countries[$country] ) ) {
                $site->user_country->current    = $country;
                $site->user_language->current   = $site->countries[$country]->language;

                setcookie( 'site_country',  $site->user_country->current,   strtotime( '+1 year' ) );
                setcookie( 'site_lang',     $site->user_language->current,  strtotime( '+1 year' ) );

                $canSave = true;
            }
        }

        if( !$canSave ) {
            $site->user_country->current    = get_option( 'def_country' );
            $site->user_language->current   = get_option( 'def_language' );

            setcookie( 'site_country',  $site->user_country->current,   strtotime( '+1 year' ) );
            setcookie( 'site_lang',     $site->user_language->current,  strtotime( '+1 year' ) );
        }
    }

}
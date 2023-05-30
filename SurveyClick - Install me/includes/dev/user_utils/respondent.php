<?php

//

$respondent_vars = [
    'pending_responses' => me()
                        ->getResults()
                        ->setStatus( 1 )
                        ->count()
];

filters()->add_filter( 'respondent_vars', $respondent_vars );

// Shop
function my_shop() {
    $categories = new \query\shop\categories;
    $categories ->setCountry( me()->getCountryId() );

    return (object) [
        'categories'    => $categories
    ];
}
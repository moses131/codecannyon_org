<?php

// Load theme options
$theme->buildOptions();

$orders         = new \query\shop\orders;
$kyc_intents    = new \query\user_intents;
$prequests      = new \query\transactions;

$owner_vars     = [
    'pending_surveys'   => surveys()
                        ->setStatus( 2 )
                        ->count(),
    'pending_orders'    => $orders
                        ->setStatus( 1 )
                        ->count(),
    'kyc_intents'       => $kyc_intents
                        ->setTypeId( 1 )
                        ->count(),
    'payout_requests'   => $prequests->setTypeId( 4 )
                        ->setStatus( 1 )
                        ->count()
];

filters()->add_filter( 'owner_vars', $owner_vars );
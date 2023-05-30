<?php

$kyc_intents    = new \query\user_intents;

$admin_vars     = [
    'pending_surveys'   => surveys()
                        ->setStatus( 2 )
                        ->count(),
    'kyc_intents'       => $kyc_intents
                        ->setTypeId( 1 )
                        ->count()
];

filters()->add_filter( 'admin_vars', $admin_vars );
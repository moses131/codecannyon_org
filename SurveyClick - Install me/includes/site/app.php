<?php

namespace site;

class app {

    function __construct() {
        $this->defaultFilters();
        $this->surveyThemes();
    }

    public function filters2() {
        $this->executePayment();
    }

    private function defaultFilters() {
        // Payment methods
        filters()->add_filter( 'deposit-methods', function( $filter ) {
            return [
                'paypal' => [
                    'name'  => 'PayPal',
                    'class' => [ '\site\payments', 'paypal' ]
                ],
                'stripe' => [
                    'name'  => 'Stripe',
                    'class' => [ '\site\payments', 'stripe' ]
                ],
            ]; 
        } );

        // Category permalink
        filters()->add_filter( 'category-permalink', function( $filter, $def, $slug, $id ) {
            return esc_url( site_url( [ filters()->do_filter( 'category-path', 'category' ), ( $slug != '' ? $slug : $id ) ] ) );
        } );

        // Survey permalink
        filters()->add_filter( 'survey-permalink', function( $filter, $def, $name, $id ) {
            return esc_url( site_url( [ filters()->do_filter( 'survey-path', 'survey' ), $id ] ) );
        } );

        // Respond permalink
        filters()->add_filter( 'respond-permalink', function( $filter, $def, $slug, $id ) {
            return esc_url( site_url( [ filters()->do_filter( 'respond-path', 'r' ), $slug ] ) );
        } );

        // Page permalink
        filters()->add_filter( 'page-permalink', function( $filter, $def, $slug, $id ) {
            return esc_url( site_url( [ filters()->do_filter( 'page-path', '' ), ( $slug != '' ? $slug : $id )  ] ) );
        } );

        // Question types
        filters()->add_filter( 'question-types', function( $filter, $selected ) {
            $types  = new \survey\question_types;
            $types  ->setType( $selected );
            return $types;
        } );

        // Media categories
        filters()->add_filter( 'media-categories', function() {
            return [
                'question-opt'  => [ 'id' => 1,     'name'  => t( 'Questions' ) ],
                'question-opts' => [ 'id' => 2,     'name'  => t( 'Question option' ) ],
                'q-attachment'  => [ 'id' => 3,     'name'  => t( 'Question attachment' ) ],
                'user-avatar'   => [ 'id' => 5,     'name'  => t( 'User avatar' ) ],
                'block'         => [ 'id' => 6,     'name'  => t( 'Block' ) ],
                'post-thumb'    => [ 'id' => 7,     'name'  => t( 'Page thumbnails' ) ],
                'kyc'           => [ 'id' => 8,     'name'  => t( 'KYC' ) ],
                'shop_item'     => [ 'id' => 10,    'name'  => t( 'Shop item' ) ],
                'theme-options' => [ 'id' => 11,    'name'  => t( 'Theme options' ) ],
                'survey-avatar' => [ 'id' => 12,    'name'  => t( 'Survey avatar' ) ],
                'survey-logo'   => [ 'id' => 13,    'name'  => t( 'Survey logo' ) ],
                'survey-meta'   => [ 'id' => 14,    'name'  => t( 'Survey meta image' ) ],
                'settings'      => [ 'id' => 15,    'name'  => t( 'Settings' ) ]
            ];
        } );

        // 2-Step verification code inserted
        actions()->add_action( 'after:user:verification-code-inserted', function( $action, $user_obj, $code, $type ) {
            // Send verification code for authentication confirmation
            if( $type == 1 ) {
                try {
                    $mail = $user_obj->mail( '2step_verification' )
                        ->useDefaultShortcodes()
                        ->setShortcodes( [
                            '%CODE%'    => $code,
                            '%NAME%'    => $user_obj->getDisplayName()
                        ] );

                    try {
                        $mail->send();
                    }
                    catch( \Exception $e ) { }
                }
                catch( \Exception $e ) { }

            // Send verification code for email verification
            } else if( $type == 2 ) {
                try {
                    $mail = $user_obj->mail( 'email_verification' )
                        ->useDefaultShortcodes()
                        ->setShortcodes( [
                            '%CODE%'    => $code,
                            '%NAME%'    => $user_obj->getDisplayName()
                        ] );

                    try {
                        $mail->send();
                    }
                    catch( \Exception $e ) { }
                }
                catch( \Exception $e ) { }

            // Send verification code for password recovery
            } else if( $type == 3 ) {
                try {
                    $mail = $user_obj->mail( 'authorization_password_recovery' )
                        ->useDefaultShortcodes()
                        ->setShortcodes( [
                            '%CODE%'    => $code,
                            '%NAME%'    => $user_obj->getDisplayName()
                        ] );

                    try {
                        $mail->send();
                    }
                    catch( \Exception $e ) { }
                }
                catch( \Exception $e ) { }
            }
        } );

        // Default survey's pages
        filters()->add_filter( 'survey_pages', function( $filter, $def ) {
            return [ 
                'thank_you'     => [
                    'title'     => t( 'Thank you' ),
                    'content'   => t( 'Thank you' ),
                    'isDefault' => true
                ],

                'disqualified'  => [
                    'title'     => t( 'Disqualification' ),
                    'content'   => t( 'Your answer has been disqualified!' ),
                    'isDefault' => true
                ]
            ];
        } );

        // Default media servers
        filters()->add_filter( 'media_servers', function( $filter, $def ) {
            return [
                'default' => [
                    'upload_image'  => [ $this, 'defaultMediaUploadImages' ],
                    'upload_file'   => [ $this, 'defaultMediaUploadFiles' ],
                    'delete_file'   => [ $this, 'defaultMediaDeleteFile' ],
                    'file_url'      => [ $this, 'defaultMediaFileUrl' ],
                    'file_preview'  => [ $this, 'defaultMediaFilePreview' ],
                    'max_size'      => [ $this, 'defaultMediaMaxSize' ]
                ] 
            ];
        } );

        // Default IP to Country
        filters()->add_filter( 'ipToCountry', function( $filter, $def, $IP ) {
            $context    = stream_context_create( [ 'http'=> [ 'timeout' => 1 ] ] );
            $content    = file_get_contents( 'https://api.iplocation.net/?ip=' . $IP, false, $context );
            
            if( !$content )
            return;

            $json       = json_decode( $content, true );
            if( isset( $json['response_code'] ) && $json['response_code'] == '200' )
            return $json['country_code2'];
        } );

        // Default web connector
        filters()->add_filter( 'web-connector', function( $filter, $url, $options ) {
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL,              $url );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT,   0 );
            curl_setopt( $ch, CURLOPT_TIMEOUT,          $options['timeout'] );
            curl_setopt( $ch, CURLOPT_HTTPHEADER,       $options['headers'] );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,   $options['ssl_verify'] );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER,   $options['return_transfer'] );
    
            if( $options['method'] == 'POST' ) {
                curl_setopt( $ch, CURLOPT_POST,         true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS,   http_build_query( $options['post'] ) );
            }
    
            $r              = [];
            $r['content']   = curl_exec( $ch );
            $r['http_code'] = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    
            curl_close( $ch );

            return $r;
        } );

        // After survey webhook
        filters()->add_filter( 'webhook:after-survey', function( $filter, $export, $url, $survey_id, $response_id ) {
            $conn   = new \util\connector( $url );
            $conn   ->setMethod( 'POST' )
                    ->setPostFields( [ 'response' => $export ] )
                    ->Open();
        } );

        // Referral program - register
        actions()->add_action( 'after:user:register', function( $action, $user ) {
            if( $user->getRefId() && $user->isVerified() && ( $options = get_option_json( 'ref_system' ) ) && ( !empty( $options['reg'] ) ) ) {
                $actions    = new \site\actions;
                $refId      = $user->getRefId();
                foreach( $options['reg'] as $points ) {
                    $refUser= users()
                            ->setId( $refId );
                    if( $refUser->getObject() ) {
                        $actions->add_user_loyalty_points( $refId, $points );
                        if( $refUser->getRefId() )
                        $refId  = $refUser->getRefId();
                        else
                        break;
                    } else
                        break; 
                }
            }
        } );

        // Referral program - register - when verified
        actions()->add_action( 'after:approve-kyc-admin', function( $action, $admin_user, $user ) {
            $user   = users()
                    ->setId( $user );

            if( $user->getObject() && $user->getRefId() && ( $options = get_option_json( 'ref_system' ) ) && ( !empty( $options['reg'] ) ) ) {
                $actions    = new \site\actions;
                $refId      = $user->getRefId();
                foreach( $options['reg'] as $points ) {
                    $refUser= users()
                            ->setId( $refId );
                    if( $refUser->getObject() ) {
                        $actions->add_user_loyalty_points( $refId, $points );
                        if( $refUser->getRefId() )
                        $refId  = $refUser->getRefId();
                        else
                        break;
                    } else
                        break; 
                }
            }
        } );

        // Referral program - upgrade
        actions()->add_action( 'after:upgrade', function( $action, $method, $type, $user, $months, $plan ) {
            if( $user->getObject() && $user->getRefId() && ( $options = get_option_json( 'ref_system' ) ) && ( !empty( $options['eachupgrade'] ) ) ) {
                $actions    = new \site\actions;
                $refId      = $user->getRefId();
                foreach( $options['eachupgrade'] as $points ) {
                    $refUser= users()
                            ->setId( $refId );
                    if( $refUser->getObject() ) {
                        $actions->add_user_loyalty_points( $refId, $points );
                        if( $refUser->getRefId() )
                        $refId  = $refUser->getRefId();
                        else
                        break;
                    } else
                        break; 
                }
            }
        } );

        // Boxes
        require 'filters/boxes.php';

        // Default blocks
        require 'helpers/blocks.php';
    }

    private function executePayment() {
        if( isset( $_GET['executePayment'] ) && ( $gateway = ( $_GET['gateway'] ?? false ) ) ) {
            $methods = filters()->do_filter( 'deposit-methods', [] );

            if( in_array( $gateway, array_keys( $methods ) ) ) {

                // processing order
                if( isset( $_GET['subscriptionId'] ) ) {

                    if( !isset( $_GET['subscriptionToken'] ) )
                    return ;

                    $class = call_user_func( $methods[$gateway]['class'] );

                    try {
                        $result = $class->executePayment();

                        if( !isset( $result['status'] ) )
                        return ;

                        switch( $result['status'] ) {
                            case 'failed':
                                // Display failed message
                                site()->alerts[] = [ 'text' => t( 'Error!' ), 'status' => 'error' ];
                            break;

                            case 'approved':
                                // Get subscription
                                $subscription   = subscriptions( (int) $_GET['subscriptionId'] );
                                if( !$subscription->getObject() ) {
                                    // Display error message
                                    site()->alerts[] = [ 'text' => t( 'Error. Please contact us.' ), 'status' => 'error' ];
                                    return ;
                                }

                                $info = $subscription->getInfoJson();

                                // Insert order
                                me()->actions()->approveOrder( (int) $_GET['subscriptionId'], $_GET['subscriptionToken'], $result );

                                // Add invoice & receipt
                                $invoicing  = me()->invoicing();
                                $invoicing  ->newInvoice( [
                                    [ $info['plan'], $info['months'], $info['total'] ],
                                ] )         
                                            ->setType( 'plan' )
                                            ->createInvoice( true );
                                            
                                // Display success message
                                site()->alerts[] = [ 'text' => t( 'Payment processed. Thank you!' ), 'status' => 'success' ];
                            break;

                        }
                    }

                    catch( \Exception $e ) {
                        // Display an error message
                        site()->alerts[] = [ 'text' => esc_html( $e->getMessage() ), 'status' => 'info' ];
                    }

                // extend subscription
                } else if( isset( $_GET['extendSubId'] ) ) {

                    if( !isset( $_GET['subscriptionToken'] ) )
                    return ;

                    $class = call_user_func( $methods[$gateway]['class'] );

                    try {
                        $result = $class->executePayment();

                        if( !isset( $result['status'] ) )
                        return ;

                        switch( $result['status'] ) {
                            case 'failed':
                                // Display failed message
                                site()->alerts[] = [ 'text' => t( 'Error!' ), 'status' => 'error' ];
                            break;

                            case 'approved':
                                // Get subscription
                                $subscription   = subscriptions( (int) $_GET['extendSubId'] );
                                if( !$subscription->getObject() ) {
                                    // Display error message
                                    site()->alerts[] = [ 'text' => t( 'Error. Please contact us.' ), 'status' => 'error' ];
                                    return ;
                                }

                                $info = $subscription->getInfoJson();

                                // Insert order
                                me()->actions()->extendOrder( (int) $_GET['extendSubId'], $_GET['subscriptionToken'], $result );

                                // Add invoice & receipt
                                $invoicing  = me()->invoicing();
                                $invoicing  ->newInvoice( [
                                    [ $info['plan'], $info['months'], $info['total'] ],
                                ] )         
                                            ->setType( 'plan' )
                                            ->createInvoice( true );

                                // Display success message
                                site()->alerts[] = [ 'text' => t( 'Payment processed. Thank you!' ), 'status' => 'success' ];
                            break;

                        }
                    }

                    catch( \Exception $e ) {
                        // Display an error message
                        site()->alerts[] = [ 'text' => esc_html( $e->getMessage() ), 'status' => 'info' ];
                    }

                // deposit funds
                } else {

                    $class = call_user_func( $methods[$gateway]['class'] );

                    try {
                        $result = $class->executePayment();
                        
                        if( !isset( $result['status'] ) )
                        return ;

                        switch( $result['status'] ) {
                            case 'failed':
                                // Insert transaction
                                me()->actions()->deposit( (double) $result['total'], $result['description'], 0 );

                                // Display failed message
                                site()->alerts[] = [ 'text' => t( 'Error!' ), 'status' => 'error' ];
                            break;

                            case 'approved':
                                // Insert transaction & update user's balance
                                me()->setBalance( (double) $result['total'] )->actions()->deposit( (double) $result['total'], $result['description'], 2 );
                                
                                // Display success message
                                site()->alerts[] = [ 'text' => t( 'Payment processed. Thank you!' ), 'status' => 'success' ];
                            break;

                        }
                    }

                    catch( \Exception $e ) {
                        // Display an error message
                        site()->alerts[] = [ 'text' => esc_html( $e->getMessage() ), 'status' => 'info' ];
                    }
                
                }

            }
        }
    }

    public function defaultMediaUploadImages( array $files, string $imageSize = 'full', int $limit = 1 ) {
        $files      = new \util\upload( $files, $limit );
        $uFiles     = $files->accept( 'image', [ $imageSize ] )
                    ->upload()
                    ->getUploadedFiles();

        if( count( $uFiles ) )
        return $uFiles;

        throw new \Exception( t( 'Error' ) );
    }

    public function defaultMediaUploadFiles( array $files, int $limit = 1 ) {
        $files      = new \util\upload( $files, $limit );
        $uFiles     = $files->upload()
                    ->getUploadedFiles();

        if( count( $uFiles ) )
        return $uFiles;

        throw new \Exception( t( 'Error' ) );
    }

    public function defaultMediaDeleteFile( string $src ) {
        if( is_writable( DIR . '/' . $src ) ) {
            unlink( DIR . '/' . $src );
            return true;
        }
    }

    public function defaultMediaFileUrl( string $path ) {
        return site_url( $path );
    }

    public function defaultMediaFilePreview( array $upload, array $options ) {
        switch( ( $options['handler'] ?? '' ) ) {
            case 'file':
                switch( $upload['file']['ftype'] ) {
                    case 'archive':
                        return '<i class="fas fa-file-archive"></i>' . $upload['file']['name'];
                    break;
                    case 'doc':
                        return '<i class="fas fa-file-alt"></i>' . $upload['file']['name'];
                    break;
                    case 'video':
                        return '<i class="fas fa-file-video"></i>' . $upload['file']['name'];
                    break;
                    case 'audio':
                        return '<i class="fas fa-file-audio"></i>' . $upload['file']['name'];
                    break;
                    default: 
                        return $upload['file']['name'];
                }
            break;

            default:
            return site_url( current( $upload['local'] ) );
        }
    }

    public function defaultMediaMaxSize() {
        return ( filters()->do_filter( 'max_default_upload_size', MAX_SIZE_FILE_TYPE ) * 1024 * 1024 );
    }

    public function getMediaCategories() {
        return filters()->do_filter( 'media-categories', [] );
    }

    public function getMediaCategoryId( string $strId ) {
        $categories = filters()->do_filter( 'media-categories', [] );
        return $categories[$strId]['id'] ?? NULL; 
    }

    public function surveyThemes() {
        return filters()->add_filter( 'survey-themes', function() {
            return [
                't1' => [
                    'name'  => t( 'Default theme' ),
                    'path'  => implode( '/', [ DIR, SURVEY_DIR, 'template', 't1' ] )
                ],
                't2' => [
                    'name'  => t( 'Classic' ),
                    'path'  => implode( '/', [ DIR, SURVEY_DIR, 'template', 't2' ] )
                ],
                't3' => [
                    'name'  => t( 'Modern' ),
                    'path'  => implode( '/', [ DIR, SURVEY_DIR, 'template', 't3' ] )
                ]
            ]; 
        } );
    }

    public function defaultTemplates() {
        return [
            'net_promoter_score' => [
                'title'         => t( 'Net Promoter Score' ),
                'description'   => t( 'Find out how well your brand is performing.' ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' )
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( "What's the main reason for your score?" ),
                    ],
                    [
                        'type'      => 'dropdown',
                        'title'     => t( 'What is your gender?' ),
                        'options'   => [ t( 'Male' ), t( 'Female' ) ]
                    ],
                    [
                        'type'      => 'dropdown',
                        'title'     => t( 'What is your age?' ),
                        'options'   => [ '< 17', '12-24', '25-34', '35-44', '45-54', '55-64', '>= 65' ]
                    ]
                ]
            ],
            'customer_satisfaction' => [
                'title'         => t( 'Customer satisfaction' ),
                'description'   => t( 'Your customers can make or break your business. Hear from them.' ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' ),
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( "What's the main reason for your score?" ),
                    ]
                ]
            ],
            'transactional_feedback' => [
                'title'         => t( 'Transactional feedback' ),
                'description'   => t( "Get feedback pertaining to a customers' recent purchase." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' ),
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( "What's the main reason for your score?" ),
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( 'How responsive have we been to your questions or concerns about our products?' ),
                        'options'   => [ t( 'Not at all responsive' ), t( 'Somewhat responsive' ), t( 'Extremely responsive' ), t( 'N/A' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( 'How likely are you to purchase any of our products again?' ),
                        'options'   => [ t( 'Not at all likely' ), t( 'Somewhat likely' ), t( 'Extremely likely' ) ]
                    ]
                ]
            ],
            'tenant_satisfaction' => [
                'title'         => t( 'Tenant Satisfaction' ),
                'description'   => t( "A tenant feedback survey is a simple way for property managers to capture valuable feedback from their residents." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' ),
                    ],
                    [
                        'type'      => 'matrix_dd',
                        'title'     => t( 'How satisfied are you with the following:' ),
                        'labels'    => [ t( 'Interior walls, flooring and all other surfaces' ), t( 'Interior appliances such as refrigerator, dishwasher, and stove.' ), t( 'Exterior walls and roofing' ) ],
                        'columns'   => [ t( 'Your experience' ) => [ t( 'Very Dissatisfied' ), t( 'Dissatisfied' ), t( 'Neutral' ), t( 'Satisfied' ), t( 'Very Satisfied' ) ] ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "Are maintenance requests handled to your satisfaction?" ),
                        'options'   => [ t( 'Yes' ), t( 'No' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Of the following, which feature or amenity would be least and most important in justifying a small increase in rent?' ),
                        'labels'    => [ t( 'New fitness center' ), t( 'Marble countertops' ), t( 'New interior appliances' ), t( 'Remodeled kitchens' ), t( 'Remodeled bathrooms' ) ],
                        'columns'   => [ t( 'Least Important' ), t( 'Most Important' ) ]
                    ]
                ]
            ],
            'tenant_satisfaction' => [
                'title'         => t( 'Tenant satisfaction' ),
                'description'   => t( "A tenant feedback survey is a simple way for property managers to capture valuable feedback from their residents." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' ),
                    ],
                    [
                        'type'      => 'matrix_dd',
                        'title'     => t( 'How satisfied are you with the following:' ),
                        'labels'    => [ t( 'Interior walls, flooring and all other surfaces' ), t( 'Interior appliances such as refrigerator, dishwasher, and stove.' ), t( 'Exterior walls and roofing' ) ],
                        'columns'   => [ t( 'Your experience' ) => [ t( 'Very Dissatisfied' ), t( 'Dissatisfied' ), t( 'Neutral' ), t( 'Satisfied' ), t( 'Very Satisfied' ) ] ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "Are maintenance requests handled to your satisfaction?" ),
                        'options'   => [ t( 'Yes' ), t( 'No' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Of the following, which feature or amenity would be least and most important in justifying a small increase in rent?' ),
                        'labels'    => [ t( 'New fitness center' ), t( 'Marble countertops' ), t( 'New interior appliances' ), t( 'Remodeled kitchens' ), t( 'Remodeled bathrooms' ) ],
                        'columns'   => [ t( 'Least Important' ), t( 'Most Important' ) ]
                    ]
                ]
            ],
            'employee_satisfaction' => [
                'title'         => t( 'Employee satisfaction' ),
                'description'   => t( "This employee template focuses more on satisfaction questions, but also covers engagement." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'multi',
                        'title'     => t( "Which department are you a part of?" ),
                        'options'   => [ t( 'Department 1' ), t( 'Department 2' ), t( 'Department 3' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "Which location do you work in?" ),
                        'options'   => [ t( 'Location 1' ), t( 'Location 2' ), t( 'Location 3' ) ]
                    ],
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' ),
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Compensation and Benefits' ),
                        'description' => t( 'How satisfied are you with the following:' ),
                        'labels'    => [ t( 'My overall compensation' ), t( 'Healthcare benefits' ), t( 'PTO and vaction' ), t( '401k / Retirement plan' ) ],
                        'columns'   => [ t( 'Very Dissatisfied' ), t( 'Dissatisfied' ), t( 'Neutral' ), t( 'Satisfied' ), t( 'Very Satisfied' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Co-Workers & Relationships' ),
                        'description' => t( 'How would you agree with the following:' ),
                        'labels'    => [ t( 'My supervior supports me in doing my job' ), t( 'I have a good relationship with my supervisor' ), t( 'I feel like I can count on my peers when I need help' ) ],
                        'columns'   => [ t( 'Strongly Disagree' ), t( 'Disagree' ), t( 'Neutral' ), t( 'Agree' ), t( 'Strongly Agree' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Culture and Mission' ),
                        'description' => t( 'How would you agree with the following:' ),
                        'labels'    => [ t( 'The overall business strategy' ), t( 'Leadership puts us in a position to be successful' ), t( 'My feedback and ideas for improvements are valued' ), t( 'I am inspired to do my best work' ) ],
                        'columns'   => [ t( 'Strongly Disagree' ), t( 'Disagree' ), t( 'Neutral' ), t( 'Agree' ), t( 'Strongly Agree' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Career Development' ),
                        'description' => t( 'How satisfied are you with the following:' ),
                        'labels'    => [ t( 'Career advancement' ), t( 'Opportunities to apply my talents' ), t( 'Opportunities to grow and challenge myself' ) ],
                        'columns'   => [ t( 'Strongly Disagree' ), t( 'Disagree' ), t( 'Neutral' ), t( 'Agree' ), t( 'Strongly Agree' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'Work Environment' ),
                        'description' => t( 'How would you agree with the following:' ),
                        'labels'    => [ t( 'I have the tools needed to do my job' ), t( 'My work environment is conducive to getting my job done' ), t( 'I have clarity in my job role and understand exactly what I need to do' ) ],
                        'columns'   => [ t( 'Strongly Disagree' ), t( 'Disagree' ), t( 'Neutral' ), t( 'Agree' ), t( 'Strongly Agree' ) ]
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( 'What I like best about this company:' ),
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( 'What I would like to help change:' ),
                    ]
                ]
            ],
            'volunteer_feedback' => [
                'title'         => t( 'Volunteer feedback' ),
                'description'   => t( "Capture the opinions of people attending volunteer events. This data is crucial to keep volunteers coming back to support your cause." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'net_prom',
                        'title'     => t( 'How likely is it that you would recommend this company to a friend or colleague?' )
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How much of an impact do you feel your volunteer work had?" ),
                        'options'   => [ t( 'Almost none' ), t( 'A moderate amount' ), t( 'A great deal' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How useful were the volunteer training sessions at our organization?" ),
                        'options'   => [ t( 'Not at all useful' ), t( 'Somewhat useful' ), t( 'Very useful' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How easy was it to get along with the other volunteers at this organization?" ),
                        'options'   => [ t( 'Not easy' ), t( 'Somewhat easy' ), t( 'Very easy' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How friendly were the staff at our organization?" ),
                        'options'   => [ t( 'Not friendly' ), t( 'Somewhat friendly' ), t( 'Very friendly' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How likely are you to continue volunteering at our organization in the future?" ),
                        'options'   => [ t( 'Not likely' ), t( 'Somewhat likely' ), t( 'Very likely' ) ]
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( 'Comments, concerns, or additional feedback:' ),
                    ]
                ]
            ],
            'parent_feedback' => [
                'title'         => t( 'Parent feedback' ),
                'description'   => t( "Find out how a students parent(s) feel about their child’s education. Can be useful to spark conversation for parent teacher conferences or to understand a child’s home environment." ),
                'options'       => [],
                'questions'     => [
                    [
                        'type'      => 'multi',
                        'title'     => t( "How often do you wish you could talk with teachers at your child's school?" ),
                        'options'   => [ t( "I don't need to meeet" ), t( 'Once a semester' ), t( 'Once a month' ), t( 'Once a week' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How confident are you that you can help your child can developer good friendships?" ),
                        'options'   => [ t( 'Not confident' ), t( 'Neutral' ), t( 'Confident' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "To what extent do you know how your child is doing socially at school?" ),
                        'options'   => [ t( 'Not at all' ), t( 'Somewhat' ), t( 'Quite a bit' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How often do you and your child talk when he or she is having a problem with others?" ),
                        'options'   => [ t( 'Rarley' ), t( 'Sometimes' ), t( 'Quite a bit' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "Does your child work better alone or in groups?" ),
                        'options'   => [ t( 'Alone' ), t( 'Groups' ), t( 'Both' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How much of a sense of belonging does your child feel at his or her school?" ),
                        'options'   => [ t( 'No belonging at all' ), t( 'Some belonging' ), t( 'Quite a bit of belonging' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How often do you help your child understand the content he or she is learning in school?" ),
                        'options'   => [ t( 'Rarley' ), t( 'Sometimes' ), t( 'Quite a bit' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How safe do you feel your children are at our school?" ),
                        'options'   => [ t( 'Not safe' ), t( 'Somewhat safe' ), t( 'Very safe' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How confident are you that our school meets your child's learning needs?" ),
                        'options'   => [ t( 'Not confident' ), t( 'Neutral' ), t( 'Confident' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How confident are you in your ability to support your child's learning at home?" ),
                        'options'   => [ t( 'Not confident' ), t( 'Neutral' ), t( 'Confident' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How much effort does your child put into school-related tasks?" ),
                        'options'   => [ t( 'Not much effort' ), t( 'Some effort' ), t( 'A lot of effort' ) ]
                    ],
                    [
                        'type'      => 'textarea',
                        'title'     => t( "What can our school do to better meet your child's learning needs?" ),
                    ],
                    [
                        'type'      => 'section_title',
                        'title'     => t( "Questions about interests and activities:" ),
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "How well do the activities offered at your child's school match his or her interests?" ),
                        'options'   => [ t( 'Not well' ), t( 'Somewhat well' ), t( 'Very well' ) ]
                    ],
                    [
                        'type'      => 'multi',
                        'title'     => t( "If we offered after school activities, how often would you volunteer?" ),
                        'options'   => [ t( "I can't volunteer" ), t( 'Once a semester' ), t( 'Once a month' ), t( 'Once a week' ) ]
                    ],
                    [
                        'type'      => 'matrix_mc',
                        'title'     => t( 'If we were to offer the following, what would be the least appealing and most appealing after school activity?' ),
                        'labels'    => [ t( 'Move night' ), t( 'Arts and crafts' ), t( 'Book club' ), t( 'Once a week motivational speaker' ), t( 'Financial planning course' ) ],
                        'columns'   => [ t( 'Least Important' ), t( 'Most Important' ) ]
                    ]
                ]
            ]
        ];
    }
    
}
<?php

// Create custom posts
developer()->customPosts( [
    // FAQ posts
    'faqs' => [
        'name'  => t( 'FAQs', 'def-theme' ),
        'icon'  => '<i class="fas fa-question"></i>',
        'view'  => t( 'View questions', 'def-theme' ),
        'new'   => t( 'New question', 'def-theme' ),
        'edit'  => t( 'Edit question', 'def-theme' ),
        'use'   => [
            'categories' => [ 'lang' => [] ],
            'lang',
            'hide-templates'
        ],
        'category_use' => [ 'slug' ],
        'pages' => [ 'home' => [ 'tc', t( 'Show on homepage', 'def-theme' ) ], 'lang' => [] ],
        'default_template' => 'page_help.php',
        'metaboxes' => [
            'home'  => [
                'type'  => 'checkbox',
                'label' => t( 'Show on homepage', 'def-theme' ),
                'title' => t( 'Yes', 'def-theme' ),
                'list_view' => function( $value, $page ) {
                    return ( $value ? t( 'Yes', 'def-theme' ) : t( 'No', 'def-theme' ) );
                },
                'vld' => function( $value ) {
                    if( $value )
                    return 1;
                },
                'position' => 10
            ]
        ]
    ],

    // Blog posts
    'blog-posts' => [
        'name'  => t( 'Blog', 'def-theme' ),
        'icon'  => '<i class="fas fa-pencil-alt"></i>',
        'view'  => t( 'View posts', 'def-theme' ),
        'new'   => t( 'New post', 'def-theme' ),
        'edit'  => t( 'Edit post', 'def-theme' ),
        'use'   => [
            'blocks',
            'categories'    => [ 'slug' => [], 'parent' => [], 'lang' => [] ],
            'thumb',
            'slug',
            'lang',
        ],
        'pages' => [ 'thumb' => [ '', t( 'Thumbnail', 'def-theme' ) ], 'lang' => [] ],
        'category_use' => [ 'lang' ],
        'metaboxes'     => [
            'duration'  => [
                'type'  => 'text',
                'label' => t( 'Reading duration', 'def-theme' ),
                'list_view' => function( $value, $page ) {
                    return $value;
                },
                'position' => 99
            ]
        ]
    ],

    // Help posts
    'help-posts' => [
        'name'  => t( 'Helpdesk', 'def-theme' ),
        'icon'  => '<i class="fas fa-life-ring"></i>',
        'view'  => t( 'View posts', 'def-theme' ),
        'new'   => t( 'New post', 'def-theme' ),
        'edit'  => t( 'Edit post', 'def-theme' ),
        'use'   => [
            'categories'    => [ 'slug' => [], 'parent' => [], 'lang' => [] ],
            'thumb'         => [], // use thumbnails
            'meta'
        ],
        'pages' => [ 'slug' => [], 'template' => [], 'lang' => [] ],
        // 'use'   => [ 'slug' ],
        'metaboxes'     => [
            'excerpt'   => [
                'type'  => 'textarea',
                'label' => t( 'Excerpt', 'def-theme' ),
                'position' => 5.4
            ],
        ],
        'templates' => [
            [
                'name'  => 'Help Posts template',
                'file'  => 'page_help.php'
            ]
        ]
    ]
] );

developer()->customBlocks( [
    'helpBlock' => [
        'name'      => t( 'Help', 'def-theme' ),
        'label'     => t( 'Help label', 'def-theme' ),
        'options'   => function( $blockId ) {
            return [
                $blockId => [ 'type' => 'group', 'fields' => [
                    'text'      => [ 'type' => 'textarea', 'label' => t( 'Content', 'def-theme' ), 'placeholder' => t( 'Write here ...', 'def-theme' ), 'classes' => 'btarea' ],
                    'options'   => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Options', 'def-theme' ), 'fields' => [
                        'intent'    => [ 'type' => 'select', 'label' => t( 'Text intent', 'def-theme' ), 'options' => [
                            ''          => t( 'Unset', 'def-theme' ),
                            'tifirst'   => t( 'First paragraph', 'def-theme' ),
                            'tiall'     => t( 'All paragraphs', 'def-theme' )
                        ] ],
                        'size'      => [ 'type' => 'select', 'label' => t( 'Text size', 'def-theme' ), 'options' => [
                            ''      => t( 'Normal', 'def-theme' ),
                            'big'   => t( 'Big', 'def-theme' )
                        ] ]
                    ], 'grouped' => false ] ], 'grouped' => false ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'Text block', 'def-theme' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'text' ]
                ] ]
            ];
        },
        'render'    => function( $data ) {
            $text = new \util\text( $data['text'] );
            return '
            <div class="txt' . ( !empty( $data['intent'] ) ? ' ' . esc_html( $data['intent'] ) : '' ) . ( !empty( $data['size'] ) ? ' ' . esc_html( $data['size'] ) : '' ) . '">
                ' . $text->fromBB() . '
            </div>';
        },
        'useFor'    => [ 'help-posts', 'help-posts' ]
    ]
] );

developer()->customTemplates( [
    [
        'name'      => 'Help page custom',
        'file'      => 'page_help.php',
        'useFor'    => [ 'help-posts' ]
    ]
] );

themes()->options( t( 'Front page', 'def-theme' ), function() {
    $fields     = [];
    foreach( getLanguages() as $lang ) {
        $fields['index_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'index_msg'     => [ 'type' => 'text', 'label' => t( 'Hero section title', 'def-theme' ), 'placeholder' => t( 'Build beautiful surveys.', 'def-theme' ) ],
            'subtitle'      => [ 'type' => 'text', 'label' => t( 'Hero section subtitle', 'def-theme' ) ],
            'hero_img'      => [ 'type' => 'image', 'label' => t( 'Image', 'def-theme' ), 'category' => 'theme-options' ],
            'cta_name'      => [ 'type' => 'text', 'label' => t( 'Call to action button (label)', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
            'cta_link'      => [ 'type' => 'text', 'label' => t( 'Call to action button (link)', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
            'sections'      => [ 'type' => 'repeater', 'label' => t( 'Sections', 'def-theme' ), 'add_button' => t( 'Add new section', 'def-theme' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'New section', 'def-theme' ), 'fields' => [
                'title' => [ 'type' => 'text', 'label' => t( 'Title', 'def-theme' ) ],
                'text'  => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'image' => [ 'type' => 'image', 'label' => t( 'Image', 'def-theme' ), 'category' => 'theme-options' ]
                ], 'grouped' => false ] ], 'grouped' => false ]
            ] ],
            'boxes'         => [ 'type' => 'repeater', 'label' => t( 'Text boxes', 'def-theme' ), 'add_button' => t( 'Add new box', 'def-theme' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'New box', 'def-theme' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'label' => t( 'Title', 'def-theme' ) ],
                'text'      => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'link_name' => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
                ], 'grouped' => false ] ], 'grouped' => false ]
            ] ],
            'cta'           => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Call to action box', 'def-theme' ), 'fields' => [
                'text'      => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'link_name' => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
            ], 'grouped' => false ] ] ],
            'hl_link'       => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Call to action link', 'def-theme' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'description' => t( 'This link appears after the hero section', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
            ], 'grouped' => false ] ] ]
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 1 );

themes()->options( t( 'Respondents page', 'def-theme' ), function() {
    $fields     = [];
    foreach( getLanguages() as $lang ) {
        $fields['respondents_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'sections'      => [ 'type' => 'repeater', 'label' => t( 'Sections', 'def-theme' ), 'add_button' => t( 'Add new section', 'def-theme' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'New section', 'def-theme' ), 'fields' => [
                'title' => [ 'type' => 'text', 'label' => t( 'Title', 'def-theme' ) ],
                'text'  => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'image' => [ 'type' => 'image', 'label' => t( 'Image', 'def-theme' ), 'category' => 'theme-options' ]
                ], 'grouped' => false ] ], 'grouped' => false ]
            ] ],
            'cta'           => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Call to action box', 'def-theme' ), 'fields' => [
                'text'      => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'link_name' => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
                ], 'grouped' => false ] ] ]
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 2 );

themes()->options( t( 'Pricing page', 'def-theme' ), function() {
    $fields     = [];
    foreach( getLanguages() as $lang ) {
        $fields['pricing_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'personal'      => [ 'type' => 'repeater', 'label' => t( 'Personal plans sections', 'def-theme' ), 'add_button' => t( 'Add new section', 'def-theme' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'New section', 'def-theme' ), 'fields' => [
                'title' => [ 'type' => 'text', 'label' => t( 'Title', 'def-theme' ) ],
                'text'  => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'image' => [ 'type' => 'image', 'label' => t( 'Image', 'def-theme' ), 'category' => 'theme-options' ]
                ], 'grouped' => false ] ], 'grouped' => false ]
            ] ],
            'team'          => [ 'type' => 'repeater', 'label' => t( 'Team plans sections', 'def-theme' ), 'add_button' => t( 'Add new section', 'def-theme' ), 'fields' => [
                [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'New section', 'def-theme' ), 'fields' => [
                'title' => [ 'type' => 'text', 'label' => t( 'Title', 'def-theme' ) ],
                'text'  => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'image' => [ 'type' => 'image', 'label' => t( 'Image', 'def-theme' ), 'category' => 'theme-options' ]
                ], 'grouped' => false ] ], 'grouped' => false ]
            ] ],
            'cta'           => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Call to action box', 'def-theme' ), 'fields' => [
                'text'      => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'link_name' => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
                ], 'grouped' => false ] ] ]
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 3 );

themes()->options( t( 'FAQs page', 'def-theme' ), function() {
    $fields     = [];
    foreach( getLanguages() as $lang ) {
        $fields['faqs_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'cta'           => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Call to action box', 'def-theme' ), 'fields' => [
                'text'      => [ 'type' => 'textarea', 'label' => t( 'Text', 'def-theme' ) ],
                'link_name' => [ 'type' => 'text', 'label' => t( 'Link name', 'def-theme' ), 'placeholder' => t( 'Click me', 'def-theme' ) ],
                'link_url'  => [ 'type' => 'text', 'label' => t( 'URL', 'def-theme' ), 'placeholder' => t( 'https:// ...', 'def-theme' ) ],
                ], 'grouped' => false ] ] ]
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 4 );

themes()->options( t( 'Logo', 'def-theme' ), function() {
    $fields['logo_normal']  = [ 'type' => 'image',  'label' => t( 'Logo', 'def-theme' ), 'category' => 'theme-options' ];
    $fields['logo_small']   = [ 'type' => 'image',  'label' => t( 'Logo on fixed menu', 'def-theme' ), 'category' => 'theme-options' ];
    return $fields;
}, 5 );

themes()->options( t( 'Header', 'def-theme' ), function() {
    foreach( getLanguages() as $lang ) {
        $fields['header_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'phone_no'  => [ 'type' => 'text',  'label' => t( 'Phone number', 'def-theme' ) ],
            'email'     => [ 'type' => 'text',  'label' => t( 'Email address', 'def-theme' ) ],
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 6 );

themes()->options( t( 'Footer', 'def-theme' ), function() {
    foreach( getLanguages() as $lang ) {
        $fields['footer_' . $lang['locale_e']] = [ 'type' => 'dropdown',  'fields' => [ [ 'label' => esc_html( $lang['name'] ), 'fields' => [
            'footer_copyright'=> [ 'type' => 'textarea',  'label' => sprintf( t( 'Copyright text (%s)', 'def-theme' ), esc_html( $lang['name'] ) ) ],
            'social_profiles' => [ 'type' => 'repeater',  'label' => t( 'Social media profiles', 'def-theme' ), 'fields' => [
                [ 'type' => 'inline-group', 'fields' => [ 
                    'name'  => [ 'type' => 'text', 'label' => t( 'Name' ) ],
                    'link'  => [ 'type' => 'text', 'label' => t( 'Link' ) ]
                ], 'grouped' => false ]
            ], 'add_button' => t( 'Add profile', 'def-theme' ), 'grouped' => false ]
        ], 'grouped' => false ] ] ];
    }
    return $fields;
}, 7 );

menus()->create( 'theme_main_menu', t( 'Main menu', 'def-theme' ), t( "Default's theme main menu", 'def-theme' ), [
    'home'          => [ 'type' => 'link', 'label' => t( 'Home', 'def-theme' ), 'url' => site_url() ],
    'respondents'   => [ 'type' => 'link', 'label' => t( 'For respondents', 'def-theme' ), 'url' => site_url( 'respondents' ) ],
    'pricing'       => [ 'type' => 'link', 'label' => t( 'Pricing', 'def-theme' ), 'url' => site_url( 'pricing' ) ],
    'faq'           => [ 'type' => 'link', 'label' => t( 'FAQ', 'def-theme' ), 'url' => site_url( 'faq' ) ],
    'more'          => [ 'type' => 'link', 'label' => t( 'More', 'def-theme' ), 'url' => '#' ],
    'help'          => [ 'type' => 'link', 'parent_id' => 'more', 'label' => t( 'Help', 'def-theme' ), 'url' => site_url( 'help' ) ],
    'blog'          => [ 'type' => 'link', 'parent_id' => 'more', 'label' => t( 'Blog', 'def-theme' ), 'url' => site_url( 'blog' ) ],
    'help-templates'=> [ 'type' => 'link', 'parent_id' => 'more', 'label' => t( 'Templates', 'def-theme' ), 'url' => site_url( 'help/templates' ) ],
    'help-examples' => [ 'type' => 'link', 'parent_id' => 'more', 'label' => t( 'Examples', 'def-theme' ), 'url' => site_url( 'help/examples' ) ],
    'contact_us'    => [ 'type' => 'link', 'parent_id' => 'more', 'label' => t( 'Contact us', 'def-theme' ), 'url' => site_url( 'contact' ) ]
] );

menus()->create( 'theme_footer_menu', t( 'Footer menu', 'def-theme' ), t( "Default's theme footer menu", 'def-theme' ), [
    'contact_us'    => [ 'type' => 'link', 'label' => t( 'Contact us', 'def-theme' ), 'url' => site_url( 'contact' ) ]
] );
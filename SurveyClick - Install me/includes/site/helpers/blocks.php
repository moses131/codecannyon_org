<?php

developer()->customBlocks( [
    // Text block
    'text' => [
        'name'      => t( 'Text' ),
        'label'     => t( 'Text block' ),
        'options'   => function( $blockId ) {
            return [
                $blockId => [ 'type' => 'group', 'fields' => [
                    'text'      => [ 'type' => 'textarea', 'label' => t( 'Content' ), 'placeholder' => t( 'Write here ...' ), 'classes' => 'btarea' ],
                    'options'   => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Options' ), 'fields' => [
                        'intent'    => [ 'type' => 'select', 'label' => t( 'Text intent' ), 'options' => [
                            ''          => t( 'Unset' ),
                            'tifirst'   => t( 'First paragraph' ),
                            'tiall'     => t( 'All paragraphs' )
                        ] ],
                        'size'      => [ 'type' => 'select', 'label' => t( 'Text size' ), 'options' => [
                            ''      => t( 'Normal' ),
                            'big'   => t( 'Big' )
                        ] ],
                        'cont'      => [ 'type' => 'checkbox', 'label' => t( 'Container' ), 'title' => t( 'Use container' ) ]
                    ], 'grouped' => false ] ], 'grouped' => false ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'Text block' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'text' ]
                ] ]
            ];
        },
        'render'    => function( $data ) {
            $text = new \util\text( $data['text'] );
            return '
            <div class="txt' . ( !empty( $data['cont'] ) ? ' cont' : '' ) . ( !empty( $data['intent'] ) ? ' ' . esc_html( $data['intent'] ) : '' ) . ( !empty( $data['size'] ) ? ' ' . esc_html( $data['size'] ) : '' ) . '">
                ' . $text->fromBB() . '
            </div>';
        },
        'useFor'    => [ 'website' ]
    ],

    // Image block
    'image' => [
        'name'      => t( 'Image' ),
        'label'     => t( 'Image block' ),
        'options'   => function( $blockId ) {
            return [ 
                $blockId => [ 'type' => 'group', 'fields' => [
                    'image' => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'block' ],
                    'copy'  => [ 'type' => 'text', 'label' => t( 'Copyrights' ) ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'Image block' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'image' ]
                ] ]
            ];
        },
        'render'    => function( $data ) {
            $text   = new \util\text( $data['copy'] );
            $markup = '
            <div class="image">';
                if( !empty( $data['image'] ) ) {
                    $markup .= '<div><img src="' . esc_url( current( $data['image'] ) ) . '"></div>';
                }
                $markup .= '
                <div class="c">' . $text->fromBB() . '</div>
            </div>';

            return $markup;
        },
        'useFor'    => [ 'website' ]
    ],

    // Title
    'title' => [
        'name'      => t( 'Title' ),
        'label'     => t( 'Title block' ),
        'options'   => function( $blockId ) {
            return [
                $blockId => [ 'type' => 'group', 'fields' => [
                    'text'      => [ 'type' => 'text', 'label' => t( 'Title' ), 'placeholder' => t( 'Write here ...' ), 'classes' => 'btarea' ],
                    'options'   => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Options' ), 'fields' => [
                        'align'     => [ 'type' => 'select', 'label' => t( 'Title align' ), 'options' => [
                            'tl'    => t( 'Left' ),
                            'tc'    => t( 'Center' ),
                            'tr'    => t( 'Right' )
                        ], 'value' => 'center' ],
                        'size'      => [ 'type' => 'select', 'label' => t( 'Text size' ), 'options' => [
                            ''      => t( 'Normal' ),
                            'big'   => t( 'Big' )
                        ] ]
                    ], 'grouped' => false ] ], 'grouped' => false ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'Title block' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'title' ]
                ] ]
            ];
        },
        'settings'  => function() {

        },
        'render'    => function( $data ) {
            $align  = $data['align'] ?? 'tc';
            $style  = $data['size'] ?? '';
            $text   = new \util\text( $data['text'] );

            if( $style == 'big' ) {
                return '
                <div class="htitle">
                    <div class="' . esc_html( $align ) . '">' . $text->fromBB() . '</div>
                </div>';
            } else {
                return '
                <div>
                    <h1 class="' . esc_html( $align ) . '">' . $text->fromBB() . '</h1>
                </div>';
            }
        },
        'useFor'    => [ 'website' ]
    ],

    // Quote
    'quote' => [
        'name'      => t( 'Quote' ),
        'label'     => t( 'Quote block' ),
        'options'   => function( $blockId ) {
            return [
                $blockId => [ 'type' => 'group', 'fields' => [
                    'text'      => [ 'type' => 'textarea', 'label' => t( 'Content' ), 'placeholder' => t( 'Write here ...' ), 'classes' => 'btarea' ],
                    'options'   => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'Options' ), 'fields' => [
                        'author'    => [ 'type' => 'text', 'label' => t( 'Author' ) ]
                    ], 'grouped' => false ] ], 'grouped' => false ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'Quote block' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'quote' ]
                ] ]
            ];
        },
        'render'    => function( $data ) {
            $text   = new \util\text( $data['text'] );

            $markup = '
            <div class="bq">
                <div>' . $text->fromBB() . '</div>';
                if( !empty( $data['author'] ) )
                $markup .= '<div>&mdash; ' . esc_html( $data['author'] ) . '</div>';
            $markup .= '
            </div>';

            return $markup;
        },
        'useFor'    => [ 'website' ]
    ],

    // HTML block
    'html' => [
        'name'      => t( 'HTML' ),
        'label'     => t( 'HTML Block' ),
        'options'   => function( $blockId ) {
            return [ 
                $blockId => [ 'type' => 'group', 'fields' => [
                    'html' => [ 'type' => 'textarea', 'label' => t( 'HTML' ) ],
                    '_label'    => [ 'type' => 'hidden', 'value' => t( 'HTML Block' ) ],
                    '_type'     => [ 'type' => 'hidden', 'value' => 'html' ]
                ] ]
            ];
        },
        'render'    => function( $data ) {
            $markup = '
            <div class="html">';
                $markup .= nl2br( $data['html'] );
                $markup .= '
            </div>';

            return $markup;
        },
        'useFor'    => [ 'website' ]
    ]  
] );
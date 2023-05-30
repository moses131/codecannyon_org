<?php

ajax()->add_call( 'init-blocks', function() {
    return cms_json_encode( [
        'buttons'   => [
            'add_b'     => '<i class="fas fa-chevron-up"></i><span>' . t( 'Add before' ) . '</span>',
            'add_a'     => '<i class="fas fa-chevron-down"></i><span>' . t( 'Add after' ) . '</span>', 
            'remove'    => '<i class="fas fa-trash-alt"></i><span>' . t( 'Remove' ) . '</span>'
        ]
    ] );
});

ajax()->add_call( [ 'add-block-before', 'add-block-after' ], function( $filter ) {
    $type = $_POST['data']['post_type'] ?? NULL;

    if( !$type )
    return ;

    $form = new \markup\front_end\form_fields( [
        'block'    => [ 'type' => 'select', 'options' => array_map( function( $b ) {
            return $b['name'];
        }, developer()->blocksForType( $type ) ) ],
        'post_type' => [ 'type' => 'hidden', 'value' => esc_html( $type ) ],
        'position'  => [ 'type' => 'hidden', 'value' => $filter ],
        [ 'type' => 'button', 'label' => t( 'Add' ) ]
    ] );

    $fields                     = $form->build();
    $attributes                 = [];
    $attributes['data-ajax']    = ajax()->get_call_url( 'add-block-proc', [ 'bl' => 'blocks' ] );

    $content = '<form id="add_block" class="form add_block_form"' . \util\attributes::add_attributes( filters()->do_filter( 'add_block_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $content .= $fields;
    $content .= '</form>';

    return cms_json_encode( [ 'title' => t( 'Add new block' ), 'content' => $content ] );
});

ajax()->add_call( 'add-block-proc', function() {
    $data = $_POST['data'] ?? [];

    if( !isset( $data['post_type'] ) || !isset( $data['block'] ) || !( $block = developer()->getBlock( $data['block'], $data['post_type'] ) ) )
    return ;

    $builder    = new \dev\builder\blocks;
    $builder    ->setCurrentBlock( $block, $data['block'] );
    $content    = $builder->getBlock();

    return cms_json_encode( [ 'callbacks' => [ '{ "callback": "add_block", "block": "' . base64_encode( $content ) . '", "place": "' . ( !empty( $data['position'] ) && $data['position'] == 'add-block-after' ? 'after' : 'before' ) . '" }' ] ] );
});
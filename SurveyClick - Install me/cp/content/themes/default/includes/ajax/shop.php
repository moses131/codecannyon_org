<?php

/** SHOP */
ajax()->add_call( 'shop', function() {
    if( isset( $_GET['method'] ) ) {
        switch( $_GET['method'] ) {
            // Add item
            case 'add-item':
                if( !isset( $_GET['item'] ) ) {
                    $response['success'] = false;
                } else {
                    $response['success'] = shop()->addItem( (int) $_GET['item'] );
                    $options    = isset( $_POST['options'] ) ? json_decode( $_POST['options'], true ) : false;
                    $cResponse  = filters()->do_filter( 'shop_item_added', false, (int) $_GET['item'], $response['success'], $options );

                    if( $cResponse ) {
                        $response = array_merge( $response, $cResponse );
                    } else if( $response['success'] ) {
                        $response['callback'] = '{
                            "callback": "shop_item_add_markup",
                            "id": "' . (int) $_GET['item'] . '",
                            "html": "' . base64_encode( '<a href="#" class="btn" data-remove-item><i class="fas fa-times"></i></a>' ) . '"
                        }';
                    }
                }
                return cms_json_encode( $response );
            break;

            // Remove item
            case 'remove-item':
                if( !isset( $_GET['item'] ) ) {
                    $response['success'] = false;
                } else {
                    $response['success'] = shop()->removeItem( (int) $_GET['item'] );
                    $options    = isset( $_POST['options'] ) ? json_decode( $_POST['options'], true ) : false;
                    $cResponse  = filters()->do_filter( 'shop_item_removed', false, (int) $_GET['item'], $response['success'], $options );

                    if( $response['success'] ) {
                        $response['callback'] = '{
                            "callback": "shop_item_remove_markup",
                            "id": "' . (int) $_GET['item'] . '",
                            "html": "' . base64_encode( '<a href="#" class="btn" data-add-item><i class="fas fa-cart-plus"></i></a>' ) . '"
                        }';
                    }
                }
                return cms_json_encode( $response );
            break;

            // Checkout
            case 'checkout':
                return cms_json_encode( $response );
            break;
        }
    }
});

/** CART */
ajax()->add_call( 'shop-cart', function() {
    $items  = shop()->items2();

    if( count( $items ) == 0 ) {
        return cms_json_encode( [ 'title' => t( 'Checkout' ), 'content' => '<div class="msg alert mb0">' . t( 'Your cart is empty' ) . '</div>' ] );
    }

    $content = '
    <div class="table">';

    foreach( $items as $item ) {
        $content .= '
        <div class="td" data-item="' . $item->item . '">
            <div class="tl w150p">' . esc_html( $item->name ) . '</div>
            <div class="tc"><i class="fas fa-star cl3"></i> ' . $item->price . '</div>
            <div class="wa mla">
                <a href="#" data-remove-item class="removeItem">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>';
    }

    $content .= '
    </div>';

    $content .= '<div><a href="#" data-popup="shop-checkout" class="btn">' . t( 'Checkout' ) . '</a></div>';

    return cms_json_encode( [ 'title' => t( 'Cart' ), 'content' => $content, 'classes' => [ 's2' ] ] );
});

/** CHECKOUT */
ajax()->add_call( 'shop-checkout', function() {
    $items  = shop()->items2();

    if( count( $items ) == 0 ) {
        return cms_json_encode( [ 'title' => t( 'Checkout' ), 'content' => '<div class="msg alert mb0">' . t( 'Your cart is empty' ) . '</div>', 'remove_prev' => true ] );
    }

    $content = '
    <div class="table t2">';

    $total  = 0;
    $f      = [];

    foreach( shop()->items2() as $item ) {
        $content .= '
        <div class="td" data-item="' . $item->item . '">
            <div class="tl w150p">' . esc_html( $item->name ) . '</div>
            <div class="tc"><i class="fas fa-star cl3"></i> ' . $item->price . '</div>
        </div>';
        $total  += $item->price;
        $f[$item->id] = [ 'type' => 'hidden', 'value' => $item->id ];
    }

    $content .= '
    </div>';

    $content .= '
    <div class="table ns nb">
        <div class="td">
            <div class="tl w150p">' . t( 'Total' ) . '</div>
            <div class="tc"><i class="fas fa-star cl3"></i> ' . $total . '</div>
        </div>
    </div>';

    $form = new \markup\front_end\form_fields( [
        'items' => [ 'type' => 'group', 'fields' => $f ],
        [ 'type' => 'button', 'label' => t( 'Send order' ) ]
    ] );

    $fields = $form->build();
    $attributes = [];
    $attributes['data-ajax'] = ajax()->get_call_url( 'send-order' );
    $content .= '<form id="checkout" class="form checkout"' . \util\attributes::add_attributes( filters()->do_filter( 'checkout_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $content .= $fields;
    $content .= '</form>';

    return cms_json_encode( [ 'title' => t( 'Checkout' ), 'content' => $content, 'classes' => [ 's2' ] ] );
});

/** CHECKOUT */
ajax()->add_call( 'send-order', function() {
    $my_cart    = shop()->items2();
    $confirmed  = $_POST['data']['items'] ?? [];
    $confirmed2 = array_intersect_key( $my_cart, $confirmed );
    $error      = cms_json_encode( [ 'show_popup' => [ 'content' => showMessage( t( 'Error!' ), false, '<i class="fas fa-times"></i>', 'error' ), 'remove_prev_all' => true ], 'goto' => [
        'url'       => 'shop',
        'path'      => [ 'shop' ]
    ] ] );

    if( count( $my_cart ) !== count( $confirmed2 ) )
    return $error;

    $total      = 0;
    $items      = [];

    foreach( $my_cart as $item ) {
        $total              += $item->price;
        $items[$item->id]   = [ 'name' => $item->name, 'qt' => 1, 'total' => $item->price ];
    }

    if( $total > me()->getLoyaltyPoints() ) {
        return cms_json_encode( [ 'show_popup' => [ 'content' => showMessage( t( 'Insufficient loyalty points' ), false, '<i class="fas fa-times"></i>', 'error' ), 'remove_prev_all' => true ] ] ); 
    }

    if( !shop()->addOrder( cms_json_encode( $items ), $total ) )
    return $error;

    return cms_json_encode( [ 'show_popup' => [ 'content' => showMessage( t( 'All done!' ), false, '<i class="fas fa-check"></i>' ), 'remove_prev_all' => true ], 'goto' => [
        'url'       => 'orders',
        'path'      => [ 'orders' ],
        'options'   => [ 'status' => 1 ]
    ] ] );
});

/** VIEW ORDER */
ajax()->add_call( 'view-order', function() {
    if( !isset( $_POST['data']['id'] ) )
    return ;

    $order = new \query\shop\orders( (int) $_POST['data']['id'] );
    if( !$order->getObject() || $order->getUserId() != me()->getId() )
    return ;

    $user       = $order->getUser();
    $summary    = $order->getSummaryJson();
    $content    = '<h2>' . t( 'Summary' ) . '</h2>';

    if( !empty( $summary ) ) {
        $content .= '
        <div class="table t2">

        <div class="tr">
            <div class="tl w150p">' . t( 'Item' ) . '</div>
            <div class="tc">' . t( 'Quantity' ) . '</div>
            <div class="tc">' . t( 'Stars' ) . '</div>
        </div>';

        $total  = 0;

        foreach( $order->getSummaryJson() as $item ) {
            $content .= '
            <div class="td">
                <div class="tl w150p">' . esc_html( $item['name'] ) . '</div>
                <div class="tc">' . esc_html( $item['qt'] ) . '</div>
                <div class="tc">' . esc_html( $item['total'] ) . '</div>
            </div>';
            $total  += (double) $item['total'];
        }

        $content .= '
        </div>';

        $content    .= '<h2>' . t( 'Total' ) . '</h2>';

        $content .= '
        <div class="table ns nb mb0">
            <div class="td">
                <div class="tl w150p"></div>
                <div></div>
                <div class="tc">' . $total . '</div>
            </div>
        </div>';
    }

    return cms_json_encode( [ 'title' => t( 'View order' ), 'classes' => [ 's2' ], 'content' => $content ] );
});
'use strict';

(function($) {

  $.shop = function( options ) {
    let option = $.extend( {
        cartLink:       '[data-shop-cart]',
        addButton:      'data-add-item',
        removeButton:   'data-remove-item'
    }, options );

    let t           = this,
    cartLink        = $( option.cartLink ),
    shopItems       = $( option.Items );
    let count_attr  = cartLink.find( '[data-count-attr]' ),
    count_html      = cartLink.find( '[data-count]' );

    $( document ).on( 'click', '[' + option.addButton + ']', function(e) {
        e.preventDefault();

        let ts      = $(this);
        let item    = parseInt( ts.closest( '[data-item]' ).attr( 'data-item' ) );

        ts.cms_simple_call( {
            URL: utils.ajax_url + '&action=shop&method=add-item&item=' + item,
            data: { options: decodeUnicode( ts.data( 'options' ) ) }
        }, function( data ) {
            //
        } );
    });

    $( document ).on( 'click', '[' + option.removeButton + ']', function(e) {
        e.preventDefault();

        let ts      = $(this);
        let itemEl  = ts.closest( '[data-item]' );
        let item    = parseInt( itemEl.attr( 'data-item' ) );

        ts.cms_simple_call( {
            URL: utils.ajax_url + '&action=shop&method=remove-item&item=' + item,
            data: { options: decodeUnicode( $(this).data( 'options' ) ) }
        }, function( data ) {
            if( ts.hasClass( 'removeItem' ) ) {
                itemEl.remove();
            }
        } );
    });

    t.add = function( data ) {
        if( count_attr ) {
            count_attr.attr( 'data-count-attr', ( parseInt( count_attr.attr( 'data-count-attr' ) ) + 1 ) );
        }

        if( count_html ) {
            count_html.text( ( parseInt( count_html.text() ) + 1 ) ); 
        }

        shopItems.find( '[data-item="' + data.id + '"]' ).find( '[' + option.addButton + ']' ).replaceWith( decodeUnicode( data.html ) );
    }

    t.remove = function( data ) {
        if( count_attr ) {
            count_attr.attr( 'data-count-attr', ( parseInt( count_attr.attr( 'data-count-attr' ) ) - 1 ) );
        }

        if( count_html ) {
            count_html.text( ( parseInt( count_html.text() ) - 1 ) ); 
        }

        shopItems.find( '[data-item="' + data.id + '"]' ).find( '[' + option.removeButton + ']' ).replaceWith( decodeUnicode( data.html ) );
    }

    return t;
};

}( jQuery ));
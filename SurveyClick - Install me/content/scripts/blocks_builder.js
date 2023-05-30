'use strict';

(function($) {

  $.fn.blocks_builder = function( options ) {
    let option = $.extend( {
      action: 'init-blocks',
      type:   'website'
    }, options );

    let t           = this;
    let moreBtns    = $( '<ul class="btnset"></ul>' );
    let currentBlock;

    function build() {
        t.on( 'click', '.more > a', function(e) {
            e.preventDefault();
            if( $(this).hasClass( 'active' ) ) {
                $(this).removeClass( 'active' );
                return ;
            }
            
            let opts        = $(this).next( 'ul.btnset' );
            currentBlock    = $(this).closest( 'li' );

            if( opts.length ) {
                $(this).addClass( 'active' );
                return ;
            }

            $(this).after( moreBtns );
            $(this).addClass( 'active' );
        });

        t.on( 'click', '.head > a', function(e) {
            e.preventDefault();
            $( this ).closest( 'li' ).toggleClass( 'open' );
        });

        t.on( 'input', '.head > a > span', function(e) {
            let v = $(this).text();
            $(this).closest( 'li' ).find( '[name$="[_label]"]' ).val( v );
        });

        t.on( 'click', '.more .btnset a', function(e) {
            e.preventDefault();
            let prop = $(this).prop( 'action' );
            if( prop ) {
                switch( prop ) {
                    case 'add_b':
                    case 'add_a':
                        t.show_popup( {
                            URL: utils.ajax_url + '&bl=blocks&action=' + ( prop == 'add_a' ? 'add-block-after' : 'add-block-before' ),
                            data: { post_type: option.type }
                        } );
                    break;

                    case 'remove':
                        currentBlock.remove();
                    break;
                }
            }
        });

        t.next( '.blocks' ).on( 'click', '> .new a', function(e) {
            e.preventDefault();
            currentBlock = undefined;
            t.show_popup( {
                URL: utils.ajax_url + '&bl=blocks&action=add-block-after',
                data: { post_type: option.type }
            } );
        });
    }

    t.addBefore = function( el ) {
        if( currentBlock === undefined ) {
            t.prepend( el );
        } else {
            currentBlock.before( el );
        }
    }

    t.addAfter = function( el ) {
        if( currentBlock === undefined ) {
            t.append( el );
        } else {
            currentBlock.after( el );
        }
    }

    t.cms_simple_call( {
        URL: utils.ajax_url + '&bl=blocks&action=' + option.action,
        data: { post_type: option.type }
        }, function( data ) {
        $.each( data.buttons, function( k, v ) {
            let aBtn = $( '<a href="#">' + v + '</a>' ).prop( 'action', k );
            moreBtns.append( $( '<li/>' ).append( aBtn ) );
        } );
        build();
    } );

    return t;
};

}( jQuery ));

function init_blocks( t, data ) {
    $( data.container ).blocks_builder( data );
}

function add_block( t, data ) {
    let el    = $( decodeUnicode( data.block ) );
    let popup = t.closest( '.popup' ).prop( 'popup' );
    if( data.place && data.place == 'after' ) {
        popup.addAfter( el );
    } else {
        popup.addBefore( el ); 
    }

    popup.sp_close();
}
$(function() {
    "use strict";

    $( document ).on( 'click', '.tabs > li > a', function(e) {
        e.preventDefault();
        let t = $(this).parent();
        if( t.hasClass( 'active' ) ) {
            t.removeClass( 'active' );
        } else {
            t.closest( '.tabs' ).find( '> li.active' ).removeClass( 'active' );
            t.toggleClass( 'active' );
        }
    } );

    $( document ).on( 'click', '.mmenu', function(e) {
        e.preventDefault();
        $( 'body' ).toggleClass( 'mmactive' );
    } );

    $( document ).on( 'click', 'ul.menu li > a.dd', function(e) {
        e.preventDefault();
        var menuActive  = $(this).parent();
        var menu    = menuActive.closest( '.menu' );
        if( menuActive.hasClass( 'item-active' ) ) {
            menuActive  .removeClass( 'item-active' );
            menu        .removeClass( 'item-active' );
            menuActive  = undefined;
        } else {
            menuActive  .addClass( 'item-active' );
            menu        .addClass( 'item-active' );
        }
    } );

    $( '.tline' ).each( function() {
        let t       = $(this);
        let items   = t.find( '> li' );
        let count   = items.length;
        if( !count ) return ;
        let active  = t.find( '> .active' );
        let pos;
        
        if( !active.length ) {
            active  = items.eq(0);
            active  .addClass( 'active' );
            pos     = 0;
        } else {
            pos     = active.index();
        }
        
        setInterval( function() {
            pos++;
            if( pos >= count ) pos = 0;
            active  .removeClass( 'active' );
            active  = items.eq( pos );
            active  .addClass( 'active' );
        }, 3000 );
    } );

    $( 'select.lang' ).on( 'change', function(e) {
        let t = $(this);
        $.post( utils.ajax_url + '&action=switch-language&type=ajax&lang=' + t.val(), function() {
            window.location = location.href;
        }, "json" );
    } );

    $( '.menu-ls a' ).on( 'click', function(e) {
        e.preventDefault();
        let t = $(this);
        $.post( utils.ajax_url + '&action=switch-language&type=ajax&lang=' + t.data( 'lang' ), function() {
            window.location = location.href;
        }, "json" );
    } );

    /** On scroll plugin */
    $.survey_pro_on_scroll = ( options ) => {
        let option = $.extend( {
            'offsetMenu': 300,
            'offsetMenuType': 'pixels',
            'cssClassMenu': 'small'
        }, options );
        
        let pageH   = $( window ).height();
        let menu    = option?.menu ? $( option.menu ) : false;
        let backTT  = option?.backToTop ? option.backToTop : false
        let bTT;
        let inView  = [];

        if( option?.inView ) {
            $.each( option.inView, ( el, cb ) => {
                if( $(el).length ) {
                    let offTop = $(el).offset().top;
                    inView.push( { top: offTop - pageH, bottom: offTop + $(el).height(), cb: cb } );
                }
            });
        }

        $( window ).on( 'scroll', (e) => {
            let sTop = $( this ).scrollTop();

            // sticky menu
            if( menu ) {
                if( sTop > option.offsetMenu ) {
                    menu.addClass( option.cssClassMenu );
                } else {
                    menu.removeClass( option.cssClassMenu );
                }
            }

            if( inView.length ) {
                $.each( inView, ( k, v ) => {
                    if( v && sTop >= v.top && sTop <= v.bottom ) {
                        v.cb();
                        delete inView[k];
                    }
                });
            }

            if( backTT ) {
                if( bTT !== false && sTop >= pageH ) {
                    backTT( 'in' );
                    bTT = false;
                } else if ( bTT !== true && sTop < pageH ) {
                    backTT( 'out' );
                    bTT = true;
                }
            }
        });
    };

    $.survey_pro_on_scroll( { 'menu': 'body', 'cssClassMenu': 'mfixed', 'offsetMenu': 600 } );
});
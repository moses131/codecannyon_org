/** GLOBAL VARIABLES */

// Loaded scripts
var scripts     = [];

// Loaded styles
var styles      = [];

// Intervals
var intervals   = {};

// Ajax navigation
var goto;

// Menu
var nav;
var nav_el          = {};
var nav_current_el;

// Survey
var sdashboard;

// Shop
var shop;

$(function() {
    "use strict";

    /** NAVIGATION */

    nav_el['main']  = $( '.nav:last' );
    nav_current_el  = $( '.nav' ).find( '.active.current' );
    goto            = nav_el['main'].cms_go_to();

    $(document).on( 'click', '.nav li:not(:first) > a.dd', function(e) {
        e.preventDefault();
        $(this).parent().toggleClass( 'active' );
    });

    goto.beforeGo( () => {
        $( 'body' ).removeClass( 'mmactive' );
    }, 'mobile-nav' );

    $(document).on( 'click', '.mmenu > a', function(e) {
        e.preventDefault();
        $( 'body' ).toggleClass( 'mmactive' );
    });

    $(document).on( 'click', '.show_filters', function(e) {
        e.preventDefault();
        $( this ).closest( '.table-container' ).prevAll( '.filters:first').toggleClass( 'visib' );
    });

    $( document ).on( 'keyup', '[name$="[title]"]', function() {
        var t = $(this);
        var l = t.closest( '.form_dropdown' ).find( ' > div > span' );
        l.text( t.val() );
    });

    $( document ).on( 'click', '.td .vopts', function(e) {
        e.preventDefault();
        $(this).closest( '.td' ).addClass( 'active' );
    });

    $( document ).on( 'click', '.td.active', function(e) {
        e.preventDefault();
        $(this).removeClass( 'active' );
    });

    $( '.view-interests' ).on( 'click', function(e) {
        e.preventDefault();
        $(this).closest( '.half-nav' ).addClass( 'p-interests' );
    });

    $( '.interests a' ).on( 'click', function(e) {
        e.preventDefault();
        $(this).closest( '.half-nav' ).removeClass( 'p-trending' ).removeClass( 'p-interests' );
    });

    $( '.view-trending' ).on( 'click', function(e) {
        e.preventDefault();
        $(this).closest( '.half-nav' ).removeClass( 'p-interests' ).addClass( 'p-trending' );
    });

    $( '.view-user-tags' ).on( 'click', function(e) {
        e.preventDefault();
        $(this).closest( '.half-nav' ).removeClass( 'p-trending' ).removeClass( '.p-interests' );
    });

    $( document ).on( 'click', '.ph-links .setsel', function(e) {
        e.preventDefault();
        let phlinks = $(this).closest( '.ph-links' );
        phlinks.find( '.active' ).removeClass( 'active' );
        $(this).addClass( 'active' );
    });

    $( document ).on( 'click', '[data-sh]', function(e) {
        e.preventDefault();
        let t = $(this);
        let d = t.data( 'sh' );
        t.parent().find( d ).toggleClass( 'hidden' );
    });
    
    $( document ).on( 'keyup', 'input[name="page[slug]"]', function(e) {
        $(this).prev( 'span' ).find( '> span' ).text( $(this).val() );
    });

    /** */
    
    $( document ).on( 'click', '.c-content .shimg > a', function(e) {
        e.preventDefault();
        $(this).closest( 'ul' ).prev().toggleClass( 'imgv' );
    });

    $( document ).on( 'click', '.page-head.title2 .title .rlnks > a.more', function(e) {
        e.preventDefault();
        $(this).closest( '.title' ).toggleClass( 'more-v' );
    });

    $( document ).on( 'click', '.c-content .shpost > a', function(e) {
        e.preventDefault();
        $(this).closest( 'ul' ).parent().toggleClass( 'postv' );
    });

    $( document ).on( 'click', '.c-cover .res-btns > a.resize', function(e) {
        e.preventDefault();

        var clicked = false, clickY, defTop;
        let cover = $(this).closest( '.c-cover' );
        let container = cover.find( '.p-cover > div' );

        defTop = Math.abs( parseInt( container.css('marginTop') ) ) + container.parent().scrollTop();

        container.removeAttr('style');

        $( '.p-cover' ).scrollTop( defTop );

        cover.on({
            'mousemove': function(e) {
                if( clicked ) {
                    $('.p-cover').scrollTop( defTop + (clickY - e.pageY));
                }
            },
            'mousedown': function(e) {
                clicked = true;
                clickY = e.pageY;
            },
            'mouseup': function() {
                clicked = false;
                defTop = container.parent().scrollTop();
            },
            'mouseleave': function() {
                clicked = false;
                defTop = container.parent().scrollTop();
            }
        });

        cover.addClass( 'resizing stouch' );
    });

    var playing_now;

    $( 'video' ).on( 'playing', function() {
        if( typeof playing_now !== 'undefined' && playing_now !== $(this)[0] ) {
            playing_now.pause();
        }
        playing_now = $(this)[0];
    });

    $.cms_do_on_scroll( { 
        900: [ 
            [ '.left-nav', '>', -5, function( el ) { el.addClass( 'fixed' ); }, function( el ) { el.removeClass( 'fixed' ) } ],
            [ '.boxes > li', '>', -50, function( el ) {  }, function( el ) { el.removeClass( 'fixed' ) } ]
        ]
     } );
     
    $( document ).on( 'change input', '[data-fform]:not(.active) input, [data-fform]:not(.active) textarea, [data-fform]:not(.active) select', function() {
        let form = $(this).closest( '[data-fform]' );
        form.addClass( 'active' );
        form.cms_fform( $(this) );
    });

    $( document ).on( 'change', 'input[name="data[attachment]"]', function() {
        let t = $(this);
        let tfls = this.files;
        let file = window.URL.createObjectURL( tfls[0] );
        switch( tfls[0].type.split( '/' )[0] ) {
            case 'video':
                let container = $( '<div class="attachment"><div><video src="' + file + '" controls /></div><div class="thumbnail"><canvas></canvas><a href="#"><i class="fas fa-camera"></i></a></div></div>' );
                t.parent().prepend( container );
                video_create_thumbnail2( container );
            break;
            case 'image':
                t.parent().prepend( '<div class="attachment"><img src="'+ file + '" alt="" /></div>' );
            break;
            default:
                t.prev().html( '<span class="elp">' + tfls[0].name + '</span>' );
        }
    });

    $( document ).on( 'click', '.nav-opts:not(.active) > li:not(.active) a', function(e) {
        e.preventDefault();
        let t       = $(this);
        let p       = t.parent(),
        opts        = t.closest( '.nav-opts' );
        opts.addClass( 'active' );
        let nav = opts.cms_options_nav( { el: '+ .settings_box', URL: opts.data( 'sto' ) } );
        if( p.hasClass( 'more' ) ) {
            nav.current( opts.find( 'li.active' ) );
            p.toggleClass( 'active' );
        } else {
            opts.find( 'li.active' ).removeClass( 'active' );
            nav.current( p ).action( t );
        }
    });

    $( document ).on( 'click', '.form_line.add_button > [data-add_button]', function(e) {
        e.preventDefault();
        let t = $(this);
        let l = t.parents( '.form_line:first' );
        let n = l.next();
        let s = t.data( 'add_button' );
        let i = Math.floor( Math.random() * Date.now() );
        l.before( '<div class="form_line">' + n.html().replaceAll( s + '[#NEW#]', s + '[' + i + ']' ) + '</div>' );
    });

    $( document ).on( 'click', '.form_line.l_opts > a.remove', function(e) {
        e.preventDefault();
        let t = $(this);
        let l = t.parents( '.form_line:eq(1)' );
        l.remove();
    });

    $( document ).on( 'click', '.btnset > li > a[href="#"]', function(e) {
        e.preventDefault();
        let t   = $(this);
        let ts  = t.next();
        let p   = t.parent();

        if( p.hasClass( 'disabled' ) ) {
            return ;
        }

        p.toggleClass( 'active' );

        $( 'html' ).on( 'click', function(e2) {
            if( ts[0] !== undefined && !t[0].contains( e2.target ) && !ts[0].contains(e2.target) ) {
                t.parent().toggleClass( 'active' );
                $(this).off();
            }
        });
    });

    $( document ).on( 'click', '.form_line.form_dropdown > div:first-of-type', function(e) {
        e.preventDefault();

        let t   = $(this);
        let ts  = t.next();
        let p   = t.closest( '.form_line' );

        p.toggleClass( 'visib' );

        $( 'html' ).on( 'click', function(e2) {
            if( ts[0] !== undefined && !t[0].contains( e2.target ) && !ts[0].contains(e2.target) ) {
                p.removeClass( 'visib' );
                $('html').unbind('click');
            }
        });
    });

    $( document ).on( 'keyup', '[data-isearch]:not(.active)', function() {
        let t = $(this);
        t.addClass( 'active' ).cms_input_search( { click_cb: t.data( 'cb' ) });
    });

    $( document ).on( 'click', '[data-copy]', function(e) {
        e.preventDefault();
        navigator.clipboard.writeText( $(this).data( 'copy') );
    });

    $( document ).on( 'click', '.nav .labelt > .lnk > a', function(e) {
        e.preventDefault();
        $(this).closest( '.label' ).toggleClass( 'active' );
    });

    $( document ).on( 'click', '.namesel > a', function(e) {
        e.preventDefault();
        let p   = $(this).parent();
        let fl  = p.closest( '.form_line' );
        let sel = fl.next( '[data-id="ff-data[comm_cat]"]' ).find( 'select[id="data[comm_cat]"]' );
        p.remove();
        fl.prev( 'input[data-id="data[community_id]"]' ).val( '' ).trigger( 'change' );
        sel.find( '> option:not([value="0"])' ).remove();
    });

    $( document ).on( 'click', 'a[href="#"]', function(e) {
        e.preventDefault();
    });

    $( document ).on( 'click', '.delcli', function(e) {
        e.preventDefault();
        $(this).closest( 'li' ).remove();
    })

    $(document).on( 'click', '.form_line .image-list > li.new > a', function() {
        let form    = $(this).closest( 'form' );
        let files   = form.prop( 'files' );
        if( !files ) {
            files   = {};
        } 
        let li      = $(this).closest( 'li' );
        let ul      = li.closest( 'ul' );
        let id      = ul.data( 'id' );
        let is_mult = ul.data( 'multi' );
        let input   = $( '<input type="file" name="image" accept="image/*"' + ( is_mult && is_mult != "0" ? ' multiple' : '' ) +  ' />' ).trigger( 'click' );

        input.on( 'change', function() {
            $.each( this.files, function( x, ufile ) {
                let fileName    = ufile.name.replace( /[^\w\d\.]/gi, '' );
                let fileId      = id + '[' + fileName + ']';
                if( !files[fileId] ) {
                    files[fileId]   = { 'value': ufile, 'filename': fileName };
                    form            .prop( 'files', files );
                    let el          = $( '<li><div style="background-image:url(\'' +  window.URL.createObjectURL( ufile ) + '\');"></div><a href="#" class="remove"><i class="fas fa-times"></i></a></li>' );
                    el              .prop( 'file-id', fileId );
                    li              .before( el );
                }
            });

            $(this).off();
        });
    });

    $(document).on( 'click', '.form_line .image-list > li > a.remove', function() {
        let li      = $(this).closest( 'li' );
        let id      = li.prop( 'file-id' );
        if( id ) {
            let form    = $(this).closest( 'form' );
            let files   = form.prop( 'files' );
            delete      files[id];
            form        .prop( 'files', files );
        }
        li.remove();
    });

    $(document).on( 'click', '.form_line .files-list > li.new > a', function() {
        let form    = $(this).closest( 'form' );
        let files   = form.prop( 'files' );
        if( !files ) {
            files   = {};
        } 
        let li      = $(this).closest( 'li' );
        let ul      = li.closest( 'ul' );
        let id      = ul.data( 'id' );
        let input   = $(this).next( 'input' ).trigger( 'click' );

        input.on( 'change', function() {
            $.each( this.files, function( x, ufile ) {
                let fileName    = ufile.name.replace( /[^\w\d\.]/gi, '' );
                let fileId      = id + '[' + fileName + ']';
                if( !files[fileId] ) {
                    files[fileId]   = { 'value': ufile, 'filename': fileName };
                    form            .prop( 'files', files );
                    let el          = $( '<li><div><i class="fas fa-file-upload"></i> ' + fileName + '</div><a href="#" class="remove"><i class="fas fa-times"></i></a></li>' );
                    el              .prop( 'file-id', fileId );
                    li              .before( el );
                }
            });

            $(this).off();
        });
    });

    $(document).on( 'click', '.form_line .files-list > li > a.remove', function() {
        let li      = $(this).closest( 'li' );
        let id      = li.prop( 'file-id' );
        if( id ) {
            let form    = $(this).closest( 'form' );
            let files   = form.prop( 'files' );
            delete      files[id];
            form        .prop( 'files', files );
        }
        li.remove();
    });

    $( document ).on( 'change', '.new_post_form [name="data[bg]"]', function() {
        let t = $(this);
        let f = t.closest( 'form' );
        t.show_popup( { URL: utils.ajax_url + '&action=new-post&type=rich', data: f.serialize() } );
    });

    $( document ).on( 'change', '.new_sh_post_form [name="data[bg]"]', function() {
        let t = $(this);
        let f = t.closest( 'form' );
        t.show_popup( { URL: utils.ajax_url + '&action=new-post&type=rich-shoutbox', data: f.serialize() } );
    });

    $( document ).on( 'change', '.rich_new_post_form [name="data[background]"]', function() {
        let t = $(this);
        let val = t.val();
        t.closest( 'form' ).find( '.text-hero' ).parent().removeAttr( 'class' ).addClass( 'col-' + val );
    });

    $( document ).on ( 'input', 'select.step_act', function(e) {
        e.preventDefault();
        let t   = $(this);
        let tar = t.closest( '.cconds' ).next();
        let val = t.val();
        if( val == 'message' ) {
            tar.removeClass( 'hidden' );
        } else {
            tar.addClass( 'hidden' );
        }
    });

    $( document ).on( 'click', 'body.sb-o', function(e) {
        if( e.target.tagName === 'BODY' ) {
            $(this).removeClass( 'sb-o' );
        }
    });

    $( document ).on( 'click', '.mob', function(e) {
        $( 'body' ).toggleClass( 'sb-o' );
    });

    $( document ).on( 'click', '.popup > .pop-ovl', function(e) {
        $(this).closest( '.popup' ).prop( 'popup-el' ).close();
    });

    $( document ).on( 'click', '.nav.nav3.fnav > li > a', function(e) {
        e.preventDefault();
        let t = $(this);
        var selected = t.closest( '.nav' ).find( '> li > a.selected' );

        goto.afterGo( function() {
            goto.beforeGo( function() {
                t.removeClass( 'selected' );
            }, 'xx' );
        });

        selected.removeClass( 'selected' );
        t.addClass( 'selected' );
    });

    $( document ).on( 'click', '.msg .close', function(e) {
        e.preventDefault();
        $(this).closest( '.msg' ).remove();
    });

    $( document ).on( 'click', '.edtt > a', function(e) {
        e.preventDefault();
        $(this).prev().focus();
    });


    $( document ).on( 'input', '.reportName, .reportInput input', function(e) {
        let t = $(this), input, title;
        if( t.prop( 'tagName' ) == 'INPUT' ) {
            if( !title ) {
                title = t.closest( '.table' ).find( '.reportName' );
            }
            title.text( t.val() );
        } else {
            if( !input ) {
                input = t.closest( '.table' ).find( '.reportInput input' );
            }
            input.val( t.text() );
        }
    });

    $( document ).on( 'click', '.viewn', function(e) {
        e.preventDefault();
        $(this).toggleClass( 'vsb' );
    });

    $( document ).on( 'change', '[data-roc]', function() {
        var url = $(this).data( 'roc' );
        window.location = url.replace( /%R/g, $(this).val() );
    });

    $( document ).on( 'input', '.chbxes [data-search]', function() {
        let t       = $(this);
        let find    = t.val();
        let list    = $(this).prop( 'search-list' );
        if( !list ) {
            let the_list = [];
            t.closest( '.chbxes' ).find( '> div label' ).each( function() {
                the_list.push( { 'el': $(this), 'text': $(this).text() } );
            });

            t.prop( 'search-list', the_list );
        }

        $.each( list, function( k, v ) {
            if( v.text.match( new RegExp( find, 'gi' ) ) ) {
                v.el.removeClass( 'h' );
            } else {
                v.el.addClass( 'h' );
            }
        });
    });

    $( document ).on( 'click', '.generate-password', function(e) {
        e.preventDefault();
        let chars = "0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        let passwordLength = 10;
        let password = "";

        for (var i = 0; i <= passwordLength; i++) {
            let randomNumber = Math.floor( Math.random() * chars.length );
            password += chars.substring( randomNumber, randomNumber+1 );
        }

        $(this).closest( '.form_line' ).prev().find( 'input' ).val( password );
    });

    // Add/remove checked result
    let lch_change = {};

    $( document ).on( 'change', '.lch > input', function(e) {
        e.preventDefault();
        let t   = $(this);
        let res = t.attr( 'name' ).replace( /\D/g, '' );
        let v   = t.val();
        let r   = {};
        r[v]    = t.is( ':checked' ) ? 1 : 0;
        clearTimeout( lch_change[res] );
        lch_change[res] = setTimeout( function() {
            t.cms_simple_call( { URL: utils.ajax_url + '&action=manage-result2&action2=add-label-item&result=' + res, data: { data: { results: r } } } );
        }, 500 );
    });

    function video_create_thumbnail2( container ) {
        setTimeout( function () {
            let video = container.find( 'video' );
            let thumb_trigger = container.find( '.thumbnail > a' );
            container.append( '<input type="hidden" name="data[videoAttrs][height]" value="' + video[0].videoHeight + '" />' );
            container.append( '<input type="hidden" name="data[videoAttrs][width]" value="' + video[0].videoWidth + '" />' );
            create_thumbnail_from_video( video, thumb_trigger );
        }, 500 );
    }

    function create_thumbnail_from_video( video, trigger ) {
        let canvas  = trigger.prev( 'canvas' );
        let vh      = 500 * ( video[0].videoHeight / video[0].videoWidth );
        canvas.attr( 'width', 500 );
        canvas.attr( 'height', vh );

        canvas[0].getContext( '2d' ).drawImage( video[0], 0, 0, 500, vh );
        canvas[0].toBlob( function( blob ) {
            video.closest( 'form' ).prop( 'prop-blob', [ [ 'data[video-thumbnail]', blob, 'vthumb.png' ] ] );
        }, 'image/png' );
    
        trigger.on( 'click', function() {
            canvas[0].getContext( '2d' ).drawImage( video[0], 0, 0, 500, vh );
            canvas[0].toBlob( function( blob ) {
                video.closest( 'form' ).prop( 'prop-blob', [ [ 'data[video-thumbnail]', blob, 'vthumb.png' ] ] );
            }, 'image/png' );
        });
    }
});

function resize_cover( t ) {
    let cv = t.closest( '.res-btns' ).nextAll( '.p-cover' );
    let co = cv.find( '> div' );
    return { 'offset': cv.scrollTop(), 'height': co.height() };
}

function cover_resized( t, main_obj, data ) {
    t.parents( '.c-cover' ).off().removeClass( 'resizing' );
    //alert(data.percent);
}

function markup_changer( t, main_obj, data ) {
    t.replaceWith( data.html );
}

function markup_main_changer( t, main_obj, data ) {
    main_obj.replaceWith( data.html );
}

function markup_change_btnset( t, data ) {
    t.closest( '.btnset' ).html( decodeUnicode( data.markup ) );
}

function part_markup_changer( t, main_obj, data ) {
    $.each( data.elements, function( k, value ) {
        $(k).html( atob( value ) );
    });

    if( typeof data.is_logged !== 'undefined' ) {
        if( data.is_logged == 1 && !utils.logged ) {
            utils.logged = true;
            if( !inbox ) {
                inbox = $.cms_inbox();
            } else {
                inbox.inbox_verifier();
            }
            if( !notif ) {
                notif = $.cms_notif();
            } else {
                notif.notif_verifier();
            }
        } else if( typeof inbox !== 'undefined' ) {
            utils.logged = false;
            inbox.stop_inbox_verifier();
            notif.stop_notif_verifier();
        }
    }
    main_obj.close();
}

function delete_post_action( t, main_obj ) {
    main_obj.closest( '.buttons' ).parents( 'li' ).slideUp( 500, function(){
        $(this).remove();
    });
    main_obj.close();
}

function close_inbox( t ) {
    inbox.removeLoader();
}

function notif_close( t ) {
    if( t.hasClass( 'c-fnotif' ) ) {
        notif.update_fnotif_count( 0 );
    } else {
        notif.update_notif_count( 0 );
    }
    notif.replay_notifier();
    t.removeClass( 'active' );
    t.find( 'ul.notif' ).remove();
}

function replace_cover_img( t, main, data ) {
    let ccover = main.parents( '.c-cover');
    let pcover = ccover.find( '.p-cover');
    pcover.removeClass( 'noco' );
    pcover.find( '> div' ).removeAttr( 'style');
    pcover.find( '.cimg' ).replaceWith( '<img src="' + data.image + '" class="cimg" alt="" />' );
    main.close();
}

function replace_avatar_img( t, main, data ) {
    let img = main.find( 'img');
    img.replaceWith( '<img src="' + data.image + '" class="cimg" alt="" />' );
    main.close();
}

function closeIn5sec( t ) {
    setTimeout( function() {
        t.close();
    }, 5000 );
}

function lat_lng_field( t, main, data ) {
    let i = t.parent().prev();
    i.val( data.coords.latitude + ' ' + data.coords.longitude ).trigger( 'change' );
}

function init_art_search( t, main, data ) {
    $(this).cms_articles_search( {
        container: data.attrs.container, 
        default: data.attrs.default,
        lat: data.attrs.lat, 
        lng: data.attrs.lng, 
        mlen: data.attrs.mlen, 
        range_changer: data.attrs.range_changer, 
        point_changer: data.attrs.point_changer 
    } );
}

function init_search( t, main, data ) {
    $(this).cms_search( {
        container: data.attrs.container, 
        default: data.attrs.default,
        value: data.attrs.value
    } );
}

function pl_around_set_city( input, ul, attrs, curel ) {
    let el = $( '<div class="citysel"><i class="fas fa-map-marker-alt mr10"></i> <i>' + attrs['name'] + '</i>' + ( attrs['parents'] !== '' ? ', ' + attrs['parents'] : '' ) + '<a href="#" onclick="this.parentElement.remove()"><i class="far fa-trash-alt"></i></a></div>' );
    el.append( '<input type="hidden" name="data[sscity-point]" value="' + attrs['point'] + '" />' )
    input.parent().before( el );
    input.removeClass( 'active' ).val( '' );
    ul.remove();
}

function form_set_community( input, ul, attrs, curel ) {
    let el = $( '<div class="namesel"><i>' + attrs['name'] + '</i>' + '<a href="#"><i class="far fa-trash-alt"></i></a></div>' );
    input.before( el );
    input.parent().prev( 'input[data-id="data[community_id]"]' ).val( attrs['id'] ).trigger( 'change' );
    input.removeClass( 'active' ).val( '' );
    ul.remove();
    input.cms_simple_call( {
        URL: utils.ajax_url + '&action=community-categories&bl=community_help',
        data: { community: attrs['id'] },
      }, function( data ) {
        let cat = input.closest( '.form_line' ).next( '.form_line' ).find( 'select[id="data[comm_cat]"]' );
        $.each( data, function( k, v ) {
            cat.append( '<option value="' + k + '">' + v + '</option>' );
        } );
    });
}

function pl_create_conversation( input, ul, attrs, curel ) {
    $( 'body' ).show_popup( { URL: utils.ajax_url + '&action=inbox&id=' + attrs['id'] } );
}

function pl_create_group_item( input, ul, attrs, curel ) {
    let exclude = typeof input.prop( 'exclude' ) == 'object' ? input.prop( 'exclude' ) : {};
    exclude[attrs['id']] = '';
    input.prop( 'exclude', exclude );
    curel.append( '<input type="hidden" name="data[u][]" value="' + attrs['id'] + '" />'  );
    ul.next( '.urs-list' ).append( curel );

    curel.on( 'click', function() {
        delete exclude[attrs['id']];
        input.prop( 'exclude', exclude );
        curel.remove();
    });
}

function call_before( t, ct ) {
    return ct.closest( '.search-c' ).prev( '.other_form' ).find( 'select, input').serialize();
}

function call_before_rp( t, form_data ) {
    form_data.append( 'data[text]', t.find( '.text-hero > [contenteditable="true"]' ).text() );
    return form_data;
}

function call_inbox_remove( t, main, attrs ) {
    inbox.removeConversation( attrs.conversation );
    if( typeof attrs.close_all !== 'undefined' ) {
        main.removePrevAll2();
    } else {
        main.close();
    }
}

function call_inbox_set_new( t, main, attrs ) {
    inbox.viewConversation( attrs.conversation )
    main.close();
}

function call_inbox_refresh_current() {
    inbox.reloadConversation();
}

function call_inbox_unread() {
    inbox.setUnread()
    .resetInbox();
}

function call_inbox_mute() {
    inbox.resetInbox();
}

function call_inbox_move_current( t, main, attrs ) {
    t.remove();
    inbox.moveCurrentEl();
}

function call_remove_current( t, main ) {
    main.removePrevAll2();
}

function call_close_main_popup( t, main ) {
    main.close();
}

function rich_field_options( t, main, attrs ) {
    let field = main.closest( '.form_line' ).find( 'input[name$="[options]"]' );
    field.val( decodeUnicode( attrs.options ) );
    main.close();
}

function remove_li_cp( t, main, attrs ) {
    if( main.hasClass( 'rmlt' ) ) {
        if( typeof main.close == 'function' ) {
            main.close();
        }
        main.closest( 'li' ).remove();
    }
}

function more_post_quote( t, main_obj, data ) {
    t.closest( '.pquote' ).before( decodeUnicode( data.markup ) );
    let li = t.parent( 'li' );
    let ul = li.parent();
    li.remove();
    if( !ul.find( '> li' ).length ) {
        ul.remove();
    }
}

function remove_li_parent( t, main_obj, attrs ) {
    main_obj.close();
    main_obj.closest( '.buttons' ).closest( 'li' ).remove();
}

function save_post_art( t, main_obj, data ) {
    let clarified = JSON.parse( decodeUnicode( data.fields ) );
    $.each( clarified, function( k, v ) {
        main_obj.append( '<input type="hidden" name="data[carticle][' + k + ']" value="' + v + '" />' );
    } );
    main_obj.close();
}

function coin_claimed( t, trigger, data ) {
    let li = t.closest( 'li' );
    if( data.success == false ) {
        li.remove();
        return ;
    }
    let span = t.find( 'span > span' );
    span.html( data.icon ).toggleClass( 'hler' );
    t.removeAttr( 'data-ajax' );
    setTimeout( function() {
        li.remove();
    }, 3000 );
}

// POPULATE TABLES

function cms_populate_table( t, data2 ) {
    // markup
    let table       = t.find( '.' + data2.class );
    let tbody       = table.find( '> .tbody' );
    let filters_f   = t.find( '.list_form' ),
    template        = table.find( '.template' ),
    temp            = table.find( '.temp' ),
    pag_markup      = $( '<div></div>' );
    table           .after( pag_markup );
    let next_p, prev_p, input_p;
    // content
    let content = template.html();
    // variables
    let the_page        = typeof data2.page != 'undefined' ? data2.page : 1,
    options             = typeof data2.options != 'undefined' ?  decodeUnicode( data2.options ) : '',
    check_pagination    = true,
    first               = true;
    tpages              = 0;
    
    if( typeof data2.hide_pagination != undefined && data2.hide_pagination ) {
        check_pagination = false;
    }

    let load_table = ( item, check_pagination ) => {
        table   .find( '.td,.msg' ).remove();
        temp    .removeClass( 'hidden' );
        let opts = { options: options, page: the_page, check_pagination: check_pagination };

        if( first ) {
            opts.firstLoad = true;
        }

        first = false;
        
        t.cms_simple_call( { URL: utils.ajax_url + '&action=populate-table&table=' + data2.table, 'data': opts }, function( data ) {
            temp.addClass( 'hidden' );

            if( typeof data.fallback != 'undefined' ) {

                tbody.append( data.fallback );
                pag_markup.html( '' );
                
            } else {

                $.each( data.list, ( k, v ) => {
                    tbody.append( '<div class="td">' + format( content, v ) + '</div>' );
                } );

                if( typeof data.pagination != 'undefined' ) {
                    pag_markup  .html( data.pagination );
                    next_p      = pag_markup.find( '[data-next]' );
                    prev_p      = pag_markup.find( '[data-prev]' );
                    input_p     = pag_markup.find( 'form > input[type="number"]' );
                    tpages      = parseInt( pag_markup.find( '[data-tpages]' ).data( 'tpages' ) );
                    
                    if( typeof input_p != 'undefined' ) {
                        input_p.val( the_page );
                    }
                }

                if( the_page <= 1 ) {
                    if( next_p )
                    next_p      .removeClass( 'hidden' );
                    if( prev_p )
                    prev_p      .addClass( 'hidden' );
                } else if( the_page >= tpages ) {
                    if( next_p )
                    next_p      .addClass( 'hidden' );
                    if( prev_p )
                    prev_p      .removeClass( 'hidden' );
                    the_page    = tpages;
                } else if( tpages > the_page ) {
                    if( next_p )
                    next_p      .removeClass( 'hidden' );
                    if( prev_p )
                    prev_p      .removeClass( 'hidden' );
                }

            }

            item.removeClass( 'disabled' );

            if( data.before )
            table.before( data.before );
        } );
    }

    pag_markup.on( 'click', 'a[data-prev]:not(.disabled)', function(e) {
        e.preventDefault();
        the_page -= 1;
        load_table( $(this), false );
    });

    pag_markup.on( 'click', 'a[data-next]:not(.disabled)', function(e) {
        e.preventDefault();
        the_page += 1;
        load_table( $(this), false );
    });

    pag_markup.on( 'submit', '.form > form:not(.disabled)', function(e) {
        e.preventDefault();
        let page = parseInt( $(this).find( 'input[type="number"]' ).val() );
        if( page != the_page ){
            the_page = page;
            load_table( $(this), false );
        }
    });

    load_table( table, check_pagination );

    filters_f.find( 'input:not([type="text"]),select:not(.disabled)' ).on( 'change', function() {
        temp.removeClass( 'hidden' );
        options     = filters_f.serialize();
        the_page    = 1;
        load_table( $(this), true );
    });

    let sint;

    filters_f.find( 'input[type="text"]:not([data-search])' ).on( 'keyup', function() {
        clearTimeout( sint );

        sint = setTimeout( function() {
            temp.removeClass( 'hidden' );
            options     = filters_f.serialize();
            the_page    = 1;
        
            load_table( $(this), true );
        }, 800 );
    });
}

// POPULATE BOXES

function cms_populate_boxes( t, data ) {
    let reqs        = {};
    let table       = t.find( '.' + data.class );
    let required    = table.find( '[data-req],[data-ref]' );
    let options     = typeof data.options != 'undefined' ? decodeUnicode( data.options ) : [];

    required.each( function( k, v ) {
        let ts = $(v);
        let req = ts.data( 'req' );
        let ref = ts.data( 'ref' );

        if( req !== undefined ) {
            reqs[req] = { id: req, el: ts, preloader: ts.html() };
        }
    });

    let reqs_vals   = Object.values( reqs );
    let count       = reqs_vals.length;
    let loaded      = [];

    if( count ) {
        let load_item = function( id ) {
            let nid     = id + 1;
            let str_id  = reqs_vals[id].id;
            if( !loaded.includes( str_id ) ) {
                t.cms_simple_call( { URL: utils.ajax_url + '&action=populate-boxes', data: { load: str_id, options: options } }, function( data ) {
                    $.each( data, function( k, v ) {
                        if( reqs[k] !== undefined ) {
                            reqs[k].el.html( v );
                            loaded.push( k );
                        }
                    } );
                    if( count > nid )
                    load_item( nid );
                } );
            } else {
                if( count > nid )
                load_item( nid );
            }
        }

        load_item( 0 );
    }
}

// POPULATE CHARTS

function populate_chart( t, data ) {
    google.charts.load( 'current', { 'packages': [ 'bar' ] } );
    let table   = $( '#' + data.table );
    let options = data.options != undefined ? decodeUnicode( data.options ) : [];

    table.addClass( 'wait' );

    google.charts.setOnLoadCallback( function() {
        let opt = {
            colors: [ '#00A170', '#FFB347', '#D31027', '#55B4B0', '#C3447A', '#B55A30', '#926AA6', '#D2386C', '#363945', '#E0B589', '#9A8B4F' ],
            chartArea: { 'backgroundColor': { 'fill': 'transparent', 'opacity': 100 } },
            backgroundColor: { fill: '#7e7c73', fillOpacity: 0.8 },
            legend: { position: 'none' },
            backgroundColor: 'transparent'
        };

        let chart = new google.charts.Bar( table[0] );

        let a = ( filters ) => {
            t.cms_simple_call( { URL: utils.ajax_url + '&action=populate-charts&chart=' + data.chart, 'data': { options: filters, data: data?.data } }, function( data ) {
                try {
                    let chart_data = google.visualization.arrayToDataTable( data );
                    chart.draw( chart_data, google.charts.Bar.convertOptions( opt ) );
                    table.removeClass( 'wait' );
                }
                catch( e ) { }
            });
        }

        a( options );

        if( data.class != undefined ) {
            let form = $( '#' + data.class );
            form.on( 'change', function() {
                table.addClass( 'wait' );
                a( form.serialize() );
            });
        }
    });
}

function populate_chart2( t, data, elem ) {
    google.charts.load( 'current', { 'packages': [ 'corechart' ] } );
    let table       = elem.find( data.table );
    let options     = data.options != undefined ? decodeUnicode( data.options ) : [];
    let defStyle    = data.default != undefined ? data.default : '3d';

    google.charts.setOnLoadCallback( function() { 
        let chart   = new google.visualization.PieChart( table[0] );
        let opts    = {
            chartArea: { left: 0, top: 10, bottom: 10, width: '100%', height: '100%' },
            height: 200,
            colors: [ '#00A170', '#FFB347', '#D31027', '#55B4B0', '#C3447A', '#B55A30', '#926AA6', '#D2386C', '#363945', '#E0B589', '#9A8B4F' ],
            legend: { position: 'right', alignment: 'center' },
            backgroundColor: 'transparent',
            sliceVisibilityThreshold: 0
        };

        let cd;

        function googleSetStyle( style, first ) {
            if( !first && defStyle == style ) return ;
            switch( style ) {
                case 'pie':
                    t.removeClass( 'txt' );
                    opts.is3D       = false;
                    opts.pieHole    = 0;
                    chart.draw( cd, opts );
                break;

                case 'donut':
                    t.removeClass( 'txt' );
                    opts.is3D       = false;
                    opts.pieHole    = 0.4;
                    chart.draw( cd, opts );
                break;

                case '3d':
                    t.removeClass( 'txt' );
                    opts.is3D       = true;
                    chart.draw( cd, opts );
                break;

                case 'text':
                    t.addClass( 'txt' );
                break;
            }

            defStyle = style;
        }

        let a = ( filters ) => {
            try {
                cd  = google.visualization.arrayToDataTable( data.data );
                googleSetStyle( defStyle, true );

                t.find( '[data-types] a[data-type]' ).on( 'click', function(e) {
                    e.preventDefault();
                    let t = $(this).data( 'type' );
                    googleSetStyle( t, false );
                });

            }
            catch( e ) {}
        }

        a( options );
    } );
}

function populate_chart3( t, data ) {
    google.charts.load( 'current', { 'packages': [ 'corechart' ] } );
    let table       = $( data.table );
    let options     = data.options != undefined ? decodeUnicode( data.options ) : [];

    google.charts.setOnLoadCallback( function() {
        let chart   = new google.visualization.ColumnChart( table[0] );
        let opts    = {
            chartArea: {  width: '100%' },
            height: 200,
            legend: { position: 'top', alignment: 'center' },
            colors: ['#00A170', '#FFB347', '#D31027', '#55B4B0', '#C3447A', '#B55A30', '#926AA6', '#D2386C', '#363945', '#E0B589', '#9A8B4F' ],
            backgroundColor: 'transparent'
        };

        let a = () => {
            try {
                let cd  = google.visualization.arrayToDataTable( data.data );
                chart.draw( cd, opts );
            }
            catch( e ) {}
        }

        a( options );
    });
}

function populate_chart4( t, data ) {
    google.charts.load( 'current', { 'packages': [ 'corechart' ] } );
    let table       = $( data.table );
    let options     = data.options != undefined ? decodeUnicode( data.options ) : [];

    google.charts.load('current', {
        'packages': [ 'geochart' ],
    });

    google.charts.setOnLoadCallback( function() {
        let opts    = {
            colors: [ '#464033' ],
            backgroundColor: 'transparent'
        };

        let a = () => {
            try {
                let chart   = new google.visualization.GeoChart( table[0] );
                let cd      = google.visualization.arrayToDataTable( data.data );
                chart.draw( cd, opts );
            }

            catch( e ) {}
        }

        a( options );
    });
}

// Utils

function format( str, args ) {
    return str.replace( /{([^}]*)}/g, function( match, key ) {
      return ( typeof args[key] !== "undefined" ? args[key] : match );
    });
}

function openLink( t, data ) {
    window.location = data.URL;
}

// MARKUP HELPERS

function markup_switch_markup( t, data ) {
    t.replaceWith( decodeUnicode( data.new_markup ) );
}

function markup_activate_theme( t ) {
    t.closest( '.table' ).find( '.td .btnset > li.activate' ).removeClass( 'hidden' );
    t.closest( 'li' ).addClass( 'hidden' );
}

function markup_delete_table_td( t ) {
    t.closest( '.td' ).slideUp(function() {
        $(this).remove();
    });
}

function markup_delete_closest_fline( t ) {
    t.closest( '.form_line' ).slideUp(function() {
        $(this).remove();
    });
}

function markup_delete_this( t ) {
    t.remove();
}

function markup_delete_form_line( t ) {
    t.closest( '.form_line' ).slideUp(function() {
        $(this).remove();
    });
}

function crlink_collector( t, data ) {
    t.prev( '.msg' ).remove();
    t.find( 'textarea[name="data[link]"]' ).val( data.link );
}

function genkey_collector( t, data ) {
    t.closest( '.form_line' ).prev( '.form_line' ).find( 'input[name="data[enckey]"]' ).val( data.key );
}

function cms_set_form_values( t, options ) {
    options.data = t.closest( 'form' ).find( '[name="data[advanced]"]' ).val();
    return options;
}

function popup_results_filter( t, popup, data ) {
    let form    = t.closest( 'form' );
    let advc    = form.find( '[name="data[advanced]"]' );
    advc.val( data.data )
    advc.trigger( 'change' );
}

function cms_set_questions_form_values( t, options ) {
    let fl  = t.closest( '.form_line' );
    let qs  = fl.prev( '.vqs' );
    let l   = {};
    qs.find( '[data-qid]' ).each( function() {
        let q = $(this);
        l[q.data( 'qid')] = $(this);
    });
    t.prop( 'qlist', l );
    options.data = Object.keys( l );
    return options;
}

function popup_questions_filter( t, popup, data ) {
    let fl  = t.closest( '.form_line' );
    let qs  = fl.prev( '.vqs' );
    let els = JSON.parse( decodeUnicode( data.data ) );
    let ql  = t.prop( 'qlist' );
    $.each( els, function( k, v ) {
        if( ql && k in ql ) {
            delete ql[k];
            // do nothing
        } else if( !( k in ql ) ) {
            qs.append( v );
            delete ql[k];
        }
    });
    $.each( ql, function( k, v ) {
        v.remove();
    });
}

function change_menu( t, data ) {
    if( nav && nav.prop( 'name' ) == data.nav ) {
        return ;
    }
    nav = $( decodeUnicode( data.el ) );
    nav .prop( 'name', data.nav );
    nav_el[data.nav] = nav;
    var navContainer = $( '.nav-container');
    navContainer.prepend( nav );
}

function result_change_labels( t, data ) {
    let res     = data.data.result;
    let popup   = t.prop( 'popupContent' );
    let curr    = {};
    let list    = t.find( '.llst' );

    list.find( '> .sav' ).each( function() {
        curr[$(this).attr( 'id' ).replace( /\D/g, '' )] = $(this);
    });

    popup.find( 'input' ).on( 'change', function() {
        let ts  = $(this);
        let nam = ts.attr( 'name' );
        let val = ts.val();
        let col = ts.data( 'color' );
        let chd = ts.is( ':checked' );
        let sdt = {};

        if( chd ) {
            if( curr[val] == undefined ) {
                curr[val] = $( '<div class="sav" id="lab-' + val + '"><i class="avt-' + col + '"></i></div>' );
                list.prepend( curr[val] );
            }
            sdt[nam] = 1;
        } else {
            if( curr[val] != undefined ) {
                curr[val].remove();
                delete curr[val];
            }
            sdt[nam] = 0;
        }

        t.cms_simple_call( { URL: utils.ajax_url + '&action=manage-result2&action2=add-label&result=' + res, data: sdt } );
    });
}

function close_popup( t ) {
    t.closest( '.popup' ).prop( 'popup' ).sp_close();
}

function init_survey_chart2( t, data, response ) {
    let elem = $( data.container );
    load_survey_charts2( data, elem );
}

function load_survey_charts2( data, elem ) {
    $.each( data.placeholders, function( k, v ) {
        if( typeof v == 'object' ) {
            load_survey_charts2( { data, ...{ placeholders: v, data: data.data[k] } }, elem );
        } else {
            let el  = elem.find(v);
            let q   = el.closest( '.q' );
            q.removeClass( 'l' );
            populate_chart2( q, { data, ...{ table: v, data: data.data[k] } }, elem );
        }
    });
}

// INITIATORS
function init_team_chat( t, data ) {
    t.cms_chat( { el: data.el, msgs: data.msgs, write: data.write } );
}

function initSort() {
    $( '.sortable' ).sortable( {
        connectWith: '.sortable',
        handle: '> div:first',
        beforeStop: function( e, ui ) {
            var item        = $( ui.item[0] );
            var parent      = item.parents( 'li:first' );
            var parentId    = parent.length ? parent.attr( 'id' ) : '';
            item.find( 'input[name$="[parent_id]"]' ).eq(0).val( parentId );
        }
    });

    $( '[name$="[label]"]' ).on( 'keyup', function() {
        var t = $(this);
        var l = t.closest( 'li' ).find( ' > div > span' );
        l.text( t.val() );
    });
}

function initPageSort() {
    $( '.sortable' ).sortable( {
        handle: 'a.move'
    });
}

function init_add_item_menu( t, data ) {
    var menu    = $( '#menu' );
    var markup  = decodeUnicode( data.markup );
    var el      = $( markup );

    menu.prepend( el );
    el.find( '> div:First' ).trigger( 'click' );
    el.find( 'input[name$="[label]"]' ).trigger( 'focus' );
    close_popup( t );

    initSort();
}

function init_survey_dashboard( t, data ) {
    if( sdashboard != undefined ) {
        sdashboard = $( '.survey_dashboard' ).cms_survey_dashboard();
        sdashboard.init( data.survey );
    } else {
        sdashboard = $( '.survey_dashboard' ).cms_survey_dashboard();
        sdashboard.init( data.survey );
    }
}

function init_survey_result( t, data ) {
    let col = $( '.report-ph:first' );
    sdashboard.addResults( col, data.report );
}

function init_survey_new_report( t, data ) {
    sdashboard.newReport( data.report );
}

function init_shop( t, data ) {
    shop = $.shop( data );
}


function shop_item_add_markup( t, data ) {
    shop.add( data );
}

function shop_item_remove_markup( t, data ) {
    shop.remove( data );
}

$.fn.cms_survey_dashboard = function() {
    let t = this, surveyId, container, report, dashboard;

    t.init = function( survey ) {
        surveyId    = survey;
        container   = $( '.survey_dashboard' );
        col1        = container.find( '> .report' );
        col2        = container.find( '> .dashboard' );
        col1        .prop( 'posInReport', 1 );
        col2        .prop( 'posInReport', 2 );
    }

    t.getContainer = function() {
        return container;
    }

    t.addResults = function( col, reportId ) {
        col.addClass( 'fadingOut' );
        setTimeout( function() {
            t.cms_simple_call( { URL: utils.ajax_url + '&action=survey-rparts', data: { action: 'r_reportView', 'reportId': reportId, 'survey': surveyId } },
            function( data ) {
                col
                .prop( 'reportId', reportId )
                .addClass( 'report-' + reportId )
                .removeClass( 'report-ph' )
                .html( data.content )
                .removeClass( 'fadingOut' );

                if( col.prop( 'posInReport' ) == 1 && !col2.prop( 'reportId' ) ) {
                    col2.addClass( 'fadingOut' );
                    t.cms_simple_call( { URL: utils.ajax_url + '&action=survey-rparts', data: { action: 'r_newReport', 'survey': surveyId, 'pos': 2 } },
                    function( data ) {
                        setTimeout( function() {
                            col2
                            .addClass( 'report-ph' )
                            .html( data.content )
                            .removeClass( 'fadingOut' );
                        }, 200 );
                    } );
                }
            } );
        }, 200 );
    }

    t.newReport = function( oldReport ) {
        let oldClass    = 'report-' + oldReport;
        let cReport     = $( '.' + oldClass );
        if( !cReport ) return ;
        cReport.addClass( 'fadingOut' );
        t.cms_simple_call( { URL: utils.ajax_url + '&action=survey-rparts', data: { action: 'r_newReport', 'survey': surveyId, 'pos': cReport.prop( 'posInReport' ) } },
        function( data ) {
            setTimeout( function() {
                cReport
                .addClass( 'report-ph' )
                .removeClass( 'fadingOut' )
                .html( data.content )
                .removeClass( oldClass );
            }, 200 );
        } );
    }
    
    return t;
};
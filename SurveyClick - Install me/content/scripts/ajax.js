'use strict';

(function($) {

    $.fn.cms_response_types = function( response ) {
        let t = this;

        if( typeof response == 'object' ) {
            if( typeof response.show_popup !== 'undefined' ) {
                t.show_popup( response.show_popup );
            }

            if( typeof response.redirect !== 'undefined' ) {
                setTimeout( function() {
                    window.location = response.redirect;
                }, ( typeof response.timeout !== 'undefined' ? response.timeout : 2000 ) );
            }

            if( typeof response.reload !== 'undefined' ) {
                setTimeout( function() {
                    window.location = window.location;
                }, ( typeof response.timeout !== 'undefined' ? response.timeout : 2000 ) );
            }
        
            if( typeof response.call !== 'undefined' ) {
                t.cms_simple_call( response.call );  
            }

            if( typeof response.callback !== 'undefined' ) {
                let cb = JSON.parse( response.callback );
                window[cb.callback]( t, cb, response );
            }

            if( typeof response.callbacks !== 'undefined' ) {
                $.each( response.callbacks, function( k, v ) {
                    let cb = JSON.parse( v );
                    window[cb.callback]( t, cb, response );
                });
            }

            if( typeof response.load_scripts !== 'undefined' ) {
                $.each( response.load_scripts, function( k, v ) {
                    if( !scripts.includes( k ) ) {
                        $.getScript( k, function() {
                            scripts = scripts.concat( [ k ] );
                            let cb = JSON.parse( v );
                            window[cb.callback]( t, cb, response );
                        } );
                    } else {
                        let cb = JSON.parse( v );
                        window[cb.callback]( t, cb, response );
                    }
                } );
            }

            if( typeof response.load_styles !== 'undefined' ) {
                $.each( response.load_styles, function( k, v ) {
                    if( !styles.includes( v ) ) {
                        styles = styles.concat( [ v ] );
                        $( 'head' ).append( $( '<link rel="stylesheet" type="text/css" />' ).attr( 'href', v ) );
                    }
                } );
            }

            if( typeof response.href !== 'undefined' ) {
                window.history.pushState( '', '', response.href );
            }

            if( typeof response.goto !== 'undefined' ) {
                goto.go2( t, response?.goto.url, response.goto.path, response.goto.options, '' );
            }

            if( typeof response.menu_link !== 'undefined' ) {
                let menu    = typeof response.menu_selected != 'undefined' ? response.menu_selected : 'main';

                if( typeof nav_el[menu] == 'undefined' && typeof response.menu_selected != 'undefined' ) {
                    nav_el[menu] = $( '.nav.' + response.menu );
                }

                let new_el  = nav_el[menu].find( '#l-' + response.menu_link );
                if( nav_current_el.length ) {
                    nav_current_el.removeClass( 'active current' );
                    nav_current_el.find( '.active' ).removeClass( 'active current' );
                    nav_current_el.closest( 'li.dd.active' ).removeClass( 'active current' );
                }

                if( new_el.length ) {
                    new_el.addClass( 'active current' );
                    new_el.parents( 'li.dd' ).addClass( 'active current' );
                    nav_current_el = new_el;
                }
            }
        }
    };
    
    $.fn.cms_simple_call = function( options, response, error, before ) {
        let option = $.extend( {
            type:       'POST',
            dataType:   'json',
            URL:        '',
            data:       [],
            content:    '',
            onFail:     function() {
                return t.oopsFail();
            }
        }, options );

        let t = this,
        c_response;

        if ( $.isFunction( before ) ) {
            before.call( this );
        }

        t.addClass( 'waiting' );

        let ajaxOpt = {
            type:     option.type,
            url:      option.URL,
            dataType: option.dataType,
            data:     option.data
        };

        if( option.data instanceof FormData ) {
            ajaxOpt.cache       = false,
            ajaxOpt.processData = false;
            ajaxOpt.contentType = false;
        }

        let request = $.ajax( ajaxOpt );
        let rstatus;

        request.done( function( data ) {
            rstatus = 'success';

            if( $.isFunction( response ) ) {
                c_response = response.call( this, data );
            }
        
            if( option.dataType == 'json' ) {
                t.cms_response_types( data );
            }

            t.removeClass( 'waiting' );
        });

        request.fail( function( request, status, thrown ) {
            rstatus = status;
            alert(request.responseText);
            if( $.isFunction( option.onFail ) ) {
                option.onFail.call( this, request.responseText );
            }

            if( $.isFunction( response ) ) {
                c_response = response.call( this, { status: 'error', msg: status } );
            }

            if( $.isFunction( error ) ) {
                error.call( this, request, status, thrown );
            }
        });

        t.oopsFail = function( text, status ) {
            // Handle the error
            t.cms_simple_call( { URL: utils.ajax_url + '&action=empty-call', onFail: false, data: { text: text, status: status } } );
        }

        t.getResponse = function() {
            return c_response;
        }

        t.getStatus = function() {
            return rstatus;
        }

        t.changeEl = function( el ) {
            t = el;
            return ;
        }

        return t;
    };    

  $.fn.cms_call = function( options, response, error, before ) {
    let option = $.extend( {
        type:     'POST',
        data_type:'json',
        URL:      false,
        blobs:    {},
    }, options );

    let t = this;

    t.add_file = function( name, value, filename ) {
      option.blobs[name] = { 'value': value, 'filename': filename };
      return t;
    }

    t.delete_file = function( name ) {
      delete option.blobs[name];
      return t;
    }

    t.init_call = function() {
      let form_data = new FormData( t[0] );

      if( $.isFunction( before ) ) {
        before.call( t );
      }

      let btn = t.find( 'button' );
  
      showPreloader();

      function showMsg( classAttr, text ) {
        t.prev( '.msg' ).remove();
        $( '<div class="msg ' + classAttr + '">' + text + '</div>' );
        t.before( $( '<div class="msg ' + classAttr + '">' + text + '</div>' ) );
      }

      function showPreloader() {
        btn.prop( 'disabled', true );
      }

      function hidePreloader() {
        btn.prop( 'disabled', false );
      }

      if( option.URL === false ) {
        option.URL = this.data( 'ajax' );
      }

      let files = t.find( 'input[type="file"]' );

      if( files.length ) {
          files.each( function() {
            form_data.append( $(this).attr( 'name' ), $(this) );
          });
      }

      let afiles = t.prop( 'files' );

      if( afiles ) {
          $.each( afiles, function( name, opt ) {
            option.blobs[name] = { 'value': opt.value, 'filename': opt.filename };
          });
      }

      if( Object.keys( option.blobs ).length ) {
          $.each( option.blobs, function( name, opt ) {
            form_data.append( name, opt.value, opt.filename );
          });
      }

      t.cms_simple_call( {
          type:     option.type,
          URL:      option.URL,
          dataType: option.data_type,
          data:     ( typeof option.call_before !== 'undefined' ? window[option.call_before]( t, form_data ) : form_data ),
      }, function( data ) {
          if ( $.isFunction( response ) ) {
            response.call( this, data );
          }

          if( typeof data.msg !== 'undefined' ) {
            showMsg( data.status, data.msg );
          }

          hidePreloader();
      }, function( jqXHR, status, thrown ) {
          if ( $.isFunction( error ) ) {
            error.call( jqXHR, status, thrown );
          }

          hidePreloader();
      } );

      return t;
    }

    return t;
  };

  $.fn.show_popup = function( options, response, error, before ) {
    let option = $.extend( {
        URL:              null,
        type:             'POST',
        action:           '',
        options:          [],
        data:             [],
        classes:          [],
        title:            null,
        title2:           null,
        content:          null,
        remove_prev:      false,
        remove_prev_all:  false
    }, options );

    let t     = this;
    let doc   = [];
    let the_URL;
    let html  = $( '<div class="popup"></div>' );
    let defm  = `<div class="popup-main">
    <div class="content-container">
        <a href="#" class="close"><i class="fas fa-times"></i></a>
        <div class="headline-msg">
            <div class="pop-container">
                <div class="preloader">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
    </div>`;
    let main  = $( defm );

    html.append( main );
    html.prop( 'popup', t );
    t.prop( 'popupContent', html );

    $( 'body' ).addClass( 'popupv' ).prepend( html );

    t.addClass( 'active' );

    if( option.content != null ) {

        html.find( '.pop-container' ).html( option.content );

        if( option.title ) {
            html.find( '.content-container' ).prepend( '<div class="headline"><span class="elp">' + option.title + '</span></div>' );
        }

        if( option.title2 ) {
            html.find( '.headline' ).append( '<span>' + option.title2 + '</span>' );
        }

        if( option.remove_prev ) {
            let next = html.next( '.popup' );
            if( next.length ) {
                next.prop( 'popup' ).sp_close();
            }
        }

        if( option.remove_prev_all ) {
            let next = html.nextAll( '.popup' );
            if( next.length ) {
                next.each( function() {
                    $(this).prop( 'popup' ).sp_close();
                } );
            }
        }

    } else {

        if( option.URL ) {
            the_URL = option.URL;
        } else {
            the_URL = utils.ajax_url + '&action=' + option.action;
        }

        load_data();

    }

    function load_data() {
        let call = t.cms_simple_call( {
            URL: the_URL, data: { data: option.data, options: option.options }
        }, function( data ) {
            t.removeClass( 'waiting' );

            if ( $.isFunction( response ) ) {
                response.call( this, data );
            }

            if( call.getStatus() != 'success' ) {
                return t.sp_close();
            }

            if( data.classes ) {
                option.classes = data.classes;
            }

            if( option.classes && option.classes.length ) {
            main.addClass( option.classes.join( ' ' ) );
            }

            if( data.title ) {
                html.find( '.content-container' ).prepend( '<div class="headline"><span class="elp">' + data.title + '</span></div>' );
            }

            if( data.title2 ) {
                html.find( '.headline' ).append( '<span>' + data.title2 + '</span>' );
            }

            html.find( '.pop-container' ).html( data.content );

            if( data.remove_prev ) {
                option.remove_prev = data.remove_prev;
            }

            if( data.remove_prev_all ) {
                option.remove_prev_all = data.remove_prev_all;
            }

            if( option.remove_prev ) {
                let next = html.next( '.popup' );
                if( next.length ) {
                    next.prop( 'popup' ).sp_close();
                }
            }

            if( option.remove_prev_all ) {
                let next = html.nextAll( '.popup' );
                if( next.length ) {
                    next.each( function() {
                        $(this).prop( 'popup' ).sp_close();
                    } );
                }
            }
        }, error, before );
    }

    html.find( '.close' ).on( 'click', function(e) {
        e.preventDefault();
        t.sp_close();
    });

    html.on( 'click', function(e) {
        if( $(e.target).hasClass( 'popup' ) || $(e.target).hasClass( 'popup-main' ) ) {
            t.sp_close();
        }
    });

    t.reload_popup = function() {
        main = $( defm );
        html.html( main );
        load_data();
    }

    t.call_on_close = function( call, data ) {
        doc.push( [ call, data ] );
    }

    t.sp_close = function() {
        html.addClass( 'closing' );
        t.removeClass( 'active' );
        t.parent().removeClass( 'active' );
        setTimeout( function() {
            let len = $( 'body' ).find( '> .popup' ).not( '.closing' ).length;

            if( doc.length ) {
                $.each( [ ...new Set( doc ) ], function( id, fct ) {
                    window[fct[0]]( t, html, fct[1] );
                });
            }

            html.remove();
            if( len < 1 ) {
                $( 'body' ).removeClass( 'popupv' );
            }
        }, 300 );
    }

    return t;
};

}( jQuery ));

$(function() {

  $( document ).on( 'submit', 'form[data-ajax]:not(.waiting)', function(e) {
    e.preventDefault();
    let t = $(this);
    form_ajax_trigger( t, t.data( 'before' ) );
  });

  $( document ).on( 'click', '[data-ajax]:not(form):not(.waiting)', function(e) {
    e.preventDefault();
    let t = $(this);
    let p = t.data( 'params' );
    t.cms_simple_call( { URL: utils.ajax_url + '&action=' + t.data( 'ajax' ) + ( typeof p != 'undefined' ? '&' + $.param( t.data( 'params' ) ) : '' ), data: t.data( 'data' ) } );
  });

  $( document ).on( 'mouseenter', '.on_hover', function() {
    let t = $(this);
    t.removeClass( 'on_hover' );
    t.find( '[data-ajax]' ).each( function() {
      $(this).cms_simple_call( { URL: t.data( 'ajax') } );
    });
  });

  $( document ).on( 'click', '[data-popup]:not(form):not(.waiting):not(.disabled)', function(e) {
    e.preventDefault();
    popup_trigger( $(this) );
  });

});

function form_ajax_trigger( cur_el, before ) {
  cur_el.cms_call({
    call_before: before
  },
  function( response ) {
  }).init_call();
}

function popup_trigger( cur_el, data ) {
  let cb      = cur_el.data( 'callback' );
  let cbf     = cur_el.data( 'before' );
  let callback= false;
  let opt     = {};
  opt.action  = cur_el.data( 'popup' );
  opt.options = cur_el.data( 'options' );

  if( typeof data !== 'undefined' ) { 
    opt.data  = data;
  } else {
    let d     = cur_el.data( 'data' );
    opt.data  = d;
  }

  if( typeof cbf !== 'undefined' ) {
    opt = window[cbf]( cur_el, opt )
  }

  if( typeof cb !== 'undefined' ) {
    callback  = window[cb];
  }

  cur_el.show_popup( opt, callback );
}

function decodeUnicode( str ) {
  if( str == undefined ) {
    return '';
  }
  return decodeURIComponent( atob( str ).split( '' ).map(function (c) {
      return '%' + ( '00' + c.charCodeAt(0).toString( 16 ) ).slice( -2 );
  } ).join( '' ) );
}

function markup_replace_closest( t, data ) {
  t.closest( data.el ).html( data.text );
}

function markup_changer( t, data ) {
  t.replaceWith( decodeUnicode( data.html ) );
}

function disable_element( t, data ) {
  t.addClass( 'disabled' );
}

function remove_element( t, data ) {
  t.remove();
}

function popup_reload_prev( t, popup ) {
  let pp = popup.next( '.popup' );
  if( pp.length ) {
    pp.prop( 'popup' ).reload_popup();
  }
}

function popup_close_from_form( t ) {
  let popup = t.closest( '.popup' ).prop( 'popup' );
  popup.sp_close();
}

function popup_on_close_from_form( t, data ) {
  let popup = t.closest( '.popup' ).prop( 'popup' );
  if( typeof data.functions !== 'undefined' ) {
    if( Array.isArray( data.functions ) ) {
      $.each( data.functions, function( id, fct ) {
        popup.call_on_close( fct, data );
      });
    } else {
      popup.call_on_close( data.functions, data );
    }
  }
}
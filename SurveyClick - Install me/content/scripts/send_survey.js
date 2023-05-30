'use strict';

(function($) {

  $.fn.cms_send_survey = function( options ) {
    let t     = this;
    let s_btn = t.find( '> article.bt > button' ),
    qs        = {},
    er        = {};

    let option = $.extend( {
    }, options );

    t.find( '> article[id]' ).each( function() {
      let el_id = $(this).attr( 'id' );
      let err   = $(this).find( '> .msg' );
      qs[el_id] = $(this);
      if( err ) {
        er[el_id] = err;
      }
    } );

    t.on( 'submit', function(e) {
      e.preventDefault();

      $.each( er, function( id, el ) {
        el.remove();
      });

      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=send-survey',
        data: new FormData( t[0] )
      }, function( data ) {
        if( typeof data.errors != 'undefined' && Object.keys( data.errors ).length ) {
          $.each( data.errors, function( id, err ) {
            er[id] = $( '<div class="msg error"><span class="icon"><i class="fas fa-exclamation"></i></span><div>' + err + '</div></div>' );
            qs[id].prepend( er[id] );
          });
        } else if( typeof data.alert != 'undefined' ) {
          $( 'body' ).show_popup( { content: data.alert } );
        } else {
          window.location.href = window.location.href;
        }

        if( data.rename_button ) {
          s_btn.text( data.rename_button );
        }

        if( $.isFunction( option.response ) ) {
            option.response.call( this, data );
        }
      } );
    });

    return t;
  };

}( jQuery ));
'use strict';

(function($) {

  $.fn.cms_fform = function( cur_el ) {
    let t     = this;
    let tr    = t.data( 'fform' );
    let vals  = [];

    function any_condition( conditions ) {
      let yes = false;
      $.each( conditions, function( k, v ) {
        switch( v[0] ) {
          case '=':
            if( v[2] == vals[v[1]] ) {
              yes = true;
              return ;
            }
          break;

          case '!=':
            if( v[2] != vals[v[1]] ) {
              yes = true;
              return ;
            }
          break;

          case '>':
            if( vals[v[1]] > v[2] ) {
              yes = true;
              return ;
            }
          break;

          case '<':
            if( vals[v[1]] < v[2] ) {
              yes = true;
              return ;
            }
          break;

          case 'IN':
            if( $.inArray( vals[v[1]], v[2] ) > -1 ) {
              yes = true;
              return ;
            }
          break;

          case 'NOT_IN':
            if( $.inArray( vals[v[1]], v[2] ) == -1 ) {
              yes = true;
              return ;
            }
          break;
        }
      });
      return yes;
    }

    function check( target ) {
      let item = t.find( '[data-id="ff-' + target + '"]' );
      let show = true;

      $.each( tr.deps[target], function( k, v ) {
        switch( v[0] ) {
          case '=':
            if( v[2] != vals[v[1]] ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case '!=':
            if( v[2] == vals[v[1]] ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case '>':
            if( vals[v[1]] <= v[2] ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case '<':
            if( vals[v[1]] >= v[2] ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'IN':
            if( $.inArray( vals[v[1]], v[2] ) == -1 ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'NOT_IN':
            if( $.inArray( vals[v[1]], v[2] ) > -1 ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'HAS':
            $.each( v[2], function( kv, vl ) {
              if( Object.keys( vals[v[1]] ).indexOf( vl ) == -1 ) {
                  item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
                  show = false;
                  return ;
              }
            });
          break;

          case 'HAS_NOT':
            $.each( v[2], function( kv, vl ) {
              if( Object.keys( vals[v[1]] ).indexOf( vl ) > -1 ) {
                  item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
                  show = false;
                  return ;
              }
            });
          break;

          case 'EMPTY':
            if( ( Array.isArray( vals[v[1]] ) && Object.values( vals[v[1]] ).length != 0 ) || vals[v[1]] != '' ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'NOT_EMPTY':
            if( ( Array.isArray( vals[v[1]] ) && Object.values( vals[v[1]] ).length == 0 ) || vals[v[1]] == '' ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'ANY':
            if( !any_condition( v[1] ) ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;
        }
      });

      if( show ) {
        item.addClass( 'visible' ).removeClass( 'hidden' ).show();
      }
    }

    function changed( target ) {
      $.each( target, function( k, trg ) {
        check( trg );
      });
    }

    function follow_change( item, item_id, target, len ) {
      item.on( 'input', function() {
        let ti = $(this);
        if( ti.is( ':radio' ) ) {
          if( ti.is( ':checked' ) ) {
            vals[item_id] = ti.val();
          }
        } else if( len > 1 ) {
          let nid = ti.attr( 'name' ).match( /\[(\w+)\]$/ );
          if( ti.is( ':checkbox' ) ) {
            if( ti.is( ':checked' ) ) {
              vals[item_id][nid[1]] = ti.val();
            } else {
              delete vals[item_id][nid[1]];
            }
          } else {
            vals[item_id][nid[1]] = ti.val();
          }
        } else {
          let val = ti.is( ':checkbox' ) ? ( ti.is( ':checked' ) ? 1 : 0 ) : ti.val();
          vals[item_id] = val;
        }
        changed( target );
      });
    }

    $.each( tr.targets, function( id, val ) {
      let ct = t.find( '[name^="' + id + '"]' );
      let len= ct.length;
      if( ct.is( ':radio' ) ) {
        len = 1;
        vals[id] = t.find( '[name^="' + id + '"]:checked' ).val();
      } else if( len > 1 ) {
        vals[id] = [];
        $.each( ct, function() {
          let nt = $(this);
          let nid = nt.attr( 'name' ).match( /\[(\w+)\]$/ );
          if( nt.is( ':checkbox' ) ) {
            if( nt.is( ':checked' ) )
            vals[id][nid[1]] = 1;
          } else {
            vals[id][nid[1]] = ct.val();
          }
        });
      } else {
        vals[id] = ct.is( ':checkbox' ) ? ( ct.is( ':checked' ) ? 1 : 0 ) : ct.val();
      }

      follow_change( ct, id, val.target, len );
      cur_el.trigger( 'input' );
    });

    t.on( 'input', '.form_dropdown .title input', function() {
      $(this).closest( '.form_dropdown' ).find( '> div > span' ).text( $(this).val() );
    });

    return t;
  };

}( jQuery ));
'use strict';

(function ( $ ) {

  var on_change_events;

  $.fn.cms_rich_form_fields = function( options ) {
    let option = $.extend( {
      options_action: 'rich-field-edit-post-options',
    }, options );

    let t = this,
    types = [];
    let c = t.find( '.add_new' ),
    i = t.find( '.form_dline' ).length,
    form = t.cms_call({ 
      main_el: t
    },
    function( response ) {
      if( response.status === 'error' ) {
        t.parents( '.form-box' ).cms_animation();
      }
    });

    t.on( 'submit', function(e) {
      e.preventDefault();
      e.stopPropagation();
      form.init_call();
    });

    function cb_type_img( html ) {
      let input = $( '<input type="file" name="image" accept="image/*" />' ).click();
      input.on( 'change', function() {
        let fileName = this.files[0].name.replace( /[^\w\d]/gi, '' );
        form.add_file( fileName, this.files[0] );
        let newId = i++;
        html = html.replace( /#IMAGE#/gi, '<img src="' + window.URL.createObjectURL( this.files[0] ) + '" /><input type="hidden" name="data[text][' + newId + '][image][URL]" value="' + fileName + '" /><input type="hidden" name="data[text][' + newId + '][image][options]" value="[]" />' );
        html = html.replace( /#NEW#/gi, newId );
        let el = $( html );
        el.prop( 'elId', newId );
        el.prop( 'elFile', fileName );
        c.before( el );
      });
    }

    function append_type( type, html ) {
      switch( type ) {
        case 'img':
          cb_type_img( html );
        break;

        default:
        c.before( html.replace( /#NEW#/gi, ++i ) );
      }
    }

    t.on( 'click', 'a[data-rff="up"]', function(e) {
      e.preventDefault();
      let ts = $(this);
      let cl = ts.closest( '.form_line' );
      cl.prev( '.form_dline' ).before( cl );
    });

    t.on( 'click', 'a[data-rff="down"]', function(e) {
      e.preventDefault();
      let ts = $(this);
      let cl = ts.closest( '.form_line' );
      cl.next( '.form_dline' ).after( cl );
    });

    t.on( 'click', 'a[data-rff="remove"]', function(e) {
      e.preventDefault();
      let ts = $(this);
      let cl = ts.closest( '.form_line' );
      cl.remove();
    });

    t.on( 'click', 'a[data-rff="options"]', function(e) {
      e.preventDefault();
      let ts = $(this);
      let options = ts.closest( '.form_line' ).find( 'input[name$="[options]"]' );
      ts.show_popup( { URL: utils.ajax_url + '&action=' + option.options_action, data: { type: ts.data( 'type' ), options: options.val() } } );
    });

    t.on( 'click', 'a[data-rff="upload-img"]', function(e) {
      e.preventDefault();
      let ts  = $(this);
      let cl  = ts.closest( '.form_line' );
      let id  = cl.prop( 'elId' ),
      file    = cl.prop( 'elFile' );
      if( typeof id == 'undefined' ) {
        let newId = cl.data( 'id' ).match( /\[([0-9]+)\]$/ );
        if( newId ) {
          id = newId[1];
        }
      }
      let input = $( '<input type="file" name="image" accept="image/*" />' ).trigger( 'click' );
      input.on( 'change', function() {
        if( typeof file !== 'undefined') {
          form.delete_file( file );
        }
        let fileName = this.files[0].name.replace( /[^\w\d]/gi, '' );
        form.add_file( fileName, this.files[0] );
        cl.prop( 'elFile', fileName );
        cl.find( '.upim' ).html( '<img src="' + window.URL.createObjectURL( this.files[0] ) + '" /><input type="hidden" name="data[text][' + id + '][image][URL]" value="' + fileName + '" /><input type="hidden" name="data[text][' + id + '][image][options]" value="[]" />' );
      });
    });

    t.on( 'click', 'a[data-rff="remove-img"]', function(e) {
      e.preventDefault();
      let ts  = $(this);
      let cl  = ts.closest( '.form_line' );
      let file= cl.prop( 'elFile' );
      form.delete_file( file );
      cl.remove();
    });

    c.find( 'a.remove' ).on( 'click', function(e) {
      e.preventDefault();
      c.toggleClass( 'active' );
    });

    c.find( 'a[data-type]' ).on( 'click', function(e) {
      e.preventDefault();
      let ts = $(this);
      let type = ts.data( 'type' );
      if( typeof types[type] !== 'undefined' ) {
        append_type( type, types[type] );
        return false;
      }
      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=rich-form-new-el',
        data: { type: type },
        dataType: 'text'
      }, function( data ) {
        append_type( type, data );
        types[type] = data;
      });
    });

    return t;
  };

  $.fn.cms_animation = function( options, after_finish ) {
    let option = $.extend( {
        duration: 1000,
        class: 'shake'
    }, options );

    let t = this;

    t.addClass( option.class );
    
    setTimeout( function() {
      t.removeClass( option.class );
      if ( $.isFunction( after_finish ) ) {
        after_finish.call( this );
      }
    }, option.duration );
    
    return t;
  };

  $.fn.cms_post_comment_handler = function( options, container, success ) {
    let option = $.extend( {
      action: 'post-comment',
      extra_data: ''
    }, options );

    let t = this;
    let d = t.serialize() + option.extra_data;

    t.html(t.html());

    t.cms_simple_call( {
      URL: utils.ajax_url + '&action=' + option.action,
      data: d
    }, function( data ) {
      if( data.status == 'success' ) {
        container.prepend( data.answer );
        if ( $.isFunction( success ) ) {
          success.call( t, data );
        }
      }
    } );

    return t;
  };

  $.fn.cms_votes_handler = function( options, button, button2, type, vote ) {
    let option = $.extend( {
      action: 'vote',
      action2: 'remove-vote'
    }, options );

    let t   = this;
    let hs  = button.hasClass( 'selected' );
    let c   = button.find( '[data-count]' );
    let cv  = parseInt( c.attr( 'data-count' ) );

    t.cms_simple_call( {
      URL: utils.ajax_url + '&action=' + ( hs ? option.action2 : option.action ),
      data: { id: t.attr( 'id' ), type: type, vote: vote }
    }, function( data ) {
      if( data.status == 'success' ) {
        if( hs ) {
          cv = cv - 1;
          c.attr( 'data-count', cv ).text( cv );
          button.removeClass( 'selected' );
        } else {
          if( parseInt( data.answer ) == 0 ) {
            // nothing new, just add select class
            button.addClass( 'selected' );
          } else if( parseInt( data.answer ) == 1 ) {
            // add new vote
            cv = cv + 1;
            c.attr( 'data-count', cv ).text( cv );
            button.addClass( 'selected' );
          } else if( parseInt( data.answer ) == 2 ) {
            // change vote
            cv = cv + 1;
            c.attr( 'data-count', cv ).text( cv );
            button.addClass( 'selected' );
            // remove old vote
            let c2 = button2.find( '[data-count]' );
            let cv2 = parseInt( c2.attr( 'data-count' ) ) - 1;
            c2.attr( 'data-count', cv2 ).text( cv2 );
            button2.removeClass( 'selected' );
          }
        }
      }
    } );

    return t;
  };

  $.fn.cms_images_upload = function( changed ) {
    let t = this;
    let input = $( '<input type="file" name="images[]" accept="image/*" multiple />' );
    input.click();
    input.on( 'change', function() {
      let t2 = this;
      let html_el = $( '<div class="form_line form_attachment form_line_bg"></div>' ).append( input );
      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=attach-el&type=images',
        dataType: 'text'
      }, function( data ) {
        $.each( t2.files, function( x, ufile ) {
            let file = window.URL.createObjectURL( ufile );
            html_el.append( data.replace( /%ID%/gi, x ).replace( /%IMAGE%/gi, file ) );
        });
        t.prevAll( '.form_line:first' ).after( html_el );
      } );
      if ( $.isFunction( changed ) ) {
        changed.call( t );
      }
    });
  };

  $.fn.cms_video_upload = function( changed ) {
    let t = this;
    let input = $( '<input type="file" name="video" accept=".mp4" />' );
    input.click();
    input.on( 'change', function() {
      let t2 = this;
      let html_el = $( '<div class="form_line form_attachment"></div>' ).append( input );
      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=attach-el&type=video',
        dataType: 'text'
      }, function( data ) {
        $.each( t2.files, function( x, ufile ) {
            let file = window.URL.createObjectURL( ufile );
            html_el.append( data.replace( /%VIDEO%/gi, file ) );
        });
        t.prevAll( '.form_line:first' ).after( html_el );
      } );
      if ( $.isFunction( changed ) ) {
        changed.call( t );
      }
    });
  };

  $.fn.cms_pagination = function( options, url, scroll_el, target_el, pdata ) {
    let option = $.extend( {
      next_page:  1,
      loader:     '<li class="loader">' + g_preloader + '</li>'
    }, options );

    let t       = this;
    let scroll  = t.find( scroll_el ),
    target      = t.find( target_el ),
    page_height = target.height(),
    def_height  = scroll.height(),
    current_p   = 1,
    next_page   = option.next_page;

    t.reinit = function( data, next ) {
      pdata     = data;
      current_p = 1;
      next_page = next;
    }

    t.on_scroll = function() {
      page_height   = t.find( target ).height();
      if( ( page_height - scroll.scrollTop() - 200 ) <= def_height && current_p < next_page ) {
        current_p   = current_p + 1;
        let loader  = $(option.loader);
        target.append( loader );
        t.cms_simple_call( {
          URL:  url,
          data: pdata + '&page=' + next_page
        }, function( data ) {
          loader.remove();
          target.append( data.content );
          next_page = parseInt( data.ajax_next );
        } );
      }
    }

    let iv;

    scroll.scroll( function() {
      clearInterval( iv );
      iv = setTimeout( function() {
        t.on_scroll();
      }, 100 );
    });

    return t;
  };

  $.fn.cms_options_nav = function( options ) {
    let option = $.extend( {
      el: '',
      URL: '',
      data: {}
  }, options );

    let t = this;
    let c = t.find( option.el ),
    cu,
    data  = option.data;

    t.on( 'click', 'li:not(.more) > a', function(e) {
      e.preventDefault();
      let ts = $(this);
      let p = ts.parent();
      if( p.hasClass( 'active' ) ) {
        return;
      }
      t.current( p ).action( ts );
    });

    t.on( 'click', 'li.more > a', function(e) {
      e.preventDefault();
      $(this).parent().toggleClass( 'active' );
    });

    t.current = function( item ) {
      if( cu ) {
        cu.removeClass( 'active' );
      }

      cu = item;
      return t;
    } 

    t.action = function( item ) {
      data.id   = item.data( 'id' );
      data.type = item.attr( 'id' );

      if( typeof data.type == 'undefined' ) {
        return ;
      }

      c.html( g_preloader );

      t.cms_simple_call( {
        URL: option.URL,
        data: option.data
      }, function( data ) {
        item.parent().addClass( 'active' );
        c.html( data.content )
      } );
      return t;
    }

    return t;
  };

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
            if( Object.values( vals[v[1]] ).length != 0 ) {
              item.removeClass( 'visible' ).addClass( 'hidden' ).hide();
              show = false;
              return ;
            }
          break;

          case 'NOT_EMPTY':
            if( Object.values( vals[v[1]] ).length == 0 ) {
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
      item.on( 'change input', function() {
        let ti = $(this);
        if( ti.is( ':radio' ) ) {
          if( ti.is( ':checked' ) ) {
            vals[item_id] = ti.val();
          }
        } else if( len > 1 ) {
          let nid = ti.attr( 'name' ).match( /\[(\w+)\]$/ );
          if( !ti.is( ':checkbox' ) || ti.is( ':checked' ) ) {
            vals[item_id][nid[1]] = ti.val();
          } else {
            delete vals[item_id][nid[1]];
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
          if( !nt.is( ':checkbox' ) || nt.is( ':checked' ) ) {
            vals[id][nid[1]] = nt.val();
          }
        });
      } else {
        vals[id] = ct.is( ':checkbox' ) ? ( ct.is( ':checked' ) ? 1 : 0 ) : ct.val();
      }
      follow_change( ct, id, val.target, len );
    });

    cur_el.on( 'change', function() {
      return false;
    }).change();

    return t;
  };

  $.fn.cms_create_poll = function( success ) {
    let t = this;
    t.cms_simple_call( {
      URL: utils.ajax_url + '&action=attach-el&type=poll',
      dataType: 'text'
    }, function( data ) {
      let html_el = $( data );

      t.prevAll( '.form_line:first' ).after( html_el );
      if ( $.isFunction( success ) ) {
        success.call( t, data );
      }
      
      html_el.find( 'a.add_more' ).on( 'click', function(e) {
        e.preventDefault();
        let ct = $(this);
        let tl = ct.parent( '.form_line' ),
        pl = tl.next();
        tl.before( '<div class="form_line answr">' + pl.html() +  '</div>' );
      });

      $(document).on( 'click', '.poll_create a.remove', function(e) {
        e.preventDefault();
        $(this).parents( '.form_line:first' ).remove();
      });
    });
  };

  $.fn.cms_new_post = function( options, st_options ) {
    let option = $.extend( {
      current: 0,
    }, options );

    let t = this;
 
    t.cms_tag_search( st_options );

    let pas_container,
    c_container = t.closest( '.content-container' ),
    h_container = c_container.find( '.headline-msg' );
    let link    = c_container.find( 'a:first' ),
    article     = t.find( 'input[name="data[post_as]"]' ),
    t_changer   = t.find( '.ctemplate select' ),
    curr_art    = option.current,
    curr_art_el;

    function loadContent( after, data ) {
      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=post-as-markup',
        data: data
      }, function( data ) {
        after.call( this, data );
      } );
    }

    t_changer.on( 'change', function() {
      let ts    = $(this);
      let form  = ts.closest( 'form' ),
      val       = ts.val();

      if( val !== '' ) {
        t.cms_simple_call( {
          URL: utils.ajax_url + '&action=new-category-post&template=' + val,
          data: form.serialize()
        } );
      }
    });

    link.on( 'click', function(e) {
      e.preventDefault();
      let ts = $(this);
      if( ts.hasClass( 'waiting' ) ) {
        return false;
      }

      if( typeof pas_container !== 'undefined' ) {
        pas_container.toggleClass( 'hidden' );
      } else {
        ts.addClass( 'waiting' );
        loadContent( function( data ) {
          ts.removeClass( 'waiting' );
          pas_container       = $( data.content );
          let list_container  = pas_container.find( 'ul' );
          h_container.prepend( pas_container );
          curr_art_el = list_container.find( 'li.active' );

          pas_container.on( 'click', 'a[data-id]', function(e) {
            e.preventDefault();
            let ts    = $(this);
            if( typeof curr_art_el !== 'undefined' ) {
              curr_art_el.removeClass( 'active' );
            }
            curr_art  = ts.data( 'id'  );
            curr_art_el = ts.closest( 'li' );
            curr_art_el.addClass( 'active' );
            link.text( decodeUnicode( ts.data( 'dname' ) ) );
            article.val( curr_art );
            pas_container.addClass( 'hidden' );
          });

          let int;

          pas_container.on( 'keyup', 'input', function() {
            let ts = $(this);
            clearTimeout( int );
            int = setTimeout( function() {
              loadContent( function( data ) {
                list_container.html( data.content );
                if( data.current ) {
                  curr_art_el = list_container.find( 'li.active' );
                }
              }, { text: ts.val(), current: curr_art } );
            }, 1000 );
          } );
        }, { current: curr_art } );
      }
    } );

    t.removeClass( 'wform' );

  }

  $.fn.cms_tag_search = function( options ) {
    let option = $.extend( {
      default: 0,
      target: '.tag_search',
      search_input: '_search',
      category_changer: '.new-post-cat input',
      max_sel: 10,
      auto_search: false,
      auto_search_text: ''
    }, options );

    let t       = this;
    let target  = t.find( option.target ),
    cur_cat     = option.default,
    cats        = {},
    cats_loaded = {};
    let searchi = target.find( 'input[name="' + option.search_input + '"]' ),
    cat_changer = t.find( option.category_changer );

    function updated() {
      cats[cur_cat].el.find( 'input:not(:checked)' ).closest( 'li' ).remove();
      $.each( cats[cur_cat].items, function( k, item ) {
        cats[cur_cat].el.prepend( item );
      });
      cats[cur_cat].el.find( 'input' ).on( 'change', function() {
        let ct = $(this);
        let id = ct.attr( 'id' ).split( '-' ).slice(-1);
        if( ct.is( ':checked' ) ) {
          cats[cur_cat].selected[id[0]] = true;
        } else {
          delete cats[cur_cat].selected[id[0]];
        }
      });
    }

    function search( text ) {
      if( typeof cats[cur_cat] == 'undefined' ) {
        let new_ul = $( '<ul class="tag-list cat-' + cur_cat + '"></ul>' );
        target.append( new_ul );
        cats[cur_cat]           = {};
        cats[cur_cat].el        = new_ul;
        cats[cur_cat].selected  = [];
      }

      let data = { category: cur_cat, text: text, selected: Object.keys( cats[cur_cat].selected ) };

      if( cur_cat in cats_loaded ) {
        data.cat_loaded = true;
      }

      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=search-tag-by-category',
        data: data
      }, function( data ) {
          cats_loaded[cur_cat]  = true;
          if( typeof data.items !== 'undefined' ) {
            cats[cur_cat].items   = Object.entries( data.items ).map( ( v ) => {
              return $( '<li><div class="checkbox"> \
              <input type="checkbox" name="data[tags][' + v[0] + ']" id="cat-' + cur_cat + '-tag-' + v[0] + '" value="' + v[1] + '" /> \
              <label for="cat-' + cur_cat + '-tag-' + v[0] + '"><span></span>' + v[1] + '</label> \
              </div></li>' );
            });
            updated();
        }
      } );
    }

    function changed() {
      if( cur_cat == 0 ) {
        return false;
      }

      Object.entries( cats ).map( ( v ) => {
        if( v[0] == cur_cat ) {
          v[1].el.show();
        } else if( v[0] !== cur_cat ) {
          v[1].el.hide();
        }
      } );

      searchi.val( '' );
      if( typeof cats[cur_cat] == 'undefined' ) {
        search( '' );
      }
    }

    let cc;

    searchi.on( 'keyup', function() {
      clearInterval( cc );
      let val = $(this).val();
      cc = setInterval( function() {
        search( val );
        clearInterval( cc );
      }, 200 );
    });

    cat_changer.on( 'change', function() {
      let val = $(this).val();
      if( $.isNumeric( val ) ) {
        cur_cat = val;
        changed();
      }
    });

    if( option.auto_search ) {
      search( option.auto_search_text );
    }
  }

  $.fn.cms_input_search = function( options ) {
    let option = $.extend( {
      click_cb: undefined,
    }, options );

    let t = this,
    target,
    last_text;
    let val = t.data( 'isearch' ),
    type = t.data( 'stype' );

    if( !target ) {
      target = $( '<ul class="loc-list"></ul>' );
      t.after( target );
    }

    function search( text ) {
      last_text = text;
      target.slideUp( 50, function() {
        target.html( '' );
        let data = { value: val, type: type, text: text };
        if( typeof t.prop( 'exclude' ) == 'object' ) {
          data.exclude = $.param( t.prop( 'exclude' ), true );
        }
        t.cms_simple_call( {
          URL: utils.ajax_url + '&action=search-input',
          data: data
        }, function( data ) {
            $.each( data.items, ( k, v ) => {
              let li = $( '<li><a href="#">' + v['name'] + ( typeof v['parents'] !== 'undefined' && v['parents'] !== '' ? '<span>' + v['parents'] + '</span>' : '' ) + '</a></li>' );
              li.prop( 's-info', v );
              target.append( li );
            });
            target.slideDown( 50 );
        } );
      });
    }

    if( option.click_cb !== undefined ) {
      target.on( 'click', '> li', function(e) {
        e.preventDefault();
        let ts = $(this);
        if( option.click_cb !== '' ) {
          window[option.click_cb]( t, target, ts.prop( 's-info' ), ts );
        }
      });
    }

    let cc;

    t.on( 'keyup', function() {
      clearInterval( cc );
      let val = $(this).val();
      if( val == last_text ) {
        return false;
      }

      cc = setInterval( function() {
        search( val );
        clearInterval( cc );
      }, 500 );
    });
  }

  $.cms_opt_filters = function() {
    $( document ).on( 'change', '[data-filter]:not(.active) input', function(e) {
      e.preventDefault();
      let t = $(this);
      let f = t.parents( '[data-filter]' );
      let c,
      fl    = f.attr( 'data-filter');
      if( fl == '' ) {
        c = f.next( '[data-filter-call]' );
      } else {
        c = f.find( fl );
      }
      
      filter_activated( t, f, c );
      f.addClass( 'active' );
    });

    function filter_activated( t, f, c ) {
      let df  = true,
      ival,
      fltrs   = [],
      def     = false;

      function do_filter() {
        if( df ) {
          c.html( '<li class="loader">' + g_preloader + '</li>' );
          let pdata = f.find( 'input' ).serialize()
          t.cms_simple_call( {
            URL:  c.data( 'filter-call' ),
            data: pdata
          }, function( data ) {
            c.html( data.content );
            if( typeof data.ajax_next !== 'undefined' && t.parents( '.paginit').length ) {
              let pagInit = t.parents( '.paginit' ).prop( 'cms_pagination' );
              if( pagInit !== undefined ) {
                  pagInit.reinit( pdata, data.ajax_next );
              }
            }
          } );
        }
        df = false;
      }

      function change_title( t ) {
        if( t.attr( 'type' ) == 'radio' ) {
          t.parents( 'ul' ).prev( 'a' ).find( '> span' ).text( t.next( 'label' ).text() );
        } else {
          let p = t.parents( 'ul' );
          if( def === false ) {
            def = p.prev( 'a' ).find( '> span' ).text();
          }
          if( t.is( ':checked' ) ) {
            fltrs[t.next( 'label' ).text().substr( 0, 3 )] = '';
          } else {
            delete fltrs[t.next( 'label' ).text().substr( 0, 3 )];
          }
          let len = Object.keys( fltrs ).length;
          if( len == 0 ) {
            p.prev( 'a' ).find( '> span' ).text( def );
          } else if( len <= 3 ) {
            p.prev( 'a' ).find( '> span' ).text( def + ' (' + Object.keys( fltrs ).join( ', ' ) + ')' );
          } else if( len <= 4 ) {
              p.prev( 'a' ).find( '> span' ).text( def + ' (' + Object.keys( fltrs ).splice( 0, 3 ).join( ', ' ) + '...)' );
          }
        }
      }

      function call_filter( t ) {
        df = true;
        clearInterval( ival );
        change_title( t );
        ival = setTimeout( function() {
          do_filter();
        }, 2000 );
      }

      f.find( '> li input' ).on( 'change', function() {
        call_filter( $(this) );
      });

      f.find( '> li' ).on( 'mouseenter', function(e) {
        clearInterval( ival );
      }).on( 'mouseleave', function() {
        clearInterval( ival );
        ival = setInterval( function() {
          do_filter();
        }, 300 );
      });

      call_filter( t );
      change_title( t );
    }
  };

  $.fn.cms_intricate_form = function( options ) {
    let t = this;

    let call = t.parents( 'form').cms_call( options,
      function( response ) {
      if( response.status === 'error' ) {
        t.parents( '.form-box' ).cms_animation();
      }
    });

    t.parents( 'form').on( 'submit', function(e) {
      e.preventDefault();
      e.stopPropagation();
      call.init_call();
    });

    return call;
  };

  /** AJAX NAVIGATION */

    $.fn.cms_go_to = function() {
        let t   = this;
        let c   = $( '.content' );
        let ic  = c,
        cbg     = {},
        cag     = [];

        t.beforeGo = ( call, id ) => {
            cbg[id] = call;
            return t;
        };

        t.afterGo = ( call ) => {
            cag.push( call );
            return t;
        };

        t.removeBeforeGo = ( id ) => {
            delete cbg[id];
        }
    
        t.go2 = ( trigger, href, data2, options, after ) => {
            if( Object.keys( intervals ).length ) {
                $.each( intervals, ( k, v ) => {
                    clearInterval( v );
                } );
            }

            c.html( '' );

            t.init( () => {
                if( Object.keys( cbg ).length ) {
                    $.each( cbg, function( k, f ) {
                        f.call();
                    } );
                }

                let dataOptions = { type: data2[0], id: data2[1], params: data2.slice(2), options: options };
                if( nav ) {
                    dataOptions.nav = nav.prop( 'name' );
                }

                $( 'body' ).cms_simple_call( {
                    URL: utils.ajax_url + '&action=load',
                    data: dataOptions
                }, ( data ) => {
                    if( href != '#' ) {
                        window.history.pushState( '', '', href );
                    }

                    if( typeof data.href != 'undefined' ) {
                        window.history.pushState( '', '', data.href );
                    }

                    if( nav && ( !data.menu || nav.prop( 'name' ) != data.menu ) ) {
                        nav.addClass( 'close' );
                        setTimeout( () => {
                            nav.remove();
                            nav = undefined;
                        }, 300 );
                    }

                    c.html( data.content );

                    if ( typeof after !== 'undefined' ) {
                        if ( $.isFunction( after ) ) {
                            after.call( this, t, trigger, data, data2 );
                        } else if( typeof window[after] === 'function' ) {
                            window[after]( t, trigger, data, data2 );
                        }
                    }
                    
                    if( typeof on_change_events !== 'undefined' && on_change_events.length ) {
                        $.each( on_change_events, function( k, f ) {
                            f();
                        });
                    }

                    if( cag.length ) {
                        let cag2 = cag;
                        cag = [];
                        $.each( cag2, function( k, f ) {
                            f.call();
                        });
                    }
                });
            } );
        }

        t.init = ( callback ) => {
            callback.call( t );
        }

        $(document).on( 'click', '[data-to]:not(input)', function(e) {
            e.preventDefault();
            let ts    = $(this);
            let href  = ts.attr( 'href' );
            let data  = ts.data( 'to' ).split( ':' );
            let o     = ts.data( 'options' );
            let b     = ts.data( 'before' );
            let a     = ts.data( 'after' );

            if( typeof b !== 'undefined' && typeof window[b] === 'function' ) {
                window[b]( t, ts );
            }

            t.go2( ts, href, data, o, a );
        });

        let cc;

        $(document).on( 'keyup', 'input[data-to]', function(e) {
            clearInterval( cc );

            let ts    = $(this);
            let data  = ts.data( 'to' ).split( ':' );
            let val   = ts.val();

            cc = setInterval( () => {
                c.html( '' );
                switch( data[0] ) {
                case 'page':
                    ic = c;
                break;

                default:
                    ic = $( '<ul class="boxes"></ul>' );
                    ic.append( post_loader.repeat( 2 ) );
                    c.append( ic );  
                }

                t.init( () => {
                    $( 'body' ).cms_simple_call( {
                        URL: utils.ajax_url + '&action=load',
                        data: { type: data[0], id: data[1], params: data.slice(2), search: val }
                    }, ( data ) => {
                        ic.html( data.content );
                    });
                } );
                clearInterval( cc );
            }, 200 );
        });
        
        $( '[data-load-to]' ).each( function(e) {
            let ts    = $(this);
            let href  = ts.attr( 'href' );
            let data  = ts.data( 'load-to' )?.split( ':' );
            let o     = ts.data( 'options' );
            let b     = ts.data( 'before' );
            let a     = ts.data( 'after' );
            
            if( typeof b !== 'undefined' && typeof window[b] === 'function' ) {
                window[b]( t, ts );
            }

            t.go2( ts, href, data, o, a );
        });

        return t;
    };

  $.cms_do_on_scroll = function( elem ) {
    let t = this;
    let h = $(window).height();
    let w = $(window).width();
    
    let ielems = [];

    t.reinit = function() {
      $.each( elem, function( res, el ) {
        if( w >= res ) {
          $.each( el, function( k, el2 ) {
            $( el2[0] ).each( function() {
              ielems.push( [ $(this), el2[1], ( $(this).offset().top + el2[2] ), el2[3], el2[4], true, true ] );
            });
          })
        }
      });
    }

    t.reinit();

    function compare( position, id, el ) {
      switch( el[1] ) {
        case '>':
        if( ( position - el[2] ) > 0 ) {
          if( el[5] ) {
            el[3].call( this, el[0] );
            ielems[id][5] = false;
            ielems[id][6] = true;
          }
        } else {
          if( el[6] ) {
            el[4].call( this, el[0] );
            ielems[id][5] = true;
            ielems[id][6] = false;
          }
        }
        break;
      }
    }

    $(window).scroll( function() {
      let pxt = $(window).scrollTop();
      $.each( ielems, function( k, item ) {
        compare( pxt, k, item );
      });
    }).scroll();

    return t;
  };

  $.fn.cms_search = function( options ) {
    let option = $.extend( {
      action: 'search2',
      default: 'members',
      value: ''
    }, options );

    let t       = this,
    c           = $( option.container ),
    b           = c.find( '.boxes' ),
    f           = c.find( '[data-filters]' ),
    al          = c.find( '[data-types-list]' ),
    type        = option.default,
    a_ld        = '<li><div class="user-c"><div class="image big"><div class="ph"></div></div><div><div class="h-user"><span class="line-ph ph" style="width:150px;"></span><span class="date"><span class="line-ph ph" style="width:30px;"></span></span></div><ul class="inf"><li><span class="line-ph ph" style="width:100px;"></span></li><li><span class="line-ph ph" style="width:200px;"></span></li></ul><ul class="inf"><li><span class="line-ph ph" style="height: 30px; width:100px;"></span></li></ul></div></div></li>',
    def_el      = al.find( '> li.active' ),
    filters     = {},
    currBS      = 'bs2';
    
    if( option.value != '' ) {
      filters['text'] = decodeUnicode( option.value );
    }

    load_results( true );

    function load_results( load_filters ) {
      b.html( a_ld.repeat( 2 ) );

      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=' + option.action,
        data: { data: { filters: filters }, type: type, load_filters: load_filters }
      }, function( data ) {
        b.html( data.list );
        if( load_filters ) {
          if( data.filters !== '' ) {
            f.html( atob( data.filters ) ).show();
            init_filters();
          } else {
            f.hide().html( '' );
          }
        }
      } );
    }

    al.find( '[data-types]' ).on( 'click', function() {
      let ts = $(this);
      if( ts.is( def_el ) ) {
        return false;
      }

      b.removeClass( currBS );
      currBS = ts.data( 'box-style' );
      b.addClass( currBS );
      def_el.removeClass( 'active' );
      def_el  = ts;
      def_el.addClass( 'active' );
      type    = ts.data( 'types' );
      filters = {};
      load_results( true );
    });

    function init_filters() {
      let sint;

      f.find( 'input[type="text"]' ).on( 'keyup', function() {
        let ts  = $(this);
        let val = ts.val();
        let nid = ts.attr( 'name' ).match( /\[(\w+)\]$/ );

        clearTimeout( sint );

        if( val != '' ) {
          filters[nid[1]] = val;
        } else {
          delete filters[nid[1]];
        }

        sint = setTimeout( function() {
          load_results( false );
        }, 1500 );
      });
    }
  };

  $.fn.cms_articles_search = function( options ) {
    let option = $.extend( {
      action: 'search-articles',
      mlen: 'km',
      def_range: 10,
      default: 0
    }, options );

    let t       = this,
    c           = $( option.container ),
    b           = c.find( '.boxes' ),
    f           = c.find( '[data-filters]' ),
    al          = c.find( '[data-articles-list]' ),
    arts        = option.default,
    lat         = option.lat,
    lng         = option.lng,
    range       = option.def_range,
    a_ld        = '<li><div class="user-c"><div class="image big"><div class="ph"></div></div><div><div class="h-user"><span class="line-ph ph" style="width:150px;"></span><span class="date"><span class="line-ph ph" style="width:30px;"></span></span></div><ul class="inf"><li><span class="line-ph ph" style="width:100px;"></span></li><li><span class="line-ph ph" style="width:200px;"></span></li></ul><ul class="inf"><li><span class="line-ph ph" style="height: 30px; width:100px;"></span></li></ul></div></div></li>',
    def_el      = al.find( '> li.active' ),
    filters     = {},
    currBS      = 'bs2';
    
    load_articles( true );

    function load_articles( load_filters ) {
      b.html( a_ld.repeat( 2 ) );

      t.cms_simple_call( {
        URL: utils.ajax_url + '&action=' + option.action,
        data: { data: { articles: arts, filters: filters, lat: lat, lng: lng, mlen: option.mlen, range: range }, load_filters: load_filters }
      }, function( data ) {
        b.html( data.list );
        if( load_filters ) {
          if( data.filters !== '' ) {
            f.html( atob( data.filters ) ).show();
          } else {
            f.hide().html( '' );
          }
          init_filters();
        }
      } );
    }

    al.find( '[data-articles]' ).on( 'click', function() {
      let ts = $(this);
      if( ts.is( def_el ) ) {
        return false;
      }

      b.removeClass( currBS );
      currBS = ts.data( 'box-style' );
      b.addClass( currBS );
      def_el.removeClass( 'active' );
      def_el  = ts;
      def_el.addClass( 'active' );
      arts    = ts.data( 'articles' );
      filters = {};
      load_articles( true );
    });

    function init_filters() {
      let sint;

      f.find( 'input[type="text"]' ).on( 'keyup', function() {
        let ts  = $(this);
        let val = ts.val();
        let nid = ts.attr( 'name' ).match( /\[(\w+)\]$/ );

        clearTimeout( sint );

        if( val != '' ) {
          filters[nid[1]] = [ val ];
        } else {
          delete filters[nid[1]];
        }

        sint = setTimeout( function() {
          load_articles( false );
        }, 1500 );
      });

      f.find( ':radio, :checkbox, select' ).on( 'change', function() {
        let ts  = $(this);
        let val = ts.val();
        let nid = ts.attr( 'name' ).match( /\[(\w+)\]$/ );

        clearTimeout( sint );
        if( ts.is( ':checkbox' ) ) {
          if( ts.is( ':checked' ) ) {
            filters[nid[1]] = val;
          } else {
            delete filters[nid[1]];
          }
        } else if( ts.is( ':radio' ) || ts.is( 'select' ) ) {
          if( val == '' ) {
            delete filters[nid[1]];
          } else {
            filters[nid[1]] = val;
          }
        }
        sint = setTimeout( function() {
          load_articles( false );
        }, 1500 );
      });
    }

    if( option.range_changer ) {
      $( option.range_changer ).on( 'change', function() {
        range = $(this).val();
        load_articles( false );
      });
    }

    if( option.point_changer ) {
      $( option.point_changer ).on( 'change', function() {
        let newLatLng = $(this).val().split( " " );
        lat = newLatLng[0];
        lng = newLatLng[1];
        load_articles( false );
      });
    }
  };

  $.fn.cms_richEditor = function( options ) {
    let option = $.extend( {
      send_action: 'send-message',
      default: undefined
    }, options );

    let t         = this,
    message       = '',
    attcs         = {},
    on_submit,
    attcs_container,
    emj_container,
    attch_input   = $( '<input type="file" name="images[]" accept="image/*" multiple />' );
    let container = t.find( '[contentEditable]' ),
    msg_container = t.find( '.compose-msg' ),
    actions       = t.find( '.topts' );
    let b_bold    = actions.find( '[data-attr="STRONG"]' ),
    b_italic      = actions.find( '[data-attr="I"]' ),
    b_underline   = actions.find( '[data-attr="U"]' ),
    rt_buttons    = actions.find( '.richtext' ),
    img_button    = actions.find( '[data-attr="image"]' ),
    attc_button   = actions.find( '[data-attr="attach"]' ),
    send_button   = actions.find( '[data-attr="send"]' ),
    defText       = container.html(),
    form_data     = new FormData;

    attc_button.on( 'click', function(e) {
      e.preventDefault();
      if( emj_container ) {
        emj_container.toggleClass( 'hidden' );
      } else {
        t.cms_simple_call( {
          URL: utils.ajax_url + '&action=com-load-emojis',
          dataType: 'text'
        }, function( data ) {
          emj_container = $( data );
          msg_container.after( emj_container );
          t.emoji_container();
        });
      }
    });
    
    img_button.on( 'click', function(e) {
      e.preventDefault();
      attch_input.trigger( 'click' );
    });

    attch_input.on( 'change', function() {
      if( !attcs_container ) {
        attcs_container = $( '<div class="attach dflexw"></div>' );
        attcs_container.on( 'click', 'a', function(e) {
          e.preventDefault();
          let el = $(this).parent();
          let id = el.prop( 'attc_id');
          delete attcs[id];
          form_data.delete( id );
          if( Object.keys( attcs ).length > 0 ) {
            el.remove();
          } else {
            el.remove();
            attcs_container.remove();
            attcs_container = undefined;
            check_send_button();
          }
        });
        msg_container.before( attcs_container );
      }

      $.each( this.files, function( x, ufile ) {
        let file = window.URL.createObjectURL( ufile );
        let id = [ ufile.name, ufile.size ].join( '-' );
        if( !Object.keys( attcs ).includes( id ) ) {
          attcs[id] = { 'value': ufile, 'filename': ufile.name };
          form_data.append( id, ufile, ufile.name );
          let new_el = $( '<div style="background-image:url(' + file + ');"><a href="#"><i class="fas fa-times"></i></a></div>' );
          new_el.prop( 'attc_id', id );
          attcs_container.append( new_el );
        }
        check_send_button();
      });
    });

    t.emoji_container = function() {
      emj_container.find( '.attach-emj > ul a' ).on( 'click', function(e) {
        e.preventDefault();
        let ts = $(this);
        let place = ts.data( 'place')
        let ul = ts.closest( 'ul' );
        let div = ul.next( 'div' );
        div.css( 'margin-left', -( parseInt( place ) * 100 ) + '%' );
      });

      emj_container.find( '.attach-emj > div > ul a' ).on( 'click', function(e) {
        e.preventDefault();
        if( message == '' ) {
          container.html( '' );
        }
        let selection = window.getSelection();
        if( selection.containsNode( container[0], true ) ) {
          let range = selection.getRangeAt(0);
          let newNode = document.createTextNode( $(this).text() );
          range.insertNode( newNode );
          range.collapse( false );
        } else {
          let range = selection.getRangeAt(0);
          range.selectNodeContents( container[0] );
          range.collapse( false );
          let newNode = document.createTextNode( $(this).text() );
          range.insertNode( newNode );
          range.collapse( false );
        }

        message = container.text();
        check_send_button();
      });
    }

    container.on( 'click', function(e) {
      e.stopPropagation();
      if( $.trim( message ) == '' ) {
        container.html( '' );
      }
    });

    msg_container.on( 'click', function(e) {
      if( e.target.nodeName !== 'DIV' ) {
        return false;
      }

      if( message == '' ) {
        container.html( '' );
      }

      let range = document.createRange();
      range.selectNodeContents( container[0] );
      range.collapse( false );
      let selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange( range );
    });

    container.on( 'input', function(e) {
      message = $.trim( $(this).text() );
      check_send_button();
    });

    container.blur( function() {
      if( message == '' ) {
        container.html( defText );
      }
    });

    container.on( 'paste', function(e) {
      e.preventDefault();
      let clipboardData = e.clipboardData || window.clipboardData || e.originalEvent.clipboardData;
      document.execCommand( 'insertText', false, clipboardData.getData( 'Text' ) );
    });

    rt_buttons.on( 'click', function(e) {
      e.preventDefault();
      let tag = $(this).data( 'attr' );
      let selection = window.getSelection();
      let pn = selection.anchorNode.parentNode;
      if( pn.nodeName == tag ) {
          container.html( container.html().replace( pn.outerHTML, pn.innerHTML ) );
      } else {
        let range = selection.getRangeAt(0);
        let content = range.toString();
        range.deleteContents();
        let newNode = document.createElement( tag );
        newNode.appendChild( document.createTextNode( content ) );
        range.insertNode( newNode );
      }
      message = container.text();
      check_send_button();
    });

    send_button.on( 'click', function(e) {
      e.preventDefault();

      form_data.set( 'text', container.html() );

      t.cms_simple_call( {
        URL:      utils.ajax_url + '&action=' + option.send_action,
        data:     form_data,
      }, function( data ) {
        message = '';
        container.html( defText );
        check_send_button();
        if( on_submit && $.isFunction( on_submit ) ) {
          on_submit.call( this, data );
        }
        hidePreloader();
      }, function( jqXHR, status, thrown ) {
        hidePreloader();
      }, function() {
        showPreloader();
      } );
    });

    let node;

    $( document ).on( 'selectionchange', function() {
      rt_buttons.hide();
      let selection = window.getSelection();
      let select_str = selection.getRangeAt(0);

      if( selection.containsNode( container[0], true ) ) {
        if( selection.anchorNode.nodeName === '#text' ) {
          node = selection.anchorNode.parentNode;
        } else {
          node = selection.anchorNode;
        }

        if( node.nodeName === 'DIV' && node.parentNode.contentEditable == 'true' ) {
          node = node.parentNode;
        }

        if( select_str.toString() !== '' && node.isSameNode( container[0] ) && !selection.anchorNode.isSameNode( container[0] ) && selection.anchorNode.isSameNode( selection.focusNode ) ) {
          rt_buttons.show();
        } else {
          switch( node.nodeName ) {
            case 'STRONG':
              b_bold.show();
            break;

            case 'I':
              b_italic.show();
            break;

            case 'U':
              b_underline.show();
            break;
          }
        }
      }
    });

    function showPreloader() {
      send_button.prop( 'disabled', true );
    }

    function hidePreloader() {
      send_button.prop( 'disabled', false );
    }

    function check_send_button() {
      if( message !== '' || Object.keys( attcs ).length > 0 ) {
        send_button.show();
      } else {
        send_button.hide();
      }
      return false;
    }

    t.onSubmited = function( fn ) {
      on_submit = fn;
    }

    t.checkPlaceholder = function() {
      if( message == '' ) {
        container.html( defText );
      }
      return t;
    }

    t.resetContent = function() {
      message = '';
      container.html( defText );
      check_send_button();
    }

    t.setFormData = function( id, data ) {
      form_data.set( id, data );
      return t;
    }

    t.deleteFormData = function( id ) {
      form_data.delete( id );
      return t;
    }

    if( option.default ) {
      switch( option.default ) {
        case 'image':
          attch_input.trigger( 'click' );
        break;
        case 'attach':
          attc_button.trigger( 'click' );
        break;
      }
    } else {
      container.html( '' ).trigger( 'focus' );
    }

    return t;
  };

}( jQuery ));
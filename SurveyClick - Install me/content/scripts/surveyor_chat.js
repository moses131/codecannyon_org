'use strict';

var surveyor_chat;

(function($) {

    $.fn.cms_chat = function( options ) {
        let option = $.extend( {
            el:         '.chat',
            msgs:       '> .tr',
            write:      '> .chat_actions > form',
            nextPage:   '> .pagination'
        }, options );
    
        let t       = this,
        cInt,
        lastMsg,
        el          = $( option.el );
        let msgs    = el.find( option.msgs ),
        fwrite      = el.find( option.write ),
        nextPage    = el.find( option.nextPage );
        let iwrite  = fwrite.find( 'input[name="message"]' );
    
        fwrite.on( 'submit', function(e) {
            e               .preventDefault();
            let fdata       = new FormData( fwrite[0] );
            let newMsg      = $( '<div class="td tc"><i class="fas fa-circle-notch fa-spin"></i></div>' );
            iwrite          .val( '' );
            msgs            .prepend( newMsg );
            
            t.cms_simple_call( {
                URL: utils.ajax_url + '&action=chat&type=send-message',
                data: fdata
            }, ( data ) => {
                newMsg.replaceWith( data.message );
            } );
        } );

        nextPage.on( 'click', function(e) {
            e.preventDefault();
            load_page();
        } );

        function load_page() {
            t.cms_simple_call( {
                URL: utils.ajax_url + '&action=chat&type=load-messages',
                data: { last_msg: lastMsg }
            }, ( data ) => {
                if( !el.hasClass( 'init' ) ) {
                    el.addClass( 'init' );
                }
                msgs    .append( data.messages );
                lastMsg = data.last_message;
                if( !data.next_page ) {
                    nextPage.addClass( 'hidden' );
                }
            } );
        }

        goto.afterGo( () => {
            goto.removeBeforeGo( 'surveyor_chat' );
            surveyor_chat.default_on_new_messages();
            clearInterval( cInt );
        } );

        surveyor_chat.no_new_messages();
        surveyor_chat.on_new_messages( function() {
            t.cms_simple_call( {
                URL: utils.ajax_url + '&action=chat&type=new-messages'
            }, ( data ) => {
                msgs.prepend( data.messages );
            } );
        } );
        
        load_page();

        return t;
    };

    $.fn.surveyor_chat = function( options ) {
        let option = $.extend( {
            alertEl:    '.chat-alert',
            checkInt:   3000
        }, options );

        let t       = this,
        onNew,
        alertEl     = $( option.alertEl );

        t.no_new_messages = () => {
            if( !alertEl.hasClass( 'a2h' ) ) {
                alertEl.addClass( 'a2h' );
            }
        }

        t.new_messages = () => {
            if( alertEl.hasClass( 'a2h' ) ) {
                alertEl.removeClass( 'a2h' );
            }
        }

        t.on_new_messages = ( fct ) => {
            onNew = fct;
        }

        t.default_on_new_messages = () => {
            onNew = undefined;
        }

        function checkForNewMessages() {
            setInterval( () => {
                $( 'body' ).cms_simple_call( {
                    URL: utils.ajax_url + '&action=chat&type=check-new-messages'
                }, ( data ) => {
                    if( data.new_messages ) {
                        if( onNew ) {
                            onNew.call();
                        } else {
                            t.new_messages()
                        }
                    } else {
                        t.no_new_messages();
                    }
                } );
            }, option.checkInt );
        }

        checkForNewMessages();

        return t;
    };

    surveyor_chat = $( 'body' ).surveyor_chat();

}( jQuery ));
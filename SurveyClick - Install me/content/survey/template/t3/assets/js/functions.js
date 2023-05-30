$(function() {
    "use strict";

    $( document ).on( 'change', '[data-fform]:not(.active) input, [data-fform]:not(.active) textarea, [data-fform]:not(.active) select', function() {
        let form = $(this).closest( '[data-fform]' );
        form.addClass( 'active' );
        form.cms_fform( $(this) );
    });

    $( document ).on( 'input', '.range input[type="range"]', function() {
        let t = $(this);
        let v = t.val();
        t.closest( '.form_line' ).next().find( 'input[type="number"]' ).val( v );
    });

    $( document ).on( 'input', '.range input[type="number"]', function() {
        let t = $(this);
        let v = t.val();
        t.closest( '.form_line' ).prev().find( 'input[type="range"]' ).val( v );
    });

    $( document ).on( 'input', '.checkbox input[type="checkbox"]', function() {
        let t = $(this);
        if( t.is( ':checked' ) ) {
            t.closest( '.checkbox' ).addClass( 'checked' );
        } else {
            t.closest( '.checkbox' ).removeClass( 'checked' );
        }
    });
    
    $( '[data-send-survey]' ).cms_send_survey();

});
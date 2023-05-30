"use strict";

const faqsList = document.querySelectorAll( '.faqs > ul > li > a' );

for ( const faq of faqsList ) {
    faq.addEventListener( 'click', ( e ) => {
        e.preventDefault();
        const listEl = faq.closest( 'li' );
        if( listEl.classList.contains( 'active' ) ) {
            listEl.classList.remove( 'active' );
        } else {
            listEl.classList.add( 'active' );
        }
    });
}
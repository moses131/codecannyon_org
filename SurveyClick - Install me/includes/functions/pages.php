<?php

function page() {
    global $item;
    return $item->object;
}

function content() {
    return page()->getBBText();
}

if( page()->getMetaTitle() !== '' )
filters()->add_filter( 'title_tag', page()->getMetaTitle() );

if( page()->getMetaKeywords() !== '' )
filters()->add_filter( 'keywords_meta', page()->getMetaKeywords() );

if( page()->getMetaDesc() !== '' )
filters()->add_filter( 'description_meta', page()->getMetaDesc() );
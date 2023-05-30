<?php

function homePageFAQs() {
    $lang   = getLanguage();
    $pages  = pages()
            ->setType( 'faqs' )
            ->setLanguage( $lang['locale_e'] )
            ->setMeta( [ 'home'  => 1 ] );
    return $pages;
}

function FAQCategories() {
    $lang   = getLanguage();
    $cats   = categories()
            ->setType( 'faqs' )
            ->setLanguage( $lang['locale_e'] );
    return $cats;
}

function theme_option_lang( string $option, $default = '' ) {
    return get_theme_option( $option . '_' . getUserLanguage( 'locale_e'), $default );
}
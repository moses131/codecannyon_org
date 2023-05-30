<?php

namespace markup\back_end;

class header {

    function __construct() {

        actions()->do_action( 'before_init_admin_header' );

        echo implode( "\n", filters()->do_filter( 'admin_header', [ $this, 'markup' ] ) );
    }

    public function markup() {
        $lang                   = getLanguage();
        $lines                  = [];
        $lines['begin']         = "<!DOCTYPE html>\n<html lang=\"" . $lang['short'] . "\">\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
        $lines['viewport']      = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">";
        $lines['title']         = "<title>" . esc_html( filters()->do_filter( 'title_tag', ( $title = get_option( 'meta_tag_title' ) ) ) ) . "</title>";
        $lines['description']   = "<meta name=\"description\" content=\"" . esc_html( filters()->do_filter( 'description_meta', ( $desc = get_option( 'meta_tag_desc' ) ) ) ) . "\">";
        $lines['keywords']      = "<meta name=\"keywords\" content=\"" . esc_html( filters()->do_filter( 'keywords_meta', get_option( 'meta_tag_keywords' ) ) ) . "\">";
        $lines['og_title']      = "<meta property=\"og:title\" content=\"" . esc_html( filters()->do_filter( 'title_tag', $title ) ) . "\">";
        if( ( $image = filters()->do_filter( 'image_meta', false ) ) )
        $lines['og_image']      = "<meta name=\"og:image\" content=\"" . $image . "\">";
        $lines['og_description']= "<meta property=\"og:description\" content=\"" . esc_html( filters()->do_filter( 'description_meta', $desc ) ) . "\">";
        $lines['robots']        = "<meta name=\"robots\" content=\"" . esc_html( filters()->do_filter( 'robots_meta', get_option( 'meta_index_backend', 'noindex, nofollow' ) ) ) . "\">";

        $favicon                = ( $image = get_option_json( 'front_end_favicon', false ) ) ? current( $image ) : '';
        if( ( $image = filters()->do_filter( 'image_meta', $favicon ) ) )
        $lines['favicon']       = '<link rel="icon" type="image/png" href="' . esc_url( $favicon ) . '" sizes="50x50">';

        if( ( $recaptcha_key = get_option( 'recaptcha_key' ) ) )
        $lines['recaptcha']     = "<script src=\"https://www.google.com/recaptcha/api.js?render=" . esc_html( $recaptcha_key ) . "\"></script>";
        
        if( ( $header_lines = filters()->do_filter( 'in_admin_header', [] ) ) && !empty( $header_lines ) )
        $lines                  = $lines + $header_lines;

        $lines['end']           = "</head>";

        return $lines;
    }

}
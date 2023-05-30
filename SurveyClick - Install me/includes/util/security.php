<?php

namespace util;

class security {

    public static function csrf_token( $token_id = 'csrf_token' ) {
        if( isset( $_SESSION[$token_id] ) ) {
            return md5( $_SESSION[$token_id] );
        }
        return false;
    }

    public static function check_csrf( string $str, $token_id = 'csrf_token' ) {
        if( isset( $_SESSION[$token_id] ) && md5( $_SESSION[$token_id] ) == $str ) {
            return true;
        }
        return false;
    }

}
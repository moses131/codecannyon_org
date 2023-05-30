<?php

namespace util;

class query_util {

    protected function _setSetUser( int $id ) {
        if( $id > 0 ) {
            return $id;
        } else if( $id == 0 && me() ) {
            return me()->getId();
        }
        return false;
    }

}
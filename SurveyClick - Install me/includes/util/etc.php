<?php

namespace util;

class etc {

    public static function check_password( string $password, int $min = 6, int $max = 30 ) {
        $len = strlen( $password );
        if( $len < 6 || $len > 30 ) {
            return false;
        }
        $different = [];
        for( $i = 0; $i < $len; $i++ ) {
            $different[$password[$i]] = '';
        }
        if( count( $different ) < 3 ) {
            return false;
        }
        return true;
    }

    public static function nestedToSingle( array $array, string $k2 = '' ) {
        $a = [];
        foreach( $array as $k => $item ) {
            $x = $k2;
            if( is_array( $item ) ) {
                $y = ( $k2 == '' ? $k : $k2 . '[' . $k . ']');
                $a = array_merge( $a, self::nestedToSingle( $item, $y ) );
            } else {
                $x = ( $x == '' ? $k : $x . '[' . $k . ']');
                $a[$x] = $item;
            }
        }
        return $a;
    }

    public static function searchInArray( array $target, array $search ) {
        foreach( $search as $k => $v ) {
            if( isset( $target[$k] ) ) {
                if( is_array( $v ) ) {
                    return self::searchInArray( $target[$k], $v );
                } else {
                    return $target[$k];
                }
            }
        }
    }

    public static function lastLevelValue( array $array, $value ) {
        $a = [];
        foreach( $array as $k => $v ) {
            if( is_array( $v ) )
                $a[$k] = self::lastLevelValue( $v, $value );
            else
                $a[$k] = $value;
        }
        return $a;
    }

    public static function formFilterOptions() {
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $options );
                if( isset( $options['data'] ) )
                return array_filter( $options['data'], function( $v ) { return $v !== ''; } );
            }
        }
        return [];
    }

    public static function buildFilterOptions() {
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
                return base64_encode( http_build_query( [ 'data' => $_POST['options'] ] ) );
            }
        }
        return '';
    }

    public static function getFilterValue( string $filter ) {
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if( isset( $_POST['options'] ) && isset( $_POST['options'][$filter] ) ) {
                return $_POST['options'][$filter];
            }
        }
        return ;
    }

    public static function monthsList() {
        return [
            1   => t( 'January' ),
            2   => t( 'February' ),
            3   => t( 'March' ),
            4   => t( 'April' ),
            5   => t( 'May' ),
            6   => t( 'June' ),
            7   => t( 'July' ),
            8   => t( 'August' ),
            9   => t( 'September' ),
            10  => t( 'October' ),
            11  => t( 'November' ),
            12  => t( 'December' ),
        ];
    }

    public static function weekDaysList() {
        return [
            0   => t( 'Monday' ),
            1   => t( 'Tuesday' ),
            2   => t( 'Wednesday' ),
            3   => t( 'Thursday' ),
            4   => t( 'Friday' ),
            5   => t( 'Saturday' ),
            6   => t( 'Sunday' )
        ];
    }

    public static function userIP() {
        if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $addr = explode( ",", $_SERVER['HTTP_X_FORWARDED_FOR'] );
            return trim( $addr[0] );
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function userCountry( string $ipAddr = NULL ) {
        if( !$ipAddr )
        $ipAddr = self::userIP();
        return filters()->value( 'ipToCountry', NULL, $ipAddr );
    }

    public static function mibToStr( int $mib, float $precision = 1 ) {
        if( $mib < 1024 ) return $mib . ' MiB';
        
        $gb = $mib / 1024;

        if( $gb < 1024 ) return round( $gb, $precision ) . ' GiB';
        return round( ( $gb / 1024 ), $precision ) . ' TiB';
    }
    
    public static function delete_directory( string $dir ) {
        $files = glob( rtrim( $dir, '/' ) . '/*' );

        foreach( $files as $file ) {
            if( is_dir( $file ) ) {
                self::delete_directory( $file );
            } else {
                unlink( $file );
            }
        }

        return rmdir( $dir );
    }

    public static function filterValues( $values ) {
        foreach ( $values as $key => $item ) {
            is_array( $item ) && $values[$key] = self::filterValues( $item );
            if( $key == '#NEW#' )
            unset( $values[$key] );
        }
        return $values;
    }

}
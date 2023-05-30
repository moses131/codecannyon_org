<?php

namespace util;

class date_time {

    private $date;
    private $timezone;
    private $dateTimeFormat = 'H:i';
    private $dateFormat     = 'm/d/Y';

    function __construct( string $time_str = '' ) {
        $this->date = new \DateTime( $time_str );
    }

    public function getObject() {
        return $this->date;
    }

    public function setTimestamp( int $time ) {
        $this->date = new \DateTime( date( 'Y-m-d\TH:i:sP', $time ) );
        $this->date ->setTimezone( $this->timezone );
        return $this;
    }

    public function setDate( string $time_str ) {
        $this->date = new \DateTime( $time_str );
        $this->date ->setTimezone( $this->timezone );
        return $this;
    }

    public function modifyTimestamp( int $time ) {
        $this->date->modify( date( 'Y-m-d\TH:i:sP', $time ) );
    }

    public function modifyDate( string $time_str ) {
        $this->date->modify( $time_str );
    }

    public function setTimezone( string $timezone ) {
        $this->timezone = new \DateTimeZone( $timezone );
        $this->date     ->setTimezone( $this->timezone );
        return $this;  
    }

    public function currentUser( string $date = '', string $hour = 'H:i', string $timezone = '' ) {
        if( !empty( $date ) )
        $this->dateFormat( $date );

        if( !empty( $hour ) )
        $this->hourFormat( $hour );     

        try {
            $this->setTimezone( $timezone );
        }

        catch( \Exception $e ) {
            $this->useDefaults();
        }

        return $this;
    }

    public function dateFormat( string $format ) {
        $this->dateFormat = $format;
        return $this;
    }

    public function hourFormat( string $format ) {
        if( $format == '12' ) {
            $this->dateTimeFormat = 'g:i A';
        } else if( $format == '24' ) {
            $this->dateTimeFormat = 'H:i';
        } else $this->dateTimeFormat = $format;
        return $this;
    }

    public function useDefaults() {
        $this->dateTimeFormat   = 'H:i';
        $this->dateFormat       = 'm/d/Y';
        $this->timezone         = new \DateTimeZone( DEFAULT_TIMEZONE );
        $this->date             ->setTimezone( $this->timezone );
        return $this;
    }

    public function toServerTime() {
        $this->setTimezone( date_default_timezone_get() );
        return $this;
    }

    public function format( string $format = '' ) {
        if( $format != '' ) {
            return $this->date->format( $format );
        }
        return $this->date->format( $this->dateFormat . ', ' . $this->dateTimeFormat );
    }

    public function formatStr() {
        $diff = $this->date->diff( ( new \DateTime )->setTimezone( $this->timezone ) );
        if( $diff->y > 0 )
        return sprintf( t( '%s y' ), $diff->y );
        else if( $diff->m > 0 )
        return sprintf( t( '%s mo' ), $diff->m );
        else if( $diff->d > 0 )
        return sprintf( t( '%s d' ), $diff->d ); 
        else if( $diff->h > 0 )
        return sprintf( t( '%s h' ), $diff->h ); 
        else if( $diff->i > 0 )
        return sprintf( t( '%s m' ), $diff->i ); 
        
        return sprintf( t( '%s s' ), $diff->s ); 
    }

    public function diffTime( $date_time ) {
        if( is_numeric( $date_time ) ) {
            $date   = new \DateTime;
            $date   ->setTimestamp( $date_time );
            return $this->date->diff( $date );   
        }

        return $this->date->diff( ( new \DateTime( $date_time ) )->setTimezone( $this->timezone ) );   
    }

    public function format2Way( string $format ) {
        $formats    = [];
        $formats[]  = $this->format( $format );
        $formats[]  = $this->formatStr();
        
        return $formats;
    }

    public function getTimestamp() {
        return $this->date->getTimestamp();
    }

}
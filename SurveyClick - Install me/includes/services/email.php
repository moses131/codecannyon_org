<?php

namespace services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class email {

    private $user;
    private $user_obj;
    private $PHPMailer;
    private $templates;
    private $template;

    function __construct( $user = 0 ) {
        $send_via           = get_option( 'email_type' );
        $this->PHPMailer    = new PHPMailer( true );
        $this->templates    = new \site\se_template;

        if( gettype( $user ) == 'object' ) {
            $this->user_obj     = $user;
            $this->user         = $this->user_obj->getId();
        } else if( $user == 0 ) {
            $this->user_obj     = me();
            $this->user         = $this->user_obj->getId();
        } else {
            $this->setUser( $user );
        }

        if( $send_via == 'smtp' ) {
            if( !( $smtp = get_option_array( 'mail_smtp' ) ) || !isset( $smtp['server' ] ) || !isset( $smtp['username'] ) || !isset( $smtp['password'] ) || !isset( $smtp['port'] ) )
            throw new \Exception( t( 'Invalid SMTP options' ) );

            $this->PHPMailer->isSMTP();
            $this->PHPMailer->Host       = $smtp['server'];
            $this->PHPMailer->SMTPAuth   = true;
            $this->PHPMailer->Username   = $smtp['username'];
            $this->PHPMailer->Password   = $smtp['password'];
            $this->PHPMailer->Port       = $smtp['port'];  
        }
    }

    public function setUser( int $user ) {
        $users = users( $user );
        if( $users->getObject() ) {
            $this->user         = $users->getId();
            $this->user_obj     = $users;
        }
        return $this;
    }

    public function getTemplate() {
        return $this->templates;
    }

    public function setTemplate( string $template ) {
        $this->templates->getTemplate( $template );
        return $this;
    }

    public function setShortcodes( array $shortcodes ) {
        $keys   = array_keys( $shortcodes );
        $vals   = array_values( $shortcodes );
        $this->templates->setSubject( str_replace( $keys, $vals, $this->templates->getSubject() ) );
        $this->templates->setBody( str_replace( $keys, $vals, $this->templates->getBody() ) );
        return $this;
    }

    public function useDefaultShortcodes() {
        $shortcodes = [
            '%SITENAME%'    => get_option( 'website_name' ),
            '%SITEURL%'     => site_url(),
            '%ACCOUNTURL%'  => admin_url() 
        ];

        if( $this->user ) {
            $shortcodes['balance'] = $this->user_obj->getBalanceF();
        }

        $this->setShortcodes( $shortcodes );
        return $this;
    }

    public function send( array $addresses = [] ) {
        $this->PHPMailer->setFrom( $this->templates->getFromEmailAddress(), $this->templates->getFromName() );
        $this->PHPMailer->addReplyTo( $this->templates->getFromEmailAddress(), $this->templates->getFromName() );

        if( empty( $addresses ) ) {
            $this->PHPMailer->AddAddress( $this->user_obj->getEmail() );
        } else {
            foreach( $addresses as $address ) {
                $this->PHPMailer->AddAddress( $address );
            }
        }

        $this->PHPMailer->isHTML(true);
        $this->PHPMailer->Subject = $this->templates->getSubject();
        $this->PHPMailer->Body    = $this->templates->getBody();

        try {
            $this->PHPMailer->send();
            return true;
        }

        catch( \Exception $e ) {
            echo 'PHPMailer error: ' . $e->getMessage();
            throw new \Exception( 'PHPMailer error: ' . $e->getMessage() ); 
        }
    }

}
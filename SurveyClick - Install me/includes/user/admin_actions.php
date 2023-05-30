<?php

namespace user;

class admin_actions extends \util\db {

    private $user;
    private $user_obj;

    function __construct( $user ) {
        parent::__construct();

        if( gettype( $user ) == 'object' ) {
            $this->user_obj     = $user;
            $this->user         = $this->user_obj->getId();
        } else if( $user == 0 ) {
            $this->user_obj     = me();
            $this->user         = $this->user_obj->getId();
        } else {
            $this->setUser( $user );
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

    public function save_action( int $to_user, string $type, array $info = [] ) {
        if( !$this->user )
        return ;

        if( !filters()->do_filter( 'custom-error-admin-save-action', true, $this->user ) ) { return ; }
        else {
            $query  = 'INSERT INTO ';
            $query  .= $this->table( 'admin_actions' );
            $query  .= ' (by_user, to_user, text) VALUES (?, ?, ?)';

            $text   = cms_json_encode( array_merge( $info, [ 'type' => $type ] ) );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iis', $this->user, $to_user, $text );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after:save-action-admin', $this->user, $to_user, $type, $info );
                return true;
            }                        
        }

        return false;
    }

    public function remove_admin_action( int $id ) {
        if( !$this->user )
        return ;

        if( !filters()->do_filter( 'custom-error-admin-remove-action', true, $this->user, $id ) ) { return ; }
        else {
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'admin_actions' );
            $query  .= ' WHERE id = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after:remove-action-admin', $this->user, $id );
                return true;
            }                        
        }

        return false;
    }

    public function approve_kyc( int $id ) {
        if( !$this->user )
        return ;

        $intent = new \query\user_intents;
        $intent ->setId( $id );

        if( !$intent->getObject() )
        return ;

        if( !filters()->do_filter( 'custom-error-admin-approve-kyc', true, $this->user, $intent ) ) { return ; }
        else {
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'user_intents' );
            $query  .= ' WHERE id = ?';

            $intent_id  = $intent->getId();
            $user       = $intent->getUser();
            $user_id    = $user->getObject() ? $user->getId() : NULL;

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $intent_id );
            $e = $stmt->execute();
        
            if( $e ) {
                if( $user_id ) {
                    array_map( function( $m_id ) {
                        mediaLinks( $m_id )->deleteItem();
                    }, $intent->getTextJson() );

                    $query  = 'UPDATE ';
                    $query  .= $this->table( 'users' );
                    $query  .= ' SET verified = 1';
                    $query  .= ' WHERE id = ?';
                
                    $stmt = $this->db->stmt_init();
                    $stmt->prepare( $query );
                    $stmt->bind_param( 'i', $user_id );
                    $e = $stmt->execute();

                    if( $e )
                    actions()->do_action( 'after:approve-kyc-admin', $this->user, $user_id );
                }

                $stmt->close();

                return true;
            }
            
            $stmt->close();
        }

        return false;
    }

    public function reject_kyc( int $id ) {
        if( !$this->user )
        return ;

        $intent = new \query\user_intents;
        $intent ->setId( $id );

        if( !$intent->getObject() )
        return ;

        if( !filters()->do_filter( 'custom-error-admin-reject-kyc', true, $this->user, $intent ) ) { return ; }
        else {
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'user_intents' );
            $query  .= ' WHERE id = ?';

            $intent_id  = $intent->getId();
            $user       = $intent->getUser();
            $user_id    = $user->getObject() ? $user->getId() : NULL;

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $intent_id );
            $e = $stmt->execute();
            $stmt->close();

            if( $e ) {
                actions()->do_action( 'after:reject-kyc-admin', $this->user, $user_id );

                array_map( function( $m_id ) {
                    mediaLinks( $m_id )->deleteItem();
                }, (array) $intent->getTextJson() );

                return true;
            }
        }

        return false;
    }

}
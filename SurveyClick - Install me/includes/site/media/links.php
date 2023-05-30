<?php

namespace site\media;

class links extends \util\db {

    private $id;
    private $type;
    private $type_id;

    function __construct( int $id = NULL, int $type_id = NULL ) {
        parent::__construct();
        if( $id ) {
            if( !$type_id )
                $this->id       = $id;
            else {
                $this->type     = $id;
                $this->type_id  = $type_id;
            }
        }
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setType( int $type, int $type_id ) {
        $this->type     = $type;
        $this->type_id  = $type_id;
        return $this;
    }

    public function getItem( int $mediaId = NULL ) {
        if( !$mediaId ) $mediaId = $this->id;
        if( !$mediaId ) return ;

        $query = 'SELECT * FROM ';            
        $query .= $this->table( 'media' );
        $query .= ' WHERE id = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $mediaId );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $mediaQuery = new \query\media;
            $mediaQuery ->setObject( $fields );
            return $mediaQuery;
        }

        return false;
    }

    public function getItemURL( int $mediaId = NULL, bool $notDeleted = true ) {
        if( !$mediaId ) $mediaId = $this->id;
        if( !$mediaId ) return ;

        $query = 'SELECT * FROM ';            
        $query .= $this->table( 'media' );
        $query .= ' WHERE id = ?';
        if( $notDeleted ) {
            $query .= ' AND deleted = 0';
        }
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $mediaId );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return mediaURL( $fields->src, $fields->server );
        }

        return false;
    }

    public function deleteItem( int $mediaId = NULL, int $type = NULL, int $type_id = NULL, int $ownerId = NULL ) {
        if( !$mediaId ) $mediaId = $this->id;
        if( !$mediaId ) return ;

        $query = 'UPDATE ';            
        $query .= $this->table( 'media' );
        $query .= ' SET deleted = 1 WHERE id = ?';
        if( $type )
        $query .= ' AND `type` = ' . $type;
        if( $type_id )
        $query .= ' AND `type_id` = ' . $type_id;
        if( $ownerId )
        $query .= ' AND `owner` = ' . $ownerId;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $mediaId );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

    public function clearDeletedFiles() {
        $query = 'DELETE FROM ';            
        $query .= $this->table( 'media' );
        $query .= ' WHERE deleted = 1';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

    public function deleteMedia( object $media ) {
        $servers    = mediaServers();
        $server     = $servers[$media->server] ?? NULL;

        if( isset( $server ) )
        return call_user_func( $server['delete_file'], $media->src );
    }

}
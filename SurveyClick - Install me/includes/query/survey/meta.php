<?php

namespace query\survey;

class meta extends \util\db {

    private $id;

    function __construct( int $id = 0 ) {
        parent::__construct();
        $this->setId( $id );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function getId() : int {
        return $this->id;
    }

    public function get( string $key, $default = '', $callback = false ) {
        if( ( $value = $this->getValue( $key ) ) !== false ) {
            if( is_callable( $callback ) ) {
                return call_user_func( $callback, $value );
            }
            return $value;
        }

        return $default;
    }

    public function getJson( string $key, $default = '', $callback = false ) {
        if( ( $value = $this->getValue( $key ) ) !== false ) {
            if( is_callable( $callback ) ) {
                return call_user_func( $callback, $value );
            }
            return json_decode( $value, true );
        }

        return $default;
    }

    public function getArray( string $key, $default = '', $callback = false ) {
        if( ( $value = $this->getValue( $key ) ) !== false ) {
            if( is_callable( $callback ) ) {
                return call_user_func( $callback, $value );
            }
            return unserialize( $value );
        }

        return $default;
    }


    public function exists( string $key ) {
        $query = 'SELECT COUNT(*) FROM ';            
        $query .= $this->table( 'survey_meta' );
        $query .= ' WHERE survey = ? AND meta_key = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "is", $this->id, $key );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        if( $count ) {
            return true;
        }

        return false;
    }

    public function getValue( string $key ) {
        $query = 'SELECT meta_value FROM ';            
        $query .= $this->table( 'survey_meta' );
        $query .= ' WHERE survey = ? AND meta_key = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "is", $this->id, $key );
        $stmt->execute();
        $stmt->bind_result( $value );
        $stmt->fetch();
        $stmt->close();

        if( $value !== NULL ) {
            return $value;
        }

        return false;
    }

    public function save( string $key, string $value, string $default = NULL, bool $removeIfExists = false ) {
        if( $default !== NULL && $value == $default ) {
            if( $removeIfExists ) {
                $this->delete( $key );
            }

            return true;
        }

        $query = 'INSERT INTO ';            
        $query .= $this->table( 'survey_meta' );
        $query .= ' (survey, meta_key, meta_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "iss", $this->id, $key, $value );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

    public function delete( string $key ) {
        $query = 'DELETE FROM ';            
        $query .= $this->table( 'survey_meta' );
        $query .= ' WHERE survey = ? AND meta_key = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "is", $this->id, $key );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

}
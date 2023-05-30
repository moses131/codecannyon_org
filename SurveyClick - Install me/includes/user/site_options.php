<?php

namespace user;

class site_options extends \util\db {

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

    function saveOption( string $name, string $value ) {
        if( !$this->user ) {
            throw new \Exception( t( 'Not logged' ) );
        }

        if( ( $errors = filters()->do_filter( 'custom-error-save-website-option', false, $this->user, $name, $value ) ) ) {
            throw new \Exception( $errors );
        } else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'options' );
            $query .= ' (name, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ss', $name, $value );
            $e  = $stmt->execute();
            $a  = $stmt->affected_rows;
            $stmt->close();

            if( $e ) {
                switch( $a ) {
                    case 1:
                        return 'saved';
                    break;

                    default:
                        return 'updated';
                }
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    function deleteOption( string $name ) {
        if( !$this->user ) {
            throw new \Exception( t( 'Not logged' ) );
        }

        if( ( $errors = filters()->do_filter( 'custom-error-delete-website-option', false, $this->user, $name ) ) ) {
            throw new \Exception( $errors );
        } else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'options' );
            $query .= ' WHERE name = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 's', $name );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    function saveThemeOptions( array $options ) {
        if( !$this->user ) {
            throw new \Exception( t( 'Not logged' ) );
        }

        if( ( $errors = filters()->do_filter( 'custom-error-save-theme-option', false, $this->user, $options ) ) ) {
            throw new \Exception( $errors );
        } else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'options' );
            $query .= ' (name, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)';

            $name   = 'to_' . themes()->getThemeName();
            $value  = cms_json_encode( array_merge( get_theme_options(), $options ) );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ss', $name, $value );
            $e  = $stmt->execute();
            $a  = $stmt->affected_rows;
            $stmt->close();
        
            if( $e ) {
                return $a;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

}
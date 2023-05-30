<?php

namespace site;

class options extends \util\db {

    function __construct() {
        parent::__construct();
    }

    public function get_option( string $id, $default = '', $callback = false ) {
        if( ( $value = $this->getOption( $id ) ) !== false ) {
            if( is_callable( $callback ) )
            return call_user_func( $callback, $value );
            return $value;
        }

        return $default;
    }

    public function get_option_array( string $id, $default = [], $callback = false ) {
        if( ( $value = $this->getOption( $id ) ) !== false ) {
            if( is_callable( $callback ) )
                return call_user_func( $callback, $value );
            else if( ( $data = unserialize( $value ) ) )
            return $data;
        }

        return $default;
    }

    public function get_option_json( string $id, $default = [], $callback = false ) {
        if( ( $value = $this->getOption( $id ) ) !== false ) {
            if( is_callable( $callback ) )
                return call_user_func( $callback, $value );
            else if ( $data = json_decode( $value, true ) )
            return $data;
        }

        return $default;
    }

    public function get_options( array $ids, bool $keepKeys = false ) {
        return $this->getOptions( $ids, $keepKeys );
    }

    public function update_option( string $id, string $value ) {
        return $this->updateOption( $id, $value );
    }

    public function save_option( string $id, string $value ) {
        if( !$this->insertOption( $id, $value ) )
        return $this->updateOption( $id, $value );
        return true;
    }

    private function optionExists( string $id ) {
        $query = 'SELECT COUNT(*) FROM ';            
        $query .= $this->table( 'options' );
        $query .= ' WHERE name = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "s", $id );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        if( $count ) {
            return true;
        }

        return false;
    }

    private function getOption( string $id ) {
        $query = 'SELECT content FROM ';            
        $query .= $this->table( 'options' );
        $query .= ' WHERE name = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "s", $id );
        $stmt->execute();
        $stmt->bind_result( $value );
        $stmt->fetch();
        $stmt->close();

        if( $value !== NULL )
        return $value;
        return false;
    }

    private function getOptions( array $ids, bool $keepKeys ) {
        $opts   = '"' . implode( '", "', array_map( [ $this, 'dbp' ], $ids ) ) . '"';
        $query  = 'SELECT name, content FROM ';            
        $query .= $this->table( 'options' );
        $query .= ' WHERE name IN (' . $opts . ')';
        $query .= ' ORDER BY FIELD(name, ' . $opts . ')';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            if( $keepKeys )
            $data[$row['name']]     = $row['content'];
            else 
            $data[]                 = $row['content'];
        }

        $stmt->close();

        return $data;
    }

    private function insertOption( string $id, string $value ) {
        $query = 'INSERT INTO ';            
        $query .= $this->table( 'options' );
        $query .= ' (name, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), content = VALUES(content)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "ss", $id, $value );
        $e = $stmt->execute();
        $stmt->close();

        if( $e )
        return true;
        return false;
    }

    private function updateOption( string $id, string $value ) {
        $query = 'UPDATE ';            
        $query .= $this->table( 'options' );
        $query .= ' SET content = ? WHERE name = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "ss", $value, $id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e )
        return true;
        return false;
    }

}
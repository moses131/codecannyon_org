<?php

namespace user;

class collector_actions extends \util\db {

    private $collector;

    function __construct( int $collector = NULL ) {
        parent::__construct();

        if( $collector )
        $this->setId( $collector );
    }

    public function setId( int $id ) {
        $this->collector = $id;
        return $this;
    }

    public function addOptions( int $type, array $values ) {    
        if( !$this->collector ) return false;

        $query = 'INSERT INTO ';
        $query .= $this->table( 'collector_options' );
        $query .= ' (collector, type, value) VALUES (?, ?, ?)';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );

        foreach( $values as $value ) {
            $stmt->bind_param( 'iii', $this->collector, $type, $value );
            $stmt->execute();
        }

        $stmt->close();

        actions()->do_action( 'after-add-options-collector', $this->collector, $type, $values );

        return true;
    }

    public function removeAllOptions() {    
        if( !$this->collector ) return false;

        $query = 'DELETE FROM ';
        $query .= $this->table( 'collector_options' );
        $query .= ' WHERE collector = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->collector );
        $stmt->execute();
        $stmt->close();

        actions()->do_action( 'after-remove-all-options-collector', $this->collector );

        return true;
    }

    public function removeOptions( int $type, array $values = NULL ) {        
        if( !$this->collector ) return false;

        if( $values !== NULL ) {

            $query = 'DELETE FROM ';
            $query .= $this->table( 'collector_options' );
            $query .= ' WHERE collector = ? AND type = ? AND value = ?';
            
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );

            foreach( $values as $value ) {
                $stmt->bind_param( 'iii', $this->collector, $type, $value );
                $e = $stmt->execute();
            }

        } else {

            $query = 'DELETE FROM ';
            $query .= $this->table( 'collector_options' );
            $query .= ' WHERE collector = ? AND type = ?';
            
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->collector, $type );
            $e = $stmt->execute();
            
        }

        $stmt->close();

        actions()->do_action( 'after-remove-options-collector', $this->collector, $type, $values );
        
        return true;
    }

}
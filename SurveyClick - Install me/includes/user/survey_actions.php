<?php

namespace user;

class survey_actions extends \util\db {

    private $survey;

    function __construct( int $survey = NULL ) {
        parent::__construct();

        if( $survey )
        $this->setId( $survey );
    }

    public function setId( int $id ) {
        $this->survey = $id;
        return $this;
    }

    public function updateStatus( int $status ) {        
        if( !$this->survey ) return false;

        $query = 'UPDATE ';
        $query .= $this->table( 'surveys' );
        $query .= ' SET `status` = ? WHERE id = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $status, $this->survey );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            actions()->do_action( 'after-update-status-survey', $this->survey, $status );
            return true;
        }

        return false;
    }

}
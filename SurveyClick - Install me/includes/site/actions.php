<?php

namespace site;

class actions extends \util\db {

    function __construct() {
        parent::__construct();
    }

    public function add_user_loyalty_points( int $user, float $points ) {
        $query = 'UPDATE ';            
        $query .= $this->table( 'users' );
        $query .= ' SET lpoints = lpoints + ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "di", $points, $user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

}
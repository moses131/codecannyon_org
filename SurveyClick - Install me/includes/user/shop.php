<?php

namespace user;

class shop extends \util\db {

    private $user;
    private $user_obj;
    private $cart_items;

    function __construct( $user = 0 ) {
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

    public function addItem( int $item_id, int $qt = 1 ) {
        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'shop_cart' );
        $query  .= ' (user, item, qt) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE qt = VALUES(qt)';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'iii', $this->user, $item_id, $qt );
        $e      = $stmt->execute();
        $a      = $stmt->affected_rows;
        $stmt   ->close();

        if( $e ) {
            return $a;
        }

        return false;
    }

    public function removeItem( int $item_id ) {
        $query  = 'DELETE FROM ';
        $query  .= $this->table( 'shop_cart' );
        $query  .= ' WHERE user = ? AND item = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'ii', $this->user, $item_id );
        $e      = $stmt->execute();
        $a      = $stmt->affected_rows;
        $stmt   ->close();

        return $a;
    }

    public function cartCount() {
        if( $this->cart_items === NULL )
        $this->cart_items = $this->items();

        return count( $this->cart_items );
    }

    public function cartHasItem( int $item_id ) {
        if( $this->cart_items === NULL )
        $this->cart_items = $this->items();

        if( isset( $this->cart_items[$item_id] ) ) {
            return $this->cart_items[$item_id];
        }

        return false;
    }

    public function clearCart() {
        $query  = 'DELETE FROM ';
        $query  .= $this->table( 'shop_cart' );
        $query  .= ' WHERE user = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'i', $this->user );
        $e      = $stmt->execute();
        $stmt   ->close();

        return $e;
    }

    public function addOrder( string $summary, float $total, int $status = 1 ) {
        if( $total > $this->user_obj->getLoyaltyPoints() )
        return ;

        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'shop_orders' );
        $query  .= ' (user, summary, total, status) VALUES (?, ?, ?, ?)';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'isdi', $this->user, $summary, $total, $status );
        $e      = $stmt->execute();
        $id     = $stmt->insert_id;
        $stmt   ->close();

        if( $e ) {
            $this->clearCart();
            return $id;
        }

        return false;
    }

    public function items() {
        $this->cart_items = [];

        $query  = 'SELECT * FROM ';
        $query  .= $this->table( 'shop_cart' ); 
        $query  .= ' WHERE user = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'i', $this->user );
        $stmt   ->execute();
        $result = $stmt->get_result();

        while( ( $row = $result->fetch_assoc() ) ) {
            $this->cart_items[$row['item']]   = $this->filters->do_filter( 'shop_items_info_values', (object) $row );
        }

        $stmt->close();

        return $this->cart_items;
    }

    public function items2() {
        $this->cart_items = [];

        $query  = 'SELECT sc.*, sc.id as sc_id, se.name, se.price FROM ';
        $query  .= $this->table( 'shop_cart sc' );
        $query  .= ' LEFT JOIN ';
        $query  .= $this->table( 'shop_items se' );
        $query  .= ' ON se.id = sc.item';
        $query  .= ' WHERE sc.user = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'i', $this->user );
        $stmt   ->execute();
        $result = $stmt->get_result();

        while( ( $row = $result->fetch_assoc() ) ) {
            $this->cart_items[$row['sc_id']]   = $this->filters->do_filter( 'shop_items_info_values', (object) $row );
        }

        $stmt->close();

        return $this->cart_items;
    }

}
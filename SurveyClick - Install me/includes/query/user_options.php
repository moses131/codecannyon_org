<?php

namespace query;

class user_options extends \util\db {

    protected $user_id;
    protected $session_id;
    protected $info;
    protected $preferences;
    protected $orderby          = [];
    protected $items_per_page   = 10;
    protected $current_page     = false;
    protected $pagination       = [];
    protected $count            = false;
    // db query
    protected $select           = '*';
    protected $selectKey        = 'id';

    function __construct( int $user_id = 0 ) {
        parent::__construct();

        $this->setUserId( $user_id );
        $this->orderby  = $this->filters->do_filter( 'user_options_default_order_by', [ '' ] );
    }

    public function setUserId( int $user_id ) {
        $this->user_id = $user_id;
        $this->conditions['user'] = [ 'user', '=', $user_id ];
        return $this;
    }

    public function setObject( $info ) {
        $this->info = (object) $info;
        return $this;
    }

    public function getName() {
        return $this->info->name;
    }

    public function getValue() {
        if( !isset( $this->info->content ) ) {
            return NULL;
        }
        return $this->info->content;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['name']           = 'name';
        $list['name_desc']      = 'name DESC';

        return $this->filters->do_filter( 'user_options_order_by_values', $list );
    }

    public function orderBy( $values ) {
        if( is_string( $values ) ) {
            $values = [ $values ];
        }
        $this->orderby = array_intersect( $values, array_keys( $this->orderBy_values() ) );
        return $this;
    }

    private function setPagination( $pagination ) {
        $this->pagination = $pagination;
        return $this;
    }

    public function getPagination() {
        return $this->pagination;
    }

    public function setPage( int $page ) {
        $this->current_page = $page;
        return $this;
    }

    public function setItemsPerPage( int $items = 10 ) {
        $this->items_per_page = $items;
        return $this;
    }

    public function itemsPerPage() {
        return $this->filters->do_filter( 'user_options_per_page', $this->items_per_page );
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'user_options_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'user_options' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'user_options_count', $count );

        return false;
    }

    // Get information as object
    public function info( string $name, int $user_id = 0 ) {
        if( empty( $user_id ) ) {
            $user_id = $this->user_id;
        }

        $query = 'SELECT * FROM ';
        $query .= $this->table( 'user_options' );
        $query .= ' WHERE user = ? AND name = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'is', $user_id, $name );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'user_options_info_values', $fields );
        }

        return false;
    }

    // Get information as object and store it in info
    public function infoSave( string $name, int $user_id = 0 ) {
        $this->info = $this->info( $name, $user_id );

        return $this->info;
    }

    // Fetch entries
    public function fetch( int $max = 0, int $offset = 0 ) {
        $limit = '';
        
        if( $max != 0 ) {
            if( $max > 0 )
            $limit = ' LIMIT ' . ( $offset ? $offset . ',' : '' ) . $max;
        } else {
            $count = $this->count();
                
            if( !$count ) {
                return [];
            }

            $items_per_page = $this->itemsPerPage();

            if( $items_per_page ) {
                $per_page       = $this->itemsPerPage();
                $total_pages    = ceil( $count / $per_page );
                $current_page   = ( $this->current_page !== false ? $this->current_page : ( !empty( $_GET['page'] ) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1 ) );
                $current_page   = min( $current_page, $total_pages );

                $this->pagination = [
                    'items_per_page'=> $per_page,
                    'total_pages'   => $total_pages,
                    'current_page'  => $current_page
                ];

                $this->setPagination( $this->pagination );

                $limit = ' LIMIT ' . ( ( $current_page - 1 ) * $per_page ) . ', ' . $per_page;
            }
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'user_options' ); 
        $query .= $this->finalCondition();

        if( !empty( $this->orderby ) ) {
            $order  = array_flip( $this->orderby );
            $query .= ' ORDER BY ' . implode( ', ', array_intersect_key( array_replace( $order, $this->orderBy_values() ), $order ) );
        }

        $query .= $limit;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            if( $this->selectKey )
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'user_options_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'user_options_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }
}
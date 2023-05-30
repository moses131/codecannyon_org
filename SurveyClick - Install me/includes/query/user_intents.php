<?php

namespace query;

class user_intents extends \util\db {

    private $id;
    private $user_id;
    private $session_id;
    protected $info;
    private $preferences;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = '*';
    protected $selectKey    = 'id';

    function __construct( int $user_id = NULL ) {
        parent::__construct();

        if( $user_id )
        $this->setUserId( $user_id );
        $this->orderby  = $this->filters->do_filter( 'user_intents_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $user_id ) {
        $this->user_id = $user_id;
        $this->conditions['user'] = [ 'user', '=', $user_id ];
        return $this;
    }

    public function setTypeId( int $id ) {
        $this->conditions['type'] = [ 'type', '=', $id ];
        return $this;
    }

    public function setStatus( int $id ) {
        $this->conditions['status'] = [ 'status', '=', $id ];
        return $this;
    }

    public function setObject( $info ) {
        $this->info = (object) $info;
        return $this;
    }

    public function getObject() {
        if( empty( $this->info ) ) {
            if( $this->id ) $this->info = $this->infoFromId();
            else $this->info = $this->info();
        }
        return $this->info;
    }

    public function getId() : int {
        return ( $this->info->id ?? $this->id );
    }

    public function getName() {
        return $this->info->name;
    }

    public function getTypeId() {
        return $this->info->type;
    }

    public function getText() {
        return $this->info->text;
    }

    public function getTextJson() {
        return ( $this->info->text ? json_decode( $this->info->text, true ) : [] );
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        if( !isset( $this->info->user ) ) return false;
        $users  = new \query\users;
        $users  ->setId( $this->info->user );
        return $users;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['status']         = 'status';
        $list['status_desc']    = 'status DESC';

        return $this->filters->do_filter( 'user_intents_order_by_values', $list );
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
        return $this->filters->do_filter( 'user_intents_per_page', $this->items_per_page );
    }

    public function pagination() {
        if( !$this->count ) {
            return false;
        }
        $pagination = new \markup\front_end\pagination( 
            $this->pagination['total_pages'], 
            $this->pagination['items_per_page'], 
            $this->pagination['current_page'] 
        );
        return $pagination;
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'user_intents_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'user_intents' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'user_intents_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $type, int $user_id = 0 ) {
        if( empty( $user_id ) ) {
            $user_id = $this->user_id;
        }

        $query = 'SELECT * FROM ';
        $query .= $this->table( 'user_intents' );
        $query .= ' WHERE user = ? AND type = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $user_id, $type );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'user_intents_info_values', $fields );
        }

        return false;
    }

    public function infoFromId( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'user_intents' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'user_intents_info_values', $fields );
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
        $query .= $this->table( 'user_intents' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'user_intents_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'user_intents_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }
}
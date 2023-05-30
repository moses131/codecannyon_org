<?php

namespace query;

class vouchers extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = '*';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'vouchers_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'user', '=', $id ];
        return $this;
    }

    public function setAllUsers() {
        $this->conditions['user'] = [ 'user', 'IS NULL', '' ];
        return $this;
    }

    public function setCode( string $code ) {
        $this->conditions['code'] = [ 'MATCH(code)', 'AGAINST', '*' . $code . '*' ];
        return $this;
    }

    public function setExpired() {
        $this->conditions['expiration'] = [ [ 'expiration', 'IS NOT NULL', '' ], 'AND', [ 'expiration', '<', [ 'NOW()' ] ] ];
        return $this;
    }

    public function setNotExpired() {
        $this->conditions['expiration'] = [ [ 'expiration', 'IS NULL', '' ], 'OR', [ 'expiration', '>=', [ 'NOW()' ] ] ];
        return $this;
    }

    public function setStatus( int $status, string $op = '=' ) {
        $this->conditions['status'] = [ 'status', $op, $status ];
        return $this;
    }

    public function setType( int $type, string $op = '=' ) {
        $this->conditions['type'] = [ 'type', $op, $type ];
        return $this;
    }

    public function setAmountType( int $type, string $op = '=' ) {
        $this->conditions['a_type'] = [ 'a_type', $op, $type ];
        return $this;
    }

    public function setLimit( int $limit, string $op = '=' ) {
        $this->conditions['limit'] = [ 'limit', $op, $limit ];
        return $this;
    }

    public function search( string $code ) {
        if( $code !== '' ) {

            filters()->add_filter( 'pages_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(code) AGAINST ("*' . $this->dbp( $code ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_code'] = [ 'MATCH(code)', 'AGAINST', '*' . $code . '*' ];
        }
        return $this;
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject() {
        if( empty( $this->info ) ) {
            $this->info = $this->info();
        }
        return $this->info;
    }

    public function getId() : int {
        return ( $this->info->id ?? $this->id );
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getCode() {
        return $this->info->code;
    }

    public function getType() {
        return $this->info->type;
    }

    public function getAmount() {
        return $this->info->amount;
    }

    public function getAmountType() {
        return $this->info->a_type;
    }

    public function getLimit() {
        return $this->info->limit;
    }

    public function getStatus() {
        return $this->info->status;
    }

    public function getExpiration() {
        return $this->info->expiration;
    }

    public function getTitle() {
        switch( $this->info->a_type ) {
            // money
            case 0:
                return cms_money_format( $this->info->amount );
            break;

            // percent
            case 1:
                return sprintf( '%s&percnt; extra', $this->info->amount );
            break;
        }
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        $users  = new users;
        $users  ->setId( $this->info->user );
        return $users;
    }

    private function orderBy_values() {
        $list                       = [];
        $list['id']                 = 'id';
        $list['id_desc']            = 'id DESC';
        $list['expiration']         = 'expiration';
        $list['expiration_desc']    = 'expiration DESC';

        return $this->filters->do_filter( 'vouchers_order_by_values', $list );
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
        return $this->filters->do_filter( 'vouchers_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'vouchers_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'vouchers' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'vouchers_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'vouchers' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'vouchers_info_values', $fields );
        }

        return false;
    }

    // Fetch entries
    public function fetch( int $max = 0, bool $pagination = true ) {
        if( $max && $pagination ) {
            $this->count = $max;
        }

        $count = $this->count();

        if( !$count ) {
            return [];
        }

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

        $limit          = '';
        
        if( $max === 0 || ( $max > 0 && $pagination ) ) {
            $limit = ' LIMIT ' . ( ( $current_page - 1 ) * $per_page ) . ', ' . $per_page;
        } else if( $max > 0 && !$pagination ) {
            $limit = ' LIMIT ' . $max;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'vouchers' );
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'vouchers_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'vouchers_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
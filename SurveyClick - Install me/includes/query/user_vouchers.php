<?php

namespace query;

class user_vouchers extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select         = 'uv.id as uv_id, uv.uses as uv_uses, uv.date as uv_date, v.*';
    protected $selectKey    = 'uv_id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'user_vouchers_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'uv.user', '=', $id ];
        return $this;
    }

    public function setVoucherId( int $id ) {
        $this->conditions['voucher'] = [ 'uv.voucher', '=', $id ];
        return $this;
    }

    public function setCode( string $code ) {
        $this->conditions['code'] = [ 'MATCH(v.code)', 'AGAINST', '*' . $code . '*' ];
        return $this;
    }

    public function setExpired() {
        $this->conditions['expiration'] = [ [ 'v.expiration', 'IS NULL', '' ], 'OR', [ 'v.expiration', '<', [ 'NOW()' ] ] ];
        return $this;
    }

    public function setNotExpired() {
        $this->conditions['expiration'] = [ [ 'v.expiration', 'IS NULL', '' ], 'OR', [ 'v.expiration', '>=', [ 'NOW()' ] ] ];
        return $this;
    }

    public function setUsed() {
        $this->conditions['used'] = [ [ 'v.limit', 'IS NOT NULL', '' ], 'OR', [ 'uv.uses', '>=', [ 'NOQ', 'v.limit' ] ] ];
        return $this;
    }

    public function setNotUsed() {
        $this->conditions['used'] = [ [ 'v.limit', 'IS NULL', '' ], 'OR', [ 'uv.uses', '<', [ 'NOQ', 'v.limit' ] ] ];
        return $this;
    }

    public function setStatus( int $status, string $op = '=' ) {
        $this->conditions['status'] = [ 'v.status', $op, $status ];
        return $this;
    }

    public function setType( int $type, string $op = '=' ) {
        $this->conditions['type'] = [ 'v.type', $op, $type ];
        return $this;
    }

    public function setAmountType( int $type, string $op = '=' ) {
        $this->conditions['a_type'] = [ 'v.a_type', $op, $type ];
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
        return $this->info->uv_id;
    }

    public function getUserId() {
        return $this->info->us_user;
    }

    public function getSurveyId() {
        return $this->info->us_name;
    }

    public function getPerm() {
        return $this->info->us_perm;
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
        return $this->info->uv_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        $users  = new users;
        $users  ->setId( $this->info->us_user );
        return $users;
    }

    public function getSurvey() {
        $surveys  = new surveys;
        $surveys  ->setObject( $this->info );
        return $surveys;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'id';
        $list['id_desc']    = 'id DESC';

        return $this->filters->do_filter( 'user_vouchers_order_by_values', $list );
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
        return $this->filters->do_filter( 'user_vouchers_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'user_vouchers_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'vouchers v' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'user_vouchers uv' );
        $query .= ' ON (v.id = uv.voucher_id AND (v.user IS NULL OR v.user = uv.user))';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'user_vouchers_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'vouchers v' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'user_vouchers uv' );
        $query .= ' ON (v.id = uv.voucher_id AND (v.user IS NULL OR v.user = uv.user))';
        $query .= ' WHERE us.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'user_vouchers_info_values', $fields );
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
        $query .= $this->table( 'vouchers v' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'user_vouchers uv' );
        $query .= ' ON (v.id = uv.voucher_id AND (v.user IS NULL OR v.user = uv.user))';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'user_vouchers_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'user_vouchers_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
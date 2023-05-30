<?php

namespace query;

class subscriptions extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select         = 's.*, p.name as p_name, p.price as p_price';
    protected $selectKey      = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'subscriptions_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'user', '=', $id ];
        return $this;
    }

    public function setPlanId( int $id ) {
        $this->conditions['plan'] = [ 'plan', '=', $id ];
        return $this;
    }

    public function setIsPaid() {
        $this->conditions['paid'] = [ 'paid', '>', 0 ];
        return $this;
    }

    public function selectDistinctCategory() {
        $this->selectKey= 'plan';
        $this->select   = 'DISTINCT(plan)';
        return $this;
    }

    public function setExpired() {
        $this->conditions['expiration'] = [ 'expiration', '<', [ 'NOW()' ] ];
        return $this;
    }

    public function setExpiration( int $value, string $interval = 'DAY', string $op = '<' ) {
         $this->conditions['expiration'] = [ 'expiration', $op, [ 'DATE_ADD', 'NOW()', $value, $interval ] ];
        return $this;
    }

    public function setNotExpired() {
        $this->conditions['not_expired'] = [ 'expiration', '>', [ 'NOW()' ] ];
       return $this;
   }

   public function setAutorenew() {
        $this->conditions['autorenew'] = [ 'autorenew', '>', 0 ];
        return $this;
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject() {
        if( empty( $this->info ) )
        $this->info = $this->info();
        return $this->info;
    }

    public function getObjectFromUserSubscription( int $user ) {
        if( empty( $this->info ) )
        $this->info = $this->infoFromUserSubscription( $user );
        return $this->info;
    }

    public function getId() : int {
        return $this->info->id;
    }

    public function getName() {
        return $this->info->p_name;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getPlanId() {
        return $this->info->plan;
    }

    public function getPlanName() {
        return $this->info->p_name;
    }

    public function getPlanPrice() {
        return $this->info->p_price;
    }

    public function getExpiration() {
        return $this->info->expiration;
    }

    public function getAutorenew() {
        return $this->info->autorenew;
    }

    public function getRenewCount() {
        return $this->info->rcount;
    }

    public function getLastRenew() {
        return $this->info->last_renew;
    }

    public function getInfo() {
        return $this->info->info;
    }

    public function getInfoJson() {
        return ( $this->info->info ? json_decode( $this->info->info, true ) : [] );
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

    public function getPlan() {
        if( !isset( $this->info->plan ) ) return false;
        $plans  = new \query\plans\plans;
        $plans  ->setId( $this->info->plan );
        return $plans;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'id';
        $list['id_desc']    = 'id DESC';
        $list['lr']         = 'last_renew';
        $list['lr_desc']    = 'last_renew DESC';
        $list['expiration']     = 'expiration';
        $list['expiration_desc']= 'expiration DESC';
        $list['date']       = 'date';
        $list['date_desc']  = 'date DESC';

        return $this->filters->do_filter( 'subscriptions_order_by_values', $list );
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
        return $this->filters->do_filter( 'subscriptions_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'subscriptions_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'subscriptions' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'subscriptions_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) )
        $id = $this->id;

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'subscriptions s' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'plans p' );
        $query .= ' ON (p.id = s.plan) ';
        $query .= ' WHERE s.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->filters->do_filter( 'subscriptions_info_values', $fields );
        return false;
    }

    // Get information as object from user subscription
    public function infoFromUserSubscription( int $user ) {
        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'subscriptions s' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'plans p' );
        $query .= ' ON (p.id = s.plan) ';
        $query .= ' WHERE s.user = ? AND s.paid > 0';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $user );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->filters->do_filter( 'subscriptions_info_values', $fields );
        return false;
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
        $query .= $this->table( 'subscriptions s' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'plans p' );
        $query .= ' ON (p.id = s.plan) ';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'subscriptions_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'subscriptions_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
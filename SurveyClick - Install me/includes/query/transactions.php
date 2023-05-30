<?php

namespace query;

class transactions extends \util\db {

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
        $this->orderby  = $this->filters->do_filter( 'transactions_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'user', '=', $id ];
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'survey', '=', $id ];
        return $this;
    }

    public function setTypeId( int $id ) {
        $this->conditions['type'] = [ 'type', '=', $id ];
        return $this;
    }

    public function setTypeIdIN( array $ids ) {
        $this->conditions['type'] = [ 'type', 'IN', $ids ];
        return $this;
    }

    public function setStatus( int $id ) {
        $this->conditions['status'] = [ 'status', '=', $id ];
        return $this;
    }

    public function searchLicense( string $license ) {
        if( $license !== '' ) {

            filters()->add_filter( 'transactions_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(license) AGAINST ("*' . $this->dbp( $license ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_license'] = [ 'MATCH(license)', 'AGAINST', '*' . $license . '*' ];
        }
        return $this;
    }

    public function setTransactionId( string $id ) {
        $this->conditions['status'] = [ 'status', '=', $id ];
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

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getTypeId() {
        return $this->info->type;
    }

    public function getAmount() {
        return $this->info->amount;
    }

    public function getAmountF() {
        return cms_money_format( $this->info->amount );
    }

    public function getDetails() {
        return $this->info->details;
    }

    public function getDetailsJD() {
        return ( !empty( $this->info->details ) ? json_decode( $this->info->details, true ) : [] );
    }

    public function getStatus() {
        return $this->info->status;
    }

    public function getLicense() {
        return $this->info->license;
    }

    public function getTransactionId() {
        return $this->info->transactionId;
    }

    public function getStatusMarkup() {
        switch( $this->info->status ) {
            case 0:
                return '<div class="tst"><div class="mmsg failed">' . t( 'Canceled' ) . '</div><div></div></div>';
            break;

            case 1:
                return '<div class="tst"><div class="mmsg onhold">' . t( 'Pending' ) . '</div><div></div></div>';
            break;

            case 2:
                return '<div class="tst"><div class="mmsg completed">' . t( 'Completed' ) . '</div><div></div></div>';
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
        if( !isset( $this->info->user ) ) return false;
        $users  = new \query\users;
        $users  ->setId( $this->info->user );
        return $users;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';

        return $this->filters->do_filter( 'transactions_order_by_values', $list );
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
        $this->current_page = max( 1, $page );
        return $this;
    }

    public function setItemsPerPage( int $items = 10 ) {
        $this->items_per_page = $items;
        return $this;
    }

    public function itemsPerPage() {
        return $this->filters->do_filter( 'transactions_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'transactions_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'transactions' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'transactions_count', $count );

        return false;
    }

    public function transactionInfo( string $id ) {
        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'transactions' );
        $query .= ' WHERE transactionId = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 's', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'transactions_info_values', $fields );
        }

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'transactions' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'transactions_info_values', $fields );
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
        $query .= $this->table( 'transactions' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'transactions_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'transactions_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
<?php

namespace query\survey;

class latest_results extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false; 
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select         = 's.*, r.id as r_id, r.user as r_user, r.visitor as r_ip_addr, r.status as r_status, r.collector as r_collector, r.country as r_country, (r.fin - r.date) as r_finish, r.date as r_date';
    protected $selectKey    = 'r_id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'latest_results_survey_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'us.user', '=', $id ];
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'us.survey', '=', $id ];
        return $this;
    }

    public function setLastAnswer( int $type, string $sign = '>' ) {
        $this->conditions['us_last_change'] = [ 'us.last_change', $sign, [ 'FROM_UNIXTIME', $time] ];
        $this->conditions['r_date'] = [ 'r.date', $sign, [ 'FROM_UNIXTIME', $time ] ];
        return $this;
    }

    public function setLast12hours() {
        $time = strtotime( '-12 hours' );
        $this->conditions['us_last_change'] = [ 'us.last_change', '>=', [ 'FROM_UNIXTIME', $time ] ];
        $this->conditions['r_date'] = [ 'r.date', '>=', [ 'FROM_UNIXTIME', $time ] ];
        return $this;
    }

    public function setLast24hours() {
        $time = strtotime( '-24 hours' );
        $this->conditions['us_last_change'] = [ 'us.last_change', '>=', [ 'FROM_UNIXTIME', strtotime( '-24 hours' ) ] ];
        $this->conditions['r_date'] = [ 'r.date', '>=', [ 'FROM_UNIXTIME', $time ] ];
        return $this;
    }

    public function setLast48hours() {
        $time = strtotime( '-48 hours' );
        $this->conditions['us_last_change'] = [ 'us.last_change', '>=', [ 'FROM_UNIXTIME', strtotime( '-48 hours' ) ] ];
        $this->conditions['r_date'] = [ 'r.date', '>=', [ 'FROM_UNIXTIME', $time ] ];
        return $this;
    }

    public function setLast7Days() {
        $time = strtotime( '-7 days' );
        $this->conditions['us_last_change'] = [ 'us.last_change', '>=', [ 'FROM_UNIXTIME', strtotime( '-7 days' ) ] ];
        $this->conditions['r_date'] = [ 'r.date', '>=', [ 'FROM_UNIXTIME', $time ] ];
        return $this;
    }

    public function setStatus( int $id, string $sign = '>' ) {
        $this->conditions['status'] = [ 'r.status', $sign, $id ];
        return $this;
    }

    public function setCollectorId( int $id ) {
        $this->conditions['collector'] = [ 'r.collector', '=', $id ];
        return $this;
    }

    public function search( string $name ) {
        if( $name !== '' ) {

            filters()->add_filter( 'latest_results_survey_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select = $this->select . ', MATCH(s.name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_name'] = [ 'MATCH(s.name)', 'AGAINST', '*' . $name . '*' ];
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
        return $this->info->r_id;
    }

    public function getUserId() {
        return $this->info->r_user;
    }

    public function getUserIpAddress() {
        return $this->info->r_ip_addr;
    }

    public function getStatus() {
        return $this->info->r_status;
    }

    public function getCollectorId() {
        return $this->info->r_collector;
    }

    public function getCountry() {
        return $this->info->r_country;
    }

    public function getFinishSeconds() {
        return $this->info->r_finish;
    }
    
    public function getDate() {
        return $this->info->r_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        $users  = new users;
        $users  ->setId( $this->info->r_user );
        return $users;
    }

    public function getSurveyObject( $info = NULL ) {
        $surveys    = new surveys;
        if( !empty( $info ) ) {
            $surveys->setObject( $info );
        } else {
            $surveys->setObject( $this->info );
        }
        return $surveys;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'r.id';
        $list['id_desc']        = 'r.id DESC';
        $list['date']           = 'r.date';
        $list['date_desc']      = 'r.date DESC';

        return $this->filters->do_filter( 'latest_results_survey_order_by_values', $list );
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
        return $this->filters->do_filter( 'latest_results_survey_per_page', $this->items_per_page );
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'latest_results_survey_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'results r' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' ON r.survey = us.survey';
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'surveys s' );
        $query .= ' ON s.id = us.survey';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'latest_results_survey_count', $count );

        return false;
    }

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
        $query .= $this->table( 'results r' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' ON r.survey = us.survey';
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'surveys s' );
        $query .= ' ON s.id = us.survey';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'latest_results_survey_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'latest_results_survey_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
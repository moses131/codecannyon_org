<?php

namespace query\survey;

class response_owner extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false; 
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'r.*, us.user as us_user, us.team as us_team';
    protected $selectKey    = 'r_id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'response_owner_survey_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'r.user', '=', $id ];
        return $this;
    }

    public function setVisitorIp( string $id ) {
        $this->conditions['visitor'] = [ 'r.visitor', '=', $id ];
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'r.survey', '=', $id ];
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

    public function isTeamSurvey() {
        return (bool) $this->info->us_team;
    }

    public function getTeamId() {
        return $this->info->us_team;
    }

    public function getOwnerId() {
        return $this->info->us_user;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getOwner() {
        $users  = new users;
        $users  ->setId( $this->info->us_user );
        return $users;
    }

    public function getResultObject( $info = NULL ) {
        $results    = new results;
        if( !empty( $info ) ) {
            $results->setObject( $info );
        } else {
            $results->setObject( $this->info );
        }
        return $results;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'r.id';
        $list['id_desc']        = 'r.id DESC';
        $list['date']           = 'r.date';
        $list['date_desc']      = 'r.date DESC';

        return $this->filters->do_filter( 'response_owner_survey_order_by_values', $list );
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
        return $this->filters->do_filter( 'response_owner_survey_per_page', $this->items_per_page );
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'response_owner_survey_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'results r' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' ON r.survey = us.survey';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'response_owner_survey_count', $count );

        return false;
    }

    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'results r' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' ON r.survey = us.survey';
        $query .= ' WHERE r.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'response_owner_survey_info_values', $fields );
        }

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'response_owner_survey_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'response_owner_survey_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
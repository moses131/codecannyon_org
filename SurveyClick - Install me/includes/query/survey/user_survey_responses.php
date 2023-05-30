<?php

namespace query\survey;

class user_survey_responses extends \util\db {

    private $id;
    private $showCountry;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select         = 'r.*, us.id as us_id, us.team as us_team, us.last_change as us_last_change, us.date as us_date';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'user_survey_responses_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'us.user', '=', $id ];
        return $this;
    }

    public function setTeamId( int $id ) {
        $this->conditions['team'] = [ 'us.team', '=', $id ];
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'us.survey', '=', $id ];
        return $this;
    }

    public function setNullTeamId() {
        $this->conditions['team'] = [ 'us.team', 'IS NULL', '' ];
        return $this;
    }

    public function setStatus( int $status, string $op = '=' ) {
        $this->conditions['status'] = [ 'r.status', $op, $status ];
        return $this;
    }

    public function setCountry() {
        $this->select       .= ', c.iso_3166 as c_iso_3166, c.name as c_name';
        $this->showCountry  = true;
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
        return $this->info->us_id;
    }

    public function getTeamId() {
        return $this->info->us_team;
    }

    public function getLastChange() {
        return $this->info->us_last_change;
    }

    public function getDate() {
        return $this->info->us_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getResponseObject( $info = NULL ) {
        $results    = new results;
        if( !empty( $info ) ) {
            $results->setObject( $info );
        } else {
            $results->setObject( $this->info );
        }
        return $results;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'us.id';
        $list['id_desc']    = 'us.id DESC';
        $list['lc']         = 'us.last_change';
        $list['lc_desc']    = 'us.last_change DESC';
        $list['date']       = 'us.date';
        $list['date_desc']  = 'us.date DESC';

        return $this->filters->do_filter( 'user_survey_responses_order_by_values', $list );
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
        return $this->filters->do_filter( 'user_survey_responses_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'user_survey_responses_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'results r' );
        $query .= ' ON (us.survey = r.survey)';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'user_survey_responses_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'results r' );
        $query .= ' ON (us.survey = r.survey)';
        $query .= ' WHERE us.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'user_survey_responses_info_values', $fields );
        }

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
        $query .= $this->table( 'usr_surveys us' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'results r' );
        $query .= ' ON (us.survey = r.survey)';

        if( $this->showCountry ) {
            $query .= ' LEFT JOIN ';
            $query .= $this->table( 'countries c' );
            $query .= ' ON c.iso_3166 = r.country';
        }

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'user_survey_responses_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'user_survey_responses_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
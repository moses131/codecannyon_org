<?php

namespace query;

class saved extends \util\db {

    private $id;
    private $user;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select         = 'sv.user as sv_user, sv.survey as sv_survey, sv.collector as sv_collector, sv.date as sv_date, s.*';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();
        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'saved_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUser( int $id ) {
        $this->user = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'sv.user', '=', $id ];
        return $this;
    }

    public function search( string $name ) {
        if( $name !== '' ) {

            filters()->add_filter( 'saved_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(s.name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
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
        return ( $this->info->id ?? $this->id );
    }

    public function getUserId() {
        return $this->info->f_user;
    }

    public function getSurveyId() {
        return $this->info->f_survey;
    }

    public function getDate() {
        return $this->info->f_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $users  = new users;
        $users  ->setId( $this->info->sv_user );
        return $users;
    }

    public function getSurveyObject( $info = NULL ) {
        $surveys    = surveys();
        if( !empty( $info ) ) {
            $surveys->setObject( $info );
        } else {
            $surveys->setObject( $this->info );
        }
        return $surveys;
    }

    public function getCollector() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $collectors  = new collectors;
        $collectors  ->setId( $this->info->sv_collector );
        return $collectors;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'sv.id';
        $list['id_desc']    = 'sv.id DESC';
        $list['date']       = 'sv.date';
        $list['date_desc']  = 'sv.date DESC';

        return $this->filters->do_filter( 'saved_order_by_values', $list );
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
        return $this->filters->do_filter( 'saved_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'saved_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'saved sv' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = sv.survey)';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'saved_count', $count );
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'saved sv' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = sv.survey)';
        $query .= ' WHERE sv.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'saved_info_values', $fields );
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
        $query .= $this->table( 'saved sv' );
        $query .= ' LEFT JOIN '; 
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = sv.survey)';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'saved_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'saved_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

    public function isSaved( int $survey ) {
        if( !$this->user ) {
            return false;
        }

        $query = 'SELECT * FROM ';
        $query .= $this->table( 'saved' );
        $query .= ' WHERE user = ? AND survey = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $survey );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'saved_info_values', $fields );
        }

        return false;
    }

}
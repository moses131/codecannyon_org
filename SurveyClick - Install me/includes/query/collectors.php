<?php

namespace query;

class collectors extends \util\db {

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
        $this->orderby  = $this->filters->do_filter( 'collectors_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setIdBySlug( string $slug ) {
        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'collectors c' );
        $query .= ' WHERE slug = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "s", $slug );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $this->id   = $fields->id;
            $this->info = $this->filters->do_filter( 'collectors_info_values', $fields );
            return $fields->id;
        }

        return false;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'survey', '=', $id ];
        return $this;
    }

    public function setTypeId( int $id ) {
        $this->conditions['type'] = [ 'type', '=', $id ];
        return $this;
    }

    public function slug( string $str ) {
        $this->conditions['slug'] = [ 'slug', '=', $str ];
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

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getName() {
        return $this->info->name;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getCPA() {
        return $this->info->cpa;
    }

    public function getCPAF() {
        return cms_money_format( $this->info->cpa );
    }

    public function getLoyaltyPoints() {
        return $this->info->lpoints;
    }

    public function getPermalink( string $path = '' ) {
        $link = $this->filters->do_filter( 'respond-permalink', false, $this->info->slug, $this->info->id );
        if( $path !== '' ) {
            $link = $link . '/' . $path;
        }
        return $link;
    }

    public function getSlug() {
        return $this->info->slug;
    }

    public function getType() {
        return $this->info->type;
    }

    public function isVisible() {
        return $this->info->visible;
    }

    public function getSetting() {
        return ( !empty( $this->info->setting ) ? json_decode( $this->info->setting, true ) : [] );
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
    }

    public function getSurvey() {
        $surveys = new surveys;
        $surveys ->setObject( $this->info );
        return $surveys;
    }

    public function getResults() {
        $id     = $this->info->id ?? $this->id;
        $results= new survey\results;
        $results->setCollectorId( $id );
        return $results;
    }

    public function actions() {
        $id         = $this->info->id ?? $this->id;
        $actions    = new \user\collector_actions;
        $actions    ->setId( $id );
        return $actions;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['name']           = 'name';
        $list['name_desc']      = 'name DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';

        return $this->filters->do_filter( 'countries_order_by_values', $list );
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
        return $this->filters->do_filter( 'collectors_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'collectors_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'collectors' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'collectors_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'collectors c' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'collectors_info_values', $fields );
        }

        return false;
    }

    public function options() {
        $id = $this->info->id ?? $this->id;

        $query = 'SELECT `type`, `value` FROM ';
        $query .= $this->table( 'collector_options' ); 
        $query .= ' WHERE collector = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            $data[$row['type']][$row['value']] = $row['value'];
        }

        $stmt->close();

        return $data;
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
        $query .= $this->table( 'collectors' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'collectors_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'collectors_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
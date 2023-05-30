<?php

namespace query\survey;

class label_results extends \util\db {

    private $id;
    private $info;
    private $showCountry;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'r.*, li.id as li_id, li.checked as checked, li.date as li_date';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();
        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'label_results_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setStatus( int $type ) {
        $this->conditions['status'] = [ 'r.status', '=', $type ];
        return $this;
    }

    public function setChecked( bool $checked ) {
        if( $checked )
        $this->conditions['checked'] = [ 'li.checked', '=', 1 ];
        else
        $this->conditions['checked'] = [ 'li.checked', 'IS NULL', '' ];
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
        return ( $this->info->li_id ?? $this->id );
    }

    public function getChecked() {
        return $this->info->checked;
    }

    public function getDate() {
        return $this->info->li_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
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
        $list               = [];
        $list['id']         = 'li.id';
        $list['id_desc']    = 'li.id DESC';
        $list['name']       = 'l.name';
        $list['name_desc']  = 'l.name DESC';
        $list['date']       = 'li.date';
        $list['date_desc']  = 'li.date DESC';

        return $this->filters->do_filter( 'label_results_order_by_values', $list );
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
        return $this->filters->do_filter( 'label_results_per_page', $this->items_per_page );
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

    public function asList() {
        $list = [];
        foreach( $this->fetch( -1 ) as $var ) {
            $list[$var->var] = $var->value;
        }

        return $list;
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'label_results_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'label_items li' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'results r' );
        $query .= ' ON r.id = li.result';
        
        $query .= ' WHERE label = ' . $this->id;
        $query .= $this->finalCondition( ' AND ' );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'label_results_count', $count );
    }

    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'label_items li' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'surveys_info_values', $fields );
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
        $query .= $this->table( 'label_items li' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'results r' );
        $query .= ' ON r.id = li.result';

        if( $this->showCountry ) {
            $query .= ' LEFT JOIN ';
            $query .= $this->table( 'countries c' );
            $query .= ' ON c.iso_3166 = r.country';
        }

        $query .= ' WHERE li.label = ' . $this->id;
        $query .= $this->finalCondition( ' AND ' );

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'label_results_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'label_results_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
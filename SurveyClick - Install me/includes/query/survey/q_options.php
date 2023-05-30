<?php

namespace query\survey;

class q_options extends \util\db {

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
        $this->orderby  = $this->filters->do_filter( 'q_options_default_order_by', [ 'position', 'date' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setQuestionId( int $id ) {
        $this->conditions['type']       = [ 'type', '=', 0 ];
        $this->conditions['type_id']    = [ 'type_id', '=', $id ];
        return $this;
    }

    public function setLabel( int $id, int $label_id = 1 ) {
        $this->conditions['type']       = [ 'type', '=', $label_id ];
        $this->conditions['type_id']    = [ 'type_id', '=', $id ];
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

    public function getId() {
        return $this->info->id;
    }

    public function getTitle() {
        return $this->info->title;
    }

    public function getQuestionId() {
        return $this->info->question;
    }

    public function getPoints() {
        return $this->info->points;
    }

    public function getConditions() {
        return ( !empty( $this->info->conditions ) ? json_decode( $this->info->conditions, true ) : [] );
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getOptions( int $type = 3 ) {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;

        $this->setLabel( $id, $type );
        return $this;
    }

    public function getMedia() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;

        $media  = new \query\media;
        $media  ->setType( 2 );
        $media  ->setTypeId( $id );
        return $media;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['title']          = 'title';
        $list['title_desc']     = 'title DESC';
        $list['position']       = 'position';
        $list['position_desc']  = 'position DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';
        $list['rand']           = 'RAND()';

        return $this->filters->do_filter( 'q_options_order_by_values', $list );
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
        return $this->filters->do_filter( 'q_options_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'q_options_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'q_options' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'q_options_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        $id     = !empty( $id ) ? $id : $this->id;
        if( !$id ) return false;

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'q_options' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'q_options_info_values', $fields );
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
        $query .= $this->table( 'q_options' );
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'q_options_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'q_options_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
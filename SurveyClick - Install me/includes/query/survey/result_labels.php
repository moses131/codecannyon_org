<?php

namespace query\survey;

class result_labels extends \util\db {

    private $id;
    private $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'l.*';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();
        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'result_labels_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
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
        return $this->id;
    }

    public function getName() {
        return $this->info->name;
    }

    public function getColor() {
        return $this->info->color;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function getLabelObject( $info = NULL ) {
        $labels    = new labels;
        if( !empty( $info ) ) {
            $labels->setObject( $info );
        } else {
            $labels->setObject( $this->info );
        }
        return $labels;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'li.id';
        $list['id_desc']    = 'li.id DESC';
        $list['name']       = 'l.name';
        $list['name_desc']  = 'l.name DESC';
        $list['date']       = 'li.date';
        $list['date_desc']  = 'li.date DESC';

        return $this->filters->do_filter( 'result_labels_order_by_values', $list );
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
        return $this->filters->do_filter( 'result_labels_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'result_labels_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'label_items' ); 
        $query .= ' WHERE result = ' . $this->id;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'result_labels_count', $count );
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
        $query .= $this->table( 'labels l' );
        $query .= ' ON l.id = li.label';
        $query .= ' WHERE li.result = ' . $this->id;

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'result_labels_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'result_labels_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
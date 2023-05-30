<?php

namespace query\shop;

class category_with_items extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'si.*, sc.category as sc_category';
    protected $selectKey;

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setCategoryId( int $id ) {
        $this->conditions['category'] = [ 'sc.category', '=', $id ];
        return $this;
    }

    public function search( string $name ) {
        if( $name !== '' ) {

            filters()->add_filter( 'shop_items_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_name'] = [ 'MATCH(si.name)', 'AGAINST', '*' . $name . '*' ];
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

    public function getCategoryId() {
        return $this->info->sc_category;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getName() {
        return $this->info->name;
    }

    public function getDescription() {
        return $this->info->description;
    }

    public function getMediaMarkup() {
        if( $this->info->media && ( $imageURL = mediaLinks( $this->info->media )->getItemURL() ) )
        return '<img src="' . $imageURL . '" alt="">';
        return filters()->do_filter( 'default_shop_item_image', '<div class="avt avt-' . strtoupper( $this->info->name[0] ) . '"><span>' . strtoupper( $this->info->name[0] ) . '</span></div>' );
    }

    public function getMediaURL() {
        if( $this->info->media && ( $imageURL = mediaLinks( $this->info->media )->getItemURL() ) )
        return $imageURL;
        return false;
    }

    public function getMedia() {
        return $this->info->media;
    }

    public function getStock() {
        return $this->info->stock;
    }

    public function getPrice() {
        return $this->info->price;
    }

    public function getPurchases() {
        return $this->info->stock;
    }

    public function getCountry() {
        return $this->info->country;
    }

    public function getStatus() {
        return $this->info->status;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getCategory() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $categories  = new categories;
        $categories  ->setCategoryId( $this->info->category );
        return $categories;
    }

    public function getItem() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $items  = new items;
        $items  ->setId( $this->info->item );
        return $items;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['name']           = 'name';
        $list['name_desc']      = 'name DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';

        return $this->filters->do_filter( 'shop_items_order_by_values', $list );
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
        return $this->filters->do_filter( 'shop_category_with_items_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'shop_category_with_items_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'shop_category_items sc' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'shop_items si' );
        $query .= ' ON si.id = sc.item';
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'shop_category_with_items_count', $count );

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
        $query .= $this->table( 'shop_category_items sc' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'shop_items si' );
        $query .= ' ON si.id = sc.item';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'shop_category_with_items_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'shop_category_with_items_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
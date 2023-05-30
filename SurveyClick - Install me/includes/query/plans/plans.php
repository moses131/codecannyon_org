<?php

namespace query\plans;

class plans extends \util\db {

    private $id;
    protected $info;
    private $free_plan;
    private $active_offers;
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
        $this->orderby  = $this->filters->do_filter( 'plan_offers_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setVisible( int $type = 2, string $op = '=' ) {
        $this->conditions['visible'] = [ 'visible', $op, $type ];
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

    public function getName() {
        return $this->info->name;
    }

    public function getSurveys() {
        return $this->info->sur;
    }

    public function getResponses() {
        return $this->info->res_p_sur;
    }

    public function getQuestions() {
        return $this->info->que_p_sur;
    }

    public function getCollectors() {
        return $this->info->col;
    }

    public function getTeam() {
        return $this->info->tm;
    }

    public function getAvailableSpace() {
        return $this->info->space;
    }

    public function getRemoveBrand() {
        return $this->info->r_brand;
    }
    
    public function getPrice() {
        return $this->info->price;
    }

    public function getPriceF() {
        return cms_money_format( $this->info->price );
    }

    public function isVisible() {
        return $this->info->visible;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getValue( string $key, string $default = '' ) {
        switch( $key ) {
            case 'surveys': return ( $this->getSurveys() > -1 ? $this->getSurveys() : t( 'unlimited') );
            case 'responses': return ( $this->getResponses() > -1 ? $this->getResponses() : t( 'unlimited') );
            case 'questions': return ( $this->getQuestions() > -1 ? $this->getQuestions() : t( 'unlimited') );
            case 'collectors': return ( $this->getCollectors() > -1 ? $this->getCollectors() : t( 'unlimited') );
            case 'members': return ( $this->getTeam() > -1 ? $this->getTeam() : t( 'unlimited') );
            case 'brand': return ( $this->getRemoveBrand() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
            case 'space': return ( $this->getAvailableSpace() > -1 ? \util\etc::mibToStr( $this->getAvailableSpace() ) : t( 'unlimited') ); break;
            case 'share': return ( $this->getTeam() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
            case 'chat': return ( $this->getTeam() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
        }

        return filters()->do_filter( 'plans-price-value', $default, $key );
    }

    public function getValueFreePlan( string $key, string $default = '' ) {
        if( !$this->free_plan )
        $this->free_plan = getFreeSubscription();

        switch( $key ) {
            case 'surveys': return ( $this->free_plan->surveys() > -1 ? $this->free_plan->surveys() : t( 'unlimited') );
            case 'responses': return ( $this->free_plan->responses() > -1 ? $this->free_plan->responses() : t( 'unlimited') );
            case 'questions': return ( $this->free_plan->questions() > -1 ? $this->free_plan->questions() : t( 'unlimited') );
            case 'collectors': return ( $this->free_plan->collectors() > -1 ? $this->free_plan->collectors() : t( 'unlimited') );
            case 'members': return ( $this->free_plan->teamMembers() > -1 ? $this->free_plan->teamMembers() : t( 'unlimited') );
            case 'brand': return ( $this->free_plan->removeBrand() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
            case 'space': return ( $this->free_plan->space() > -1 ? \util\etc::mibToStr( $this->free_plan->space() ) : t( 'unlimited') ); break;
            case 'share': return ( $this->free_plan->teamMembers() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
            case 'chat': return ( $this->free_plan->teamMembers() ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ); break;
        }

        return filters()->do_filter( 'plans-price-value-free-plan', $default, $key );
    }

    public function getSubscribers() {
        $id             = $this->info->id ?? $this->id;
        $subscribers    = new plan_subscribers;
        $subscribers    ->setPlanId( $id );
        return $subscribers;
    }
    
    public function activeOffers() {
        $id     = $this->info->id ?? $this->id;
        $offers = new offers;
        $offers ->setPlanId( $id )
                ->setMinMonths( 1 )
                ->setNotExpired();
        return $offers; 
    }

    public function getMonths( int $months ) {
        $currp  = $this->getPrice();
        $mlist  = [];
        foreach( range( 1, max( 1, $months ) ) as $month ) {
            $mPrice     = $this->priceMonths( $month );
            $mlist[$month]    = [ 'month' => $month, 'priceF' => cms_money_format( $mPrice ), 'price' => $mPrice ];
        }
        return $mlist;
    }

    public function priceMonths( int $months ) {
        if( !$this->active_offers )
        $this->active_offers= $this->activeOffers()
                            ->fetch( -1 );
        $price = [ $this->getPrice() ];
        foreach( $this->active_offers as $ao ) {
            if( $months >= $ao->min_months )
            $price[] = $ao->price;
        }
        return min( $price );
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['price']          = 'price';
        $list['price_desc']     = 'price DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';

        return $this->filters->do_filter( 'plan_offers_order_by_values', $list );
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
        return $this->filters->do_filter( 'plan_offers_per_page', $this->items_per_page );
    }

    public function bestPrice( int $months = 1 ) {
        $id     = $this->info->id ?? $this->id;

        $query  = 'SELECT price FROM ';
        $query  .= $this->table( 'plan_offers' );
        $query  .= ' WHERE plan = ? AND min_months >= ? AND starts <= NOW() AND (expires IS NULL OR expires >= NOW()) AND visible >= 1';
        $query  .= ' ORDER BY price';
        $query  .= ' LIMIT 1';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $id, $months );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $fields->price;
        }

        if( !empty( $this->info ) ) {
            $this->getObject();
        }

        return $this->info->price;
    }

    public function bestPriceF() {
        return cms_money_format( $this->bestPrice() );
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'plan_offers_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'plans' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'plan_offers_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->info->id ?? $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'plans' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'plan_offers_info_values', $fields );
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
        $query .= $this->table( 'plans' );
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'plan_offers_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'plan_offers_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
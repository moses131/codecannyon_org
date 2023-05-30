<?php

namespace query\survey;

class custom extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    private $options        = [];
    private $commission;
    // db query
    protected $select         = 's.*, c.id as c_id, c.survey as c_survey, c.user as c_user, c.name as c_name, c.slug as c_slug, c.type as c_type, c.setting as c_setting, c.cpa as c_cpa,	c.lpoints as c_lpoints, c.visible as c_visible, c.date as c_date';
    protected $selectKey      = 'id';
    
    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'custom_surveys_default_order_by', [ 'cpa_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setCollectorId( int $id ) {
        $this->conditions['collector'] = [ 'c.id', '=', $id ];
        return $this;
    }

    public function setOptions( array $options ) {
        $this->options = $options;
        return $this;
    }

    public function setOption( array $option ) {
        $this->options[] = $option;
        return $this;
    }

    public function setUserOptions( bool $visitor = true ) {
        if( me() ) {

            $gender     = me()->getGender();
            if( !$gender )
            return false;

            $birthday   = me()->getBirthday();
            if( !$birthday )
            return false;

            $age       = me()->getAge();
            if( !$age )
            return false;

            switch( $age ) {
                case ( $age >= 18 && $age <= 24 ): $age_r = 1; break;
                case ( $age >= 25 && $age <= 34 ): $age_r = 2; break;
                case ( $age >= 35 && $age <= 44 ): $age_r = 3; break;
                case ( $age >= 45 && $age <= 54 ): $age_r = 4; break;
                case ( $age >= 55 && $age <= 64 ): $age_r = 5; break;
                case ( $age >= 65 && $age <= 74 ): $age_r = 6; break;
                default: $age_r = 0;
            }

            $uCountry   = me()->getCountry()
                        ->select( [ 'id' ] )->selectKey( 'id' );
            $country    = $uCountry->getObject() ? $uCountry->getId() : -1;
            $this->options[]    = [ 1 => $country ];
            $this->options[]    = [ 2 => ( $gender == 'F' ? 2 : 1 ) ];
            $this->options[]    = [ 3 => $age_r ];

        } else if( $visitor )
            $this->options[]    = [ 1 => getUserCountry( 'id' ) ];
        
        return true;
    }

    public function setCategoryId( int $id ) {
        $this->conditions['category'] = [ 's.category', '=', $id ];
        return $this;
    }

    public function search( string $name ) {
        if( $name !== '' ) {
            filters()->add_filter( 'custom_surveys_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select = $this->select . ', MATCH(s.name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_name'] = [ 'MATCH(s.name)', 'AGAINST', '*' . $name . '*' ];
        }
        return $this;
    }

    public function selectDistinctCategory() {
        $this->selectKey= 'category';
        $this->select   = 'DISTINCT(s.category)';
        return $this;
    }

    public function selectDistinctStatus() {
        $this->selectKey= 'status';
        $this->select = 'DISTINCT(s.status)';
        return $this;
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject( string $slug = NULL ) {
        if( empty( $this->info ) ) {
            $this->info = $this->info( $slug );
        }
        return $this->info;
    }

    public function getId() : int {
        return $this->info->c_id;
    }

    public function getSurveyId() {
        return $this->info->c_survey;
    }

    public function getName() {
        return $this->info->c_name;
    }

    public function getUserId() {
        return $this->info->c_user;
    }

    public function getCPA() {
        return $this->info->c_cpa;
    }

    public function getCPAF() {
        return cms_money_format( $this->info->c_cpa );
    }

    public function getCPA2() {
        if( !$this->commission )
        $this->commission   = get_option( 'comm_cpa', 0 );
        $commission         = $this->info->c_cpa * ( $this->commission / 100 );
        return ( $this->info->c_cpa - $commission );
    }

    public function getCPAF2() {
        if( !$this->commission )
        $this->commission   = get_option( 'comm_cpa', 0 );
        $commission         = $this->info->c_cpa * ( $this->commission / 100 );
        return cms_money_format( ( $this->info->c_cpa - $commission ) );
    }

    public function getLoyaltyPoints() {
        return $this->info->c_lpoints;
    }

    public function getCollectorPermalink( string $path = '' ) {
        $link = $this->filters->do_filter( 'respond-permalink', false, $this->info->c_slug, $this->info->c_id );
        if( $path !== '' ) {
            $link = $link . '/' . $path;
        }
        return $link;
    }

    public function getSlug() {
        return $this->info->c_slug;
    }

    public function getType() {
        return $this->info->c_type;
    }

    public function isVisible() {
        return $this->info->c_visible;
    }

    public function getSetting() {
        return ( !empty( $this->info->c_setting ) ? json_decode( $this->info->c_setting, true ) : [] );
    }

    public function getDate() {
        return $this->info->c_date;
    }

    public function resetInfo() {
        $this->info = [];
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

    public function getResults() {
        $results = new results;
        $results ->setCollectorId( $this->info->c_id );
        return $results;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'c.id';
        $list['id_desc']    = 'c.id DESC';
        $list['cpa']        = 'c.cpa';
        $list['cpa_desc']   = 'c.cpa DESC';
        $list['date']       = 'c.date';
        $list['date_desc']  = 'c.date DESC';

        return $this->filters->do_filter( 'custom_surveys_order_by_values', $list );
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
        return $this->filters->do_filter( 'custom_surveys_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'custom_surveys_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(DISTINCT(s.id)) FROM ';
        $query .= $this->table( 'collectors c' );

        if( !empty( $this->options ) ) {
            $query .= ' RIGHT  JOIN ';
            $query .= ' (SELECT collector, COUNT(*) as count FROM ';
            $query .= $this->table( 'collector_options' );
            $query .= ' WHERE ';

            $conds = array_map( function( $v ) {
                return '(`type` = ' . (int) key( $v ) . ' AND `value` IN (0,' . (int) current( $v ) . '))';
            }, $this->options );

            $query .= implode( ' OR ', $conds );
            $query .= ' GROUP BY collector HAVING count = ' . count( $conds );
            $query .= ') co ON (co.collector = c.id)';
        }

        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = c.survey)';
        $query .= ' WHERE c.type = 1 AND c.visible = 1 AND s.status = 4';
        $query .= $this->finalCondition( ' AND ' );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'custom_surveys_count', $count );
    }

    // Get information as object
    public function info( $slug = NULL ) {
        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'collectors c' );

        if( !empty( $this->options ) ) {
            $query .= ' RIGHT JOIN ';
            $query .= ' (SELECT collector, COUNT(*) as count FROM ';
            $query .= $this->table( 'collector_options' );
            $query .= ' WHERE ';

            $conds = array_map( function( $v ) {
                return '(`type` = ' . (int) key( $v ) . ' AND `value` IN (0,' . (int) current( $v ) . '))';
            }, $this->options );

            $query .= implode( ' OR ', $conds );
            $query .= ' GROUP BY collector HAVING count = ' . count( $conds );
            $query .= ') co ON (co.collector = c.id)';
        }

        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = c.survey)';

        if( $slug ) 
            $query .= ' WHERE c.slug = ?';
        else if( $this->id )
            $query .= ' WHERE c.id = ?';
        else 
            return false;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );

        if( $slug ) 
            $stmt->bind_param( 's', $slug );
        else if( $this->id )
            $stmt->bind_param( 'i', $this->id );

        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->filters->do_filter( 'custom_surveys_info_values', $fields );

        return false;
    }

    // Check options
    public function checkOptions() {
        $query = 'SELECT COUNT(*) as count FROM ';
        $query .= $this->table( 'collector_options' );
        $query .= ' WHERE collector = ? AND (';

        $conds = array_map( function( $v ) {
            return '(`type` = ' . (int) key( $v ) . ' AND `value` IN (0,' . (int) current( $v ) . '))';
        }, $this->options );

        $query .= implode( ' OR ', $conds );
        $query .= ') GROUP BY collector HAVING count = ' . count( $conds );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->info->c_id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->filters->do_filter( 'custom_surveys_info_values', $fields );

        return false;
    }

    // Get user's response
    public function getResponse() {
        $query = 'SELECT * FROM ';
        $query .= $this->table( 'results' );
        $query .= ' WHERE ';
        if( me() ) {
            $query .= 'user = ' . (int) me()->getId();
        } else {
            $query .= 'visitor = "' . $this->dbp( \util\etc::userIP() ) . '"';
        }
        $query .= ' AND survey = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->info->id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->filters->do_filter( 'results_info_values', $fields );

        return false;
    }

    // Fetch entries
    public function fetch( int $max = 0, bool $pagination = true ) {
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
        $query .= $this->table( 'collectors c' );

        if( !empty( $this->options ) ) {
            $query .= ' RIGHT JOIN ';
            $query .= ' (SELECT collector, COUNT(*) as count FROM ';
            $query .= $this->table( 'collector_options' );
            $query .= ' WHERE ';

            $conds = array_map( function( $v ) {
                return '(`type` = ' . (int) key( $v ) . ' AND `value` IN (0, ' . (int) current( $v ) . '))';
            }, $this->options );

            $query .= implode( ' OR ', $conds );
            $query .= ' GROUP BY collector HAVING count = ' . count( $conds );
            $query .= ') co ON (co.collector = c.id)';
        }

        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'surveys s' );
        $query .= ' ON (s.id = c.survey)';
        $query .= ' WHERE c.type = 1 AND c.visible = 1 AND s.status = 4';
        $query .= $this->finalCondition( ' AND ' );
        $query .= ' GROUP BY c.survey';

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'custom_surveys_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'custom_surveys_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
<?php

namespace query\survey;

class results extends \util\db {

    private $id;
    private $user;
    private $showCountry;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'r.*';
    protected $selectKey    = 'id';
    private $answer_select  = '*';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'results_default_order_by', [ 'id_desc' ] );
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
        $this->conditions['user'] = [ 'r.user', '=', $id ];
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'r.survey', '=', $id ];
        return $this;
    }

    public function setCollectorId( int $id ) {
        $this->conditions['collector'] = [ 'r.collector', '=', $id ];
        return $this;
    }

    public function setStatus( int $type, string $op = '=' ) {
        $this->conditions['status'] = [ 'r.status', $op, $type ];
        return $this;
    }

    public function setStatusIN( array $ids ) {
        $this->conditions['type'] = [ 'r.status', 'IN', $ids ];
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
        return $this->info->id;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getUserIpAddress() {
        return $this->info->visitor;
    }

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getStatus() {
        return $this->info->status;
    }

    public function getCommissionWithoutBonus() {
        return $this->info->commission;
    }

    public function getCommissionBonus() {
        return $this->info->commission_bonus;
    }
    
    public function getCommission() {
        return ( $this->info->commission + $this->info->commission_bonus );
    }

    public function getCommissionF() {
        return cms_money_format( $this->info->commission + $this->info->commission_bonus );
    }

    public function getLoyaltyStars() {
        return $this->info->lpoints;
    }

    public function getResults() {
        return ( !empty( $this->info->results ) ? json_decode( $this->info->results, true ) : [] );
    }

    public function getFinishDate() {
        return $this->info->fin;
    }

    public function getExpirationDate() {
        return $this->info->exp;
    }

    public function getDuration() {
        if( $this->info->status == 1 )
        $spent  = time();
        else
        $spent  = strtotime( $this->info->fin );
        $spent  = $spent - strtotime( $this->info->date );
        $durat  = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );
        return $durat;
    }

    public function getStatusMarkup( string $str = '' ) {
        // 0 - Rejected
        // 1 - in progress
        // 2 - finished
        // 3 - finished approved

        switch( $this->info->status ) {
            case 0:
                return '<div class="tst"><div class="mmsg failed">' . t( 'Rejected' ) . '</div>' . $str . '</div>';
            break;

            case 1:
                return '<div class="tst"><div class="mmsg onhold">' . t( 'In progress' ) . '</div>' . $str . '</div>';
            break;

            case 2:
                return '<div class="tst"><div class="mmsg completed">' . t( 'Finished & awaiting approval' ) . '</div>' . $str . '</div>';
            break;

            case 3:
                return '<div class="tst"><div class="mmsg completed">' . t( 'Finished' ) . '</div>' . $str . '</div>';
            break;
        }
    }

    public function getCountry() {
        return $this->info->country;
    }

    public function getCountryName() {
        return $this->info->c_name;
    }

    public function getCountryIso3166() {
        return $this->info->c_iso_3166;
    }

    public function getComment() {
        return $this->info->comment;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function isSelfResponse() {
        return ( !$this->info->user && !$this->info->visitor );
    }

    public function answerSelect( string $select ) {
        $this->answer_select = $select;
        return $this;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getUser() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $users  = users();
        $users  ->setId( $this->info->user );
        return $users;
    }

    public function getSurvey() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $surveys  = surveys();
        $surveys  ->setId( $this->info->survey );
        return $surveys;
    }

    public function getCollector() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $collectors  = collectors();
        $collectors  ->setId( $this->info->collector );
        return $collectors;
    }

    public function getVariables() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $vars   = new result_variables;
        $vars   ->setResultId( $id );
        return $vars;
    }

    public function getLabels() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $vars   = new result_labels;
        $vars   ->setId( $id );
        return $vars;
    }

    public function getAnswer( int $type, int $id = NULL, int $survey = NULL ) {
        $id     =  $id ?? $this->info->id ?? $this->id;
        if( !$id ) return false;
        $survey =  $survey ?? $this->info->survey ?? NULL;
        if( !$survey ) return false;

        $query  = 'SELECT ' . $this->answer_select . ' FROM ';
        $query  .= $this->table( 'answers' );
        $query  .= ' WHERE survey = ? AND type = ? AND result = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $survey, $type, $id );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            $data[] = $this->filters->do_filter( 'answer_values', (object) $row );
        }

        $count = count( $data );

        if( $count == 0 ) {
            $data = false;
        } else if( count( $data ) == 1 ) {
            $data = current( $data );
        }

        $stmt->close();

        return $data;
    }
    
    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'r.id';
        $list['id_desc']        = 'r.id DESC';
        $list['date']           = 'r.date';
        $list['date_desc']      = 'r.date DESC';

        return $this->filters->do_filter( 'results_order_by_values', $list );
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
        return $this->filters->do_filter( 'results_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'results_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'results r' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'results_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'results r' );
        $query .= ' WHERE r.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'results_info_values', $fields );
        }

        return false;
    }

    // Get user's response
    public function getResponse() {
        $query = 'SELECT * FROM ';
        $query .= $this->table( 'results' );
        $query .= ' WHERE id = ? AND ';
        if( me() ) {
            $query .= 'user = ' . (int) me()->getId();
        } else {
            $query .= 'visitor = "' . $this->dbp( \util\etc::userIP() ) . '"';
        }

        $id = $this->info->id ?? $this->id;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'results_info_values', $fields );
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
        $query .= $this->table( 'results r' );

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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'results_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'results_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

    public function isResponsed( int $survey ) {
        if( !$this->user ) {
            return false;
        }

        $query = 'SELECT * FROM ';
        $query .= $this->table( 'results' );
        $query .= ' WHERE user = ? AND survey = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $survey );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'results_info_values', $fields );
        }

        return false;
    }

}
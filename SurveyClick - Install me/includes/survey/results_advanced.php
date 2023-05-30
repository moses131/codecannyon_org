<?php

namespace survey;

class results_advanced extends report_options {

    private $survey;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    private $groupBy        = 'r.id';
    // db query
    protected $select       = 'r.*, c.iso_3166 as c_iso_3166, c.name as c_name';
    protected $selectKey    = 'id';
    private $answer_select  = '*';

    function __construct( int $survey = 0, int $user = 0 ) {
        parent::__construct();
        $this->setSurveyId( $survey );

        $this->orderby  = $this->filters->do_filter( 'results_advanced_default_order_by', [ 'id_desc' ] );
    }

    public function setSurveyId( int $survey ) {
        if( $survey ) $this->survey = $survey;
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

    public function setStatus( int $type ) {
        $this->conditions['status'] = [ 'r.status', '=', $type ];
        return $this;
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
    
    public function getCommission() {
        return ( $this->info->commission + $this->info->commission_bonus );
    }

    public function getCommissionF() {
        return cms_money_format( $this->info->commission + $this->info->commission_bonus );
    }

    public function getResults() {
        return json_decode( $this->info->results, true );
    }

    public function getFinishDate() {
        return $this->info->fin;
    }

    public function getExpirationDate() {
        return $this->info->exp;
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

    public function getDuration() {
        if( $this->info->status == 1 )
        $spent  = time();
        else
        $spent  = strtotime( $this->info->fin );
        $spent  = $spent - strtotime( $this->info->date );
        $durat  = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );
        return $durat;
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
        if( !empty( $this->info->user ) ) return false;
        $users  = users();
        $users  ->setId( $this->info->user );
        return $users;
    }

    public function getSurvey() {
        if( !isset( $this->info->survey ) ) return false;
        $surveys  = surveys();
        $surveys  ->setId( $this->info->survey );
        return $surveys;
    }

    public function getVariables() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $vars   = new \query\survey\result_variables;
        $vars   ->setResultId( $id );
        return $vars;
    }

    public function getLabels() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $vars   = new \query\survey\result_labels;
        $vars   ->setId( $id );
        return $vars;
    }

    public function getAnswer( int $type, int $id = NULL ) {
        $id     =  $id ?? $this->info->id ?? $this->id;
        if( !$id ) return false;

        $query  = 'SELECT ' . $this->answer_select . ' FROM ';
        $query  .= $this->table( 'answers' );
        $query  .= ' WHERE survey = ? AND type = ? AND result = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $this->survey, $type, $id );
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

        return $this->filters->do_filter( 'results_advanced_order_by_values', $list );
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
        return $this->filters->do_filter( 'results_advanced_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'results_advanced_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $aConds = [];
        $query  = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'results r' );

        if( !empty( $this->answersCond ) ) {
            $query .= ' RIGHT JOIN ';
            $i      = 0;
            foreach( $this->answersCond as $type => $conds ) {
                foreach( $conds as $cond ) {
                    $a[] = '(SELECT * FROM ' . $this->table( 'answers' ) . ' WHERE survey = ' . $this->survey . ' AND type = ' . $type .' AND ' . $cond . ') a' . ++$i. ' ON r.id = a' . $i . '.result';
                }
            }
            $query .= implode( ' RIGHT JOIN ', $a );
        }

        if( !empty( $this->labels ) ) {
            $query .= ' RIGHT JOIN ';
            $query .= '(SELECT * FROM ' . $this->table( 'label_items' ) . ' WHERE label IN (' . implode( ',', array_map( 'intval', $this->labels ) ) . ')) l ON l.result = r.id';
        }
        
        $query .= ' WHERE r.survey = ' . $this->survey;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'surveys_count', $count );
    }

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

        $limit  = '';
        
        if( $max === 0 || ( $max > 0 && $pagination ) ) {
            $limit = ' LIMIT ' . ( ( $current_page - 1 ) * $per_page ) . ', ' . $per_page;
        } else if( $max > 0 && !$pagination ) {
            $limit = ' LIMIT ' . $max;
        }

        $aConds = [];
        $query  = 'SELECT ' . $this->select . ' FROM ' . $this->table( 'results r' );

        if( !empty( $this->answersCond ) ) {
            $query .= ' RIGHT JOIN ';
            $i      = 0;
            foreach( $this->answersCond as $type => $conds ) {
                foreach( $conds as $cond ) {
                    $a[] = '(SELECT * FROM ' . $this->table( 'answers' ) . ' WHERE survey = ' . $this->survey . ' AND type = ' . $type .' AND ' . $cond . ') a' . ++$i. ' ON r.id = a' . $i . '.result';
                }
            }
            $query .= implode( ' RIGHT JOIN ', $a );
        }

        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'countries c' );
        $query .= ' ON c.iso_3166 = r.country';

        if( !empty( $this->labels ) ) {
            $query .= ' RIGHT JOIN ';
            $query .= '(SELECT * FROM ' . $this->table( 'label_items' ) . ' WHERE label IN (' . implode( ',', array_map( 'intval', $this->labels ) ) . ')) l ON l.result = r.id';
        }

        $query .= ' WHERE r.survey = ' . $this->survey;

        $query .= $limit;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            if( $this->selectKey )
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'results_advanced_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'results_advanced_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
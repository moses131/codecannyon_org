<?php

namespace query\survey;

class questions extends \util\db {

    private $id;
    protected $info;
    private $view;
    private $filter;
    private $summary_types;
    private $survey;
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
        $this->orderby  = $this->filters->do_filter( 'questions_default_order_by', [ 'position' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setSurveyId( int $id ) {
        $this->conditions['survey'] = [ 'survey', '=', $id ];
        return $this;
    }

    public function setStepId( int $id ) {
        $this->conditions['step'] = [ 'step', '=', $id ];
        return $this;
    }

    public function setVisible( int $type, string $op = '=' ) {
        $this->conditions['visible'] = [ 'visible', $op, $type ];
        return $this;
    }

    public function search( string $title ) {
        if( $title !== '' ) {

            filters()->add_filter( 'questions_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(title) AGAINST ("*' . $this->dbp( $title ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_title'] = [ 'MATCH(title)', 'AGAINST', '*' . $title . '*' ];
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

    public function getTitle() {
        return $this->info->title;
    }

    public function getInfo() {
        return $this->info->info;
    }

    public function getType() {
        return $this->info->type;
    }

    public function getSetting() {
        return ( !empty( $this->info->setting ) ? json_decode( $this->info->setting, true ) : [] );
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getParentId() {
        return $this->info->parent;
    }

    public function getPosition() {
        return $this->info->position;
    }

    public function getStepId() {
        return $this->info->step;
    }

    public function isRequired() {
        return $this->info->required;
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

    public function getAttachments( int $response = NULL ) {
        if( !isset( $this->info->survey ) ) return false;
        $attachments = new attachments;
        $attachments->setSurveyId( $this->info->survey );
        $attachments->setQuestionId( $this->info->id );
        if( $response )
        $attachments->setResponseId( $response );
        return $attachments;
    }

    public function getOptions() {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $options = new q_options;
        $options ->setQuestionId( $id );
        return $options;
    }

    public function getLabels( int $type = 1 ) {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $labels = new q_options;
        $labels ->setLabel( $id, $type );
        return $labels;
    }

    public function getAnswerConditions() {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $conditions = new q_cond;
        $conditions ->setQuestionId( $id );
        return $conditions;
    }

    public function getUser() {
        if( !isset( $this->info->user ) ) return false;
        $users  = new users;
        $users  ->setId( $this->info->user );
        return $users;
    }

    public function getSurvey() {
        if( !isset( $this->info->survey ) ) return false;
        $surveys    = new surveys;
        $surveys    ->setId( $this->info->survey );
        return $surveys;
    }

    public function saveSurvey() {
        if( !isset( $this->info->survey ) ) return false;
        if( $this->survey ) return $this->survey;
        $this->survey   = new surveys;
        $this->survey   ->setId( $this->info->survey );
        return $this->survey;
    }

    public function markupView( string $view ) {
        switch( $view ) {
            case 'filters':
                $this->view = new \admin\markup\question\filters( $this );
            break;

            case 'results':
                $this->view = new \admin\markup\question\results( $this );
            break;

            case 'print_view':
                $this->view = new \admin\markup\question\print_view( $this );
            break;
        }

        return $this;
    }

    public function setFilter( string $filter ) {
        $this->filter = $filter;
        return $this;
    }

    private function filter( array $q ) {
        switch( $this->filter ) {
            case 'report':
                if( !$this->summary_types )
                $this->summary_types = survey_types()->getTypesSummary();
                if( array_search( $q['type'], $this->summary_types ) !== false ) 
                return true;
            break;

            default: return true;
        }
    }

    public function markup( $value = '', ...$attrs ) {
        if( !$this->view ) {
            return ;
        }

        return $this->view->markup( $value, $attrs );
    }

    public function getMedia() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        
        $media  = new \query\media;
        $media  ->setType( 1 );
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

        return $this->filters->do_filter( 'questions_order_by_values', $list );
    }

    public function orderBy( $values ) {
        if( is_string( $values ) )
        $values         = [ $values ];
        $this->orderby  = array_intersect( $values, array_keys( $this->orderBy_values() ) );
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
        return $this->filters->do_filter( 'questions_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'questions_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'questions' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'questions_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        $id     = !empty( $id ) ? $id : $this->id;
        if( !$id ) return false;

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'questions' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'questions_info_values', $fields );
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
        $query .= $this->table( 'questions' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'questions_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'questions_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
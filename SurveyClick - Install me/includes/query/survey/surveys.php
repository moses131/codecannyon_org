<?php

namespace query\survey;

class surveys extends \util\db {

    private $id;
    protected $info;
    private $texts;
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
        $this->orderby  = $this->filters->do_filter( 'surveys_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->conditions['user'] = [ 'user', '=', $id ];
        return $this;
    }

    public function setType( int $type, string $op = '=' ) {
        $this->conditions['type'] = [ 'type', $op, $type ];
        return $this;
    }

    public function setStatus( int $status, string $op = '=' ) {
        $this->conditions['status'] = [ 'status', $op, $status ];
        return $this;
    }

    public function setCategoryId( int $id ) {
        $this->conditions['category'] = [ 'category', '=', $id ];
        return $this;
    }

    public function search( string $name ) {
        if( $name !== '' ) {
            filters()->add_filter( 'surveys_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_name'] = [ 'MATCH(name)', 'AGAINST', '*' . $name . '*' ];
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

    public function getName() {
        return $this->info->name;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getTeamId() {
        return $this->info->team;
    }

    public function getLastUpdateUserId() {
        return $this->info->lu_user;
    }

    public function getCategoryId() {
        return $this->info->category;
    }

    public function getResponses() {
        return $this->info->ent_done;
    }

    public function getResponsesTarget() {
        return $this->info->ent_target;
    }

    public function getBudget() {
        return ( $this->info->budget + $this->info->budget_bonus );
    }

    public function getRealBudget() {
        return $this->info->budget;
    }

    public function getBudgetBonus() {
        return $this->info->budget_bonus;
    }

    public function getBudgetF() {
        return cms_money_format( (double) ( $this->info->budget + $this->info->budget_bonus ) );
    }

    public function getRealBudgetF() {
        return cms_money_format( (double) $this->info->budget );
    }

    public function getBudgetBonusF() {
        return cms_money_format( (double) $this->info->budget_bonus );
    }

    public function getBudgetSpent() {
        return $this->info->spent;
    }

    public function getBudgetSpentF() {
        return cms_money_format( (double) $this->info->spent );
    }

    public function getStatus() {
        return $this->info->status;
    }

    public function getType() {
        return $this->info->type;
    }

    public function getTemplate() {
        return $this->info->template;
    }

    public function autovalidate() {
        return $this->info->autovalid;
    }

    public function getLastReport() {
        return $this->info->last_report;
    }

    public function getLastAnswer() {
        return $this->info->last_answer;
    }

    public function getAvatarMarkup() {
        if( $this->info->avatar && ( $imageURL = mediaLinks( $this->info->avatar )->getItemURL() ) ) {
            return '<div><img src="' . $imageURL . '" alt="" /></div>';
        }
        return filters()->do_filter( 'default_survey_avatar', '<div class="avt avt-' . strtoupper( $this->info->name[0] ) . '"><span>' . strtoupper( $this->info->name[0] ) . '</span></div>' );
    }

    public function getAvatarURL() {
        if( $this->info->avatar && ( $imageURL = mediaLinks( $this->info->avatar )->getItemURL() ) ) {
            return $imageURL;
        }
        return false;
    }

    public function getAvatar() {
        return $this->info->avatar;
    }

    public function getQuestionsCount() {
        return $this->info->questions;
    }

    public function getStatusMarkup( bool $show_stats = true ) {
        // 0 - rejected
        // 1 - questions added & budget set
        // 2 - waiting approval
        // 3 - on pause
        // 4 - live
        // 5 - finished

        switch( $this->info->status ) {
            case -1:
                return '<div class="tst tc"><div class="mmsg failed">' . t( 'Pending deletion' ) . '</div><div></div></div>';
            break;

            case 0:
                return '<div class="tst tc"><div class="mmsg failed">' . t( 'Not approved' ) . '</div><div></div></div>';
            break;

            case 1:
                return '<div class="tst tc"><div class="mmsg onhold">' . t( 'Request setup' ) . '</div>' . ( $show_stats ? '<div>' . $this->info->ent_done . '/' . $this->info->ent_target . '</div>' : '' ) . '</div>';
            break;

            case 2:
                return '<div class="tst tc"><div class="mmsg failed">' . t( 'Waiting approval' ) . '</div>' . ( $show_stats ? '<div>' . $this->info->ent_done . '/' . $this->info->ent_target . '</div>' : '' ) . '</div>';
            break;

            case 3:
                return '<div class="tst tc"><div class="mmsg onhold">' . t( 'Paused' ) . '</div>' . ( $show_stats ? '<div>' . $this->info->ent_done . '/' . $this->info->ent_target . '</div>' : '' ) . '</div>';
            break;

            case 4:
                return '<div class="tst tc"><div class="mmsg success">' . t( 'Live' ) . '</div>' . ( $show_stats ? '<div>' . $this->info->ent_done . '/' . $this->info->ent_target . '</div>' : '' ) . '</div>';
            break;

            case 5:
                return '<div class="tst tc"><div class="mmsg completed">' . t( 'Finished' ) . '</div>' . ( $show_stats ? '<div>' . $this->info->ent_done . '/' . $this->info->ent_target . '</div>' : '' ) . '</div>';
            break;
        }
    }

    public function getPermalink( string $path = '' ) {
        $link = $this->filters->do_filter( 'survey_permalink', false, $this->info->name, $this->info->id );
        if( $path !== '' ) {
            $link = $link . '/' . $path;
        }
        return $link;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function getText( string $id, string $fallback ) {
        if( $this->texts === NULL )
        $this->texts = $this->meta()->getJson( 'texts', [] );

        if( !empty( $this->texts[$id] ) )
        return esc_html( $this->texts[$id] );
        else return $fallback;
    }
    
    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getSteps() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $steps  = new steps;
        $steps  ->setSurveyId( $id );
        return $steps;
    }

    public function getCollectors() {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $collectors = new \query\collectors;
        $collectors ->setSurveyId( $id );
        return $collectors;
    }

    public function getResults() {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $results    = new results;
        $results    ->setSurveyId( $id );
        return $results;
    }

    public function getLabels() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $labels = new labels;
        $labels ->setSurveyId( $id );
        return $labels;
    }

    public function getCategory() {
        if( !isset( $this->info->category ) ) return false;
        $categories = new \query\categories;
        $categories ->setId( $this->info->category );
        return $categories;
    }

    public function getUser() {
        if( !isset( $this->info->user ) ) return false;
        $users  = new \query\users;
        $users  ->setId( $this->info->user );
        return $users;
    }

    public function getLastUpdateUser() {
        if( !isset( $this->info->lu_user ) ) return false;
        $users  = new \query\users;
        $users  ->setId( $this->info->lu_user );
        return $users;
    }

    public function getUsers_Surveys() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $susers = new user_surveys;
        $susers ->setSurveyId( $id );
        return $susers;
    }

    public function getUsers_Users() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $susers = new users_survey;
        $susers ->setSurveyId( $id );
        return $susers;
    }

    public function getTransactions( int $type = 0 ) {
        $id             = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $transactions   = new \query\transactions;
        $transactions   ->setSurveyId( $id );
        if( $type ) {
            $transactions->setTypeId( $type );
        }
        return $transactions;
    }

    public function getQuestions( int $step = NULL ) {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $questions  = new questions;
        $questions  ->setSurveyId( $id );
        if( $step !== NULL ) {
            $questions->setStepId( $step );
        }
        return $questions;
    }

    public function resultsByCountry( int $status = NULL ) {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $results  = new results_by_country;
        $results  ->setSurveyId( $id );
        if( $status !== NULL ) {
            $results->setStatus( $status );
        }
        return $results;
    }

    public function dashboard( int $user = NULL ) {
        $id     = $this->info->id ?? $this->id;
        $user   = $user ? $user : ( me() ? me()->getId() : NULL );
        if( !$id || !$user ) return false;
        $dashboard  = new dashboard;
        $dashboard  ->setSurveyId( $id );
        $dashboard  ->setUserId( $user );
        return $dashboard;
    }

    public function dashboardMarkup( int $user = NULL ) {
        $dashboard  = $this->dashboard( $user );
        if( !$dashboard ) return false;
        $markup     = new \admin\markup\surveys_dashboard;
        $markup     ->items = [];
        $parts      = [];
        foreach( $dashboard->fetch( -1 ) as $entry ) {
            $markup         ->setObject( $entry );
            $markup->items[]= $markup->markup();
        }
        return $markup;
    }

    public function reports( int $id = NULL, int $user = NULL ) {
        $survey = $this->info->id ?? $this->id;
        $user   = $user ? $user : ( me() ? me()->getId() : NULL );
        if( !$survey || !$user ) return false;
        $reports  = new saved_reports;
        $reports  ->setSurveyId( $survey );
        $reports  ->setUserId( $user );
        if( $id ) {
            $reports->setId( $id );
        }
        return $reports;
    }

    public function reportMarkup( int $id, int $user = NULL ) {
        $reports    = $this->reports( $id, $user );
        if( !$reports ) return false;
        if( !( $report = $reports->getObject() ) ) return false;
        $markup     = new \admin\markup\report\report;
        $markup     ->setObject( $report );
        return $markup;
    }

    public function stats() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $stats   = new \query\stats\survey;
        $stats   ->setSurveyId( $id );
        return $stats;
    }

    public function meta() {
        $id     = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $meta   = new meta;
        $meta   ->setId( $id );
        return $meta;
    }

    public function actions() {
        $id         = $this->info->id ?? $this->id;
        if( !$id ) return false;
        $actions    = new \user\survey_actions;
        $actions    ->setId( $id );
        return $actions;
    }

    public function importFromTemplate( string $template ) {
        $tool   = new tools\import_from_template;
        $tool   ->setSurvey( $this )
                ->setTemplate( $template );
        return $tool->import();
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'id';
        $list['id_desc']    = 'id DESC';
        $list['name']       = 'name';
        $list['name_desc']  = 'name DESC';
        $list['date']       = 'date';
        $list['date_desc']  = 'date DESC';

        return $this->filters->do_filter( 'surveys_order_by_values', $list );
    }

    public function orderBy( $values ) {
        if( !is_array( $values ) ) {
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
        return $this->filters->do_filter( 'surveys_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'surveys_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'surveys' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        return $this->filters->do_filter( 'surveys_count', $count );
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'surveys' );
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
        $query .= $this->table( 'surveys' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'surveys_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'surveys_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
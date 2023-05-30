<?php

namespace admin\markup;

class surveys_dashboard extends report\question {

    private $id;
    private $info;
    protected $callbacks;
    private $result;

    function __construct( int $id = 0 ) {
        parent::__construct();

        // Set dashboard id
        $this->setId( $id );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setObject( object $info ) {
        $this->info = $info;
        return $this;
    }

    public function getId() : int {
        return ( $this->info->id ?? $this->id );
    }
    
    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getType() {
        return $this->info->type;
    }

    public function getTypeId() {
        return $this->info->type_id;
    }

    public function getObject() {
        if( !empty( $this->info ) ) return $this->info;
        else if( $this->id ) {
            $dashboard  = new \query\survey\dashboard;
            $dashboard  ->setId( $this->id );
            $info       = $dashboard->getObject();
            $this       ->setObject( $info );
            return $info;
        }

        return false;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getCallbacks() {
        return $this->callbacks;
    }

    public function modifyResult() {
        return $this->result;
    }

    public function typeStats() {
        $markup = '
        <div class="q">
            <h3><span>' . t( 'Responses' ) . '</span></h3>
            <div class="' . ( $uqid = 'stat_' . uniqid() ) . '"><i class="fas fa-circle-notch fa-spin"></i></div>
        </div>';

        $dates  = [ explode( '-', date( 'Y-m-d', strtotime( '-7 days' ) ) ), explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'today' ) ) ), true ];
        $stats  = responsesStats()
                ->setSurveyId( $this->info->survey )
                ->setStatus( 3 );
        $stats  ->generateReport( ...$dates );

        $aprvd  = $stats->autoFillDates();

        $stats  ->setStatus( 0 );
        $stats  ->generateReport( ...$dates );
        $rejct  = $stats->autoFillDates();

        $data   = [];
        $data[] = [ 'Name', t( 'Approved responses' ), t( 'Rejected responses' ) ];
        foreach( array_reverse( $aprvd ) as $res ) {
            $data[] = [ $res->date, $res->total, $rejct[$res->date]->total ];
        }

        $this->result[ 'load_scripts' ] = [ 'https://www.gstatic.com/charts/loader.js?mku=0' => '{
            "callback": "populate_chart3",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "table": ".' . $uqid . '",
            "data": ' . cms_json_encode( $data ) . '
        }' ];

        return $markup;
    }

    public function typeQuestion() {
        $results    = surveyResults( $this->info->survey )
                    ->setQuestionId( $this->info->type_id )
                    ->results();

        $this       ->setQuestion( $this->info->type_id )
                    ->setResults( (array) $results );
        return $this->questionMarkup();
    }

    public function markup() {
        if( !isset( $this->info->type ) ) return ;
        switch( $this->info->type ) {
            case 0:
                return $this->typeStats();
            break;

            case 1:
                return $this->typeQuestion();
            break;
        }
    }
    
}
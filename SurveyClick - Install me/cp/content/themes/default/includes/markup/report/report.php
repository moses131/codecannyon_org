<?php

namespace admin\markup\report;

class report extends question {

    private $id;
    private $info;

    function __construct( int $id = 0 ) {
        parent::__construct();

        // Set report id
        $this->setId( $id );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setObject( object $info ) {
        $this->info = $info;
        $this->setResults( (array) $this->getResult() );
        return $this;
    }

    public function getId() : int {
        return ( $this->info->id ?? $this->id );
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getPosition() {
        return $this->info->temp_pos;
    }

    public function getTitle() {
        return $this->info->title;
    }
    
    public function getOptions() {
        return json_decode( $this->info->options, true );
    }

    public function getResult() {
        return json_decode( $this->info->result, true );
    }

    public function getObject() {
        if( !empty( $this->info ) ) return $this->info;
        else if( $this->id ) { 
            $reports    = new \query\survey\saved_reports;
            $reports    ->setId( $this->id );
            $info       = $reports->getObject();
            $this       ->setObject( $info );
            return $info;
        }

        return false;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function questions() {
        $questions  = new \query\survey\questions;
        $questions  ->setSurveyId( $this->getSurveyId() );
        $questions  ->setVisible( 2 );
        $options    = $this->getOptions();
        $qAvailable = survey_types()->getTypesSummary();
        $items      = [];

        foreach( $questions->fetch( -1 ) as $question ) {
            $this       ->setQuestion( $question );
            $questions  ->setObject( $question );
            if( array_search( $questions->getType(), $qAvailable ) === false || 
            ( !empty( $options['show'] ) && array_search( $questions->getId(), $options['show'] ) === false ) ) continue;
            $items[] = $this->questionMarkup(); 
        }

        return $items;
    }
    
}
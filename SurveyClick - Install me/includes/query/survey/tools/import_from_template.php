<?php

namespace query\survey\tools;

class import_from_template extends \util\db {

    private $id;
    private $survey;
    private $templateId;
    private $template;

    function __construct( int $id = NULL ) {
        parent::__construct();
        if( $id )
        $this->setId( $id );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setSurvey( object $survey ) {
        $this->survey   = $survey;
        $this->id       = $survey->getId();
        return $this;
    }

    public function setTemplate( string $template ) {
        $this->templateId = $template;
        return $this;
    }

    public function import() {
        if( !$this->templateId )
        throw new \Exception( t( 'No template selected' ) );
        else if( !$this->survey && !$this->id )
        throw new \Exception( t( 'No survey selected' ) );
        else if( !$this->survey ) {
            $this->survey = surveys( $id );
            if( !$this->survey->getObject() )
            throw new \Exception( t( 'No survey selected' ) );

            $this->survey = $survey;
        }

        $this->template = site()->templates->getTemplate( $this->templateId );

        if( !$this->template )
        throw new \Exception( t( 'Wrong template' ) );

        return $this->importQuestions();
    }

    private function importQuestions() {
        $steps  = $this->survey
                ->getSteps()
                ->setMain()
                ->select( [ 'id' ] )
                ->fetch( 1 );
        $stepId = key( $steps );
        $pos    = 1;

        foreach( $this->template['questions'] as $question ) {
            $this   ->insertQuestion( $question, $pos, $stepId );
            $pos    ++;
        }

        return true;
    }

    private function insertQuestion( array $question, int $position, int $mainStep ) {
        $types  = new \survey\question_types;
        if( !$types->setType( $question['type'] ) )
        return ;

        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'questions' );
        $query  .= ' (survey, user, type, title, info, setting, position, step) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $userId = me()->getId();
        $type   = $types->getType();

        if( !empty( $question['setting'] ) )
        $setting = cms_json_encode( $question['setting'] );

        $stmt   ->bind_param( 'iissssii', $this->id, $userId, $question['type'], $question['title'], $question['description'], $setting, $position, $mainStep );
        $stmt   ->execute();
        $qId    = $stmt->insert_id;
        $stmt   ->close();

        if( isset( $type['importFromTemplate'] ) && is_callable( $type['importFromTemplate'] ) )
            call_user_func( $type['importFromTemplate'], $qId, $question );
        else {
            switch( $question['type'] ) {
                case 'multi':
                case 'checkboxes':
                case 'dropdown':
                    if( isset( $question['options'] ) )
                    $this->insertOptions( $question['options'], $qId, 0 );
                break;

                case 'matrix_mc':
                    if( isset( $question['labels'] ) )
                    $this->insertOptions( $question['labels'], $qId, 1 );
                    if( isset( $question['columns'] ) )
                    $this->insertOptions( $question['columns'], $qId, 2 );
                break;

                case 'matrix_rs':
                    if( isset( $question['labels'] ) )
                    $this->insertOptions( $question['labels'], $qId, 1 );
                break;

                case 'matrix_dd':
                    if( isset( $question['labels'] ) )
                    $this->insertOptions( $question['labels'], $qId, 1 );
                    if( isset( $question['columns'] ) ) {
                        foreach( $question['columns'] as $column => $options ) {
                            $this->insertOptions( [ $column ], $qId, 2, function( $id ) use ( $options ) {
                                $this->insertOptions( $options, $id, 3 );
                            } );
                        }
                    }
                break;
            }
        }
    }

    private function insertOptions( array $options, int $questionId, int $type, callable $cb = NULL ) {
        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'q_options' );
        $query  .= ' (type, type_id, points, title, position) VALUES (?, ?, ?, ?, ?)';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $points = 0;
        $pos    = 1;

        foreach( $options as $option ) {
            $stmt   ->bind_param( 'iiisi', $type, $questionId, $points, $option, $pos );
            $stmt   ->execute();
            $pos    ++;

            if( $cb )
            call_user_func( $cb, $stmt->insert_id );
        }

        $stmt   ->close();
    }

}
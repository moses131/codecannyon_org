<?php

namespace survey;

class response extends proc {

    protected $step;
    protected $step_id;
    protected $data     = [];
    protected $setting  = [];
    protected $lastPoints;
    protected $lastPoints_v;
    protected $lastInputs;
    protected $neverExpires;

    function __construct( object $result, object $survey = NULL, bool $neverExpires = false ) {
        parent::__construct();

        $this->info         = $result;
        $this->survey       = $survey;
        $this->response     = !empty( $this->info->results ) ? json_decode( $this->info->results, true ) : [];

        if( !$neverExpires ) {
            $this->neverExpires = true;
            $this->checkResponse();
        }
    }

    public function reset( object $response = NULL ) {
        if( $response ) {
            $this->info     = $result;
            $this->response = json_decode( $result->results, true );
        } else {
            $this->info     = NULL;
            $this->response = NULL;
        }

        $this->step     = NULL;
        $this->step_id  = NULL;
    }

    public function setResult( object $result ) {
        $this           ->reset();
        $this->info     = $result;
        $this->response = json_decode( $result->response );
    }

    public function setData( array $data ) {
        $this->data = $data;
    }

    public function getResponses() {
        return $this->response;
    }

    public function getResponsePoints() {
        if( !isset( $this->response['pt'] ) ) return 0;
        return array_sum( $this->response['pt'] );
    }

    public function currentStep() {
        if( $this->step_id !== NULL ) {
            return $this->step_id;
        } else if( !empty( $this->response['cs'] ) ) {
            $step = steps( $this->response['cs'] );
            if( $step->getObject() && $step->getSurveyId() == $this->info->survey ) {
                $this->step     = $step;
                $this->step_id  = $step->getId();
                $this->setting  = $step->getSetting();
                if( !empty( $this->setting['time'] ) ) {
                    $this->updateTime( (int) $this->setting['time'] );
                }
                return $step->getId();
            }
        }

        return $this->setMainStep();
    }

    public function step() {
        if( $this->step_id === NULL ) {
            $this->currentStep();
        }

        return $this->step ?? NULL;
    }

    public function getId() {
        return ( $this->info->id ?? $this->id );
    }

    public function getRespondent() {
        return ( $this->info->user ? [ 'user' => $this->info->user ] : [ 'visitor' => $this->info->visitor ] );
    }

    public function getStatus() {
        return $this->info->status;
    }
    
    public function getUserId() {
        return $this->info->user ?? NULL;
    }

    public function getVisitorIp() {
        return $this->info->visitor ?? NULL;
    }

    public function getUser() {
        if( $this->info->user === NULL ) return ;
        $users = new users;
        $users ->setId( $this->info->user );
        return $users;
    }

    public function getSurveyId() {
        return $this->info->survey;
    }

    public function getSurvey() {
        if( !$this->survey ) {
            $survey = surveys();
            $survey ->setId( $this->info->survey );
            if( $survey->getObject() ) {
                $this->survey = $survey;
            }
        }
        return $this->survey;
    }

    public function getVariables() {
        $vars = new \query\survey\result_variables;
        $vars ->setResultId( $this->info->id );
        return $vars;
    }

    public function getQuestions() {
        $step_id    = $this->currentStep();
        $questions  = questions();
        $questions  ->setSurveyId( $this->info->survey )
                    ->setStepId( $step_id );

        return $questions;
    }

    public function getCollectorId() {
        return $this->info->collector;
    }

    public function getCollector() {
        $collectors = new \query\collectors;
        $collectors ->setId( $this->info->collector );
        return $collectors;
    }

    public function getCommission() {
        return ( $this->info->commission + $this->info->commission_bonus );
    }

    public function getCommissionF() {
        return cms_money_format( $this->info->commission + $this->info->commission_bonus );
    }
    
    public function getLoyaltyPoints() {
        return $this->info->lpoints;
    }

    public function getCountry() {
        return $this->info->country;
    }

    public function getFinishedTime() {
        return $this->info->fin;
    }

    public function getExpirationDate() {
        return $this->info->exp;
    }

    public function isExpired() {
        return ( time() > strtotime( $this->info->exp ) );
    }

    public function getDate() {
        return $this->info->date;
    }

    public function getSetting( string $setting ) {
        return ( $this->setting[$setting] ?? NULL );
    }

    public function hasPrevStep() {
        if( $this->getSetting( 'hnav' ) ) return false;
        $ps = $this->response['st'][$this->step_id]['ps'] ?? NULL;
        if( $ps ) return true;
        return false;
    }

    public function goToPrevStep() {
        $cs = $this->currentStep();
        if( $this->getSetting( 'hnav' ) ) return false;
        $ps = $this->response['st'][$cs]['ps'] ?? NULL;
        if( $ps ) {
            $this->response['cs'] = $ps;
            return $this->saveData( cms_json_encode( $this->response ) );
        }
        return false;
    }

    public function checkStep() {
        $answer         = [];
        $errors         = [];
        $questions      = questions();
        $question_types = new \survey\question_types;
        $question_types ->setResponse( $this );
        $step_questions = $this->getQuestions( $this->currentStep() );

        foreach( $step_questions->setVisible( 2 )->fetch( -1 ) as $question ) {
            if( !$question_types->setType( $question->type ) ) {
                return [ 'fatal_error' => true ];
            }

            $questions->setObject( $question );
            
            try {
                $question_types->validate( $questions, ( $this->data[$question->id] ?? [] ) );
            }

            catch( \survey\exceptions\CustomMessage $e ) {
                $errors[$question->id] = $e->getMessage();
            }

            catch( \Exception $e ) {
                $errors[$question->id] = $e->getMessage();
            }
        }

        if( empty( $errors ) ) {
            // verify step
            try {
                $this->lastPoints   = $question_types->getPoints();
                $this->lastPoints_v = $question_types->getValidPoints();
                $this->lastInputs   = $question_types->getInputs();
                $this->strValues    = $question_types->getStrValues();
                $this->verifyStep();
                $saved = true;
            }

            catch( \Exception $e ) {
                $answer['alert'] = $e->getMessage();
            }

            catch( \Exception $e ) {
                $answer['alert'] = $e->getMessage();
            }
        }

        $answer['saved']    = $saved ?? false;
        $answer['errors']   = $errors;

        return $answer;
    }

    private function verifyStep() {
        switch( $this->step->getActionType() ) {
            // finish after
            case 0:
                if( !$this->updateResult() || !$this->saveStatus( 2 ) ) {
                    // can't save results | status
                    throw new \Exception( t( 'Something went wrong' ) );
                }

                return true;
            break;

            // go to another step
            case 1:
                $step = steps();
                if( $step->setId( $this->step->getActionId() )->getObject() && $step->getSurveyId() == $this->getSurveyId() ) {
                    if( !$this->updateResult( $step->getId() ) ) {
                        // can't save results
                        throw new \Exception( t( 'Something went wrong' ) );
                    }

                    return true;
                }

                // the new step does not exist
                throw new \Exception( t( 'Something went wrong' ) );
            break;

            // custom conditions
            case 2:
            case 3:
                $condition = $this->verifyConditions();
                if( $condition ) {
                    switch( $condition->action ) {
                        // finish
                        case 0:
                            if( !$this->updateResult() || !$this->saveStatus( 2 ) ) {
                                // can't save results | status
                                throw new \Exception( t( 'Something went wrong' ) );
                            }

                            return true;
                        break;

                        // disqualify
                        case 1:
                            if( !$this->updateResult() || !$this->saveStatus( 0 ) ) {
                                // can't save results | status
                                throw new \Exception( t( 'Something went wrong' ) );
                            }

                            return true;
                        break;

                        // move to another step
                        case 2:
                            if( !$this->updateResult( $condition->action_id ) ) {
                                // can't save results | status
                                throw new \Exception( t( 'Something went wrong' ) );
                            }

                            return true;
                        break;

                        // message
                        case 3:
                            if( !$this->updateResult() ) {
                                // can't save results
                                throw new \Exception( t( 'Something went wrong' ) );
                            }
                            
                            throw new \survey\exceptions\CustomMessage( esc_html( $condition->emsg ) );
                        break;
                    }
                }
            break;

        }

        // fallback conditions
        switch( $this->step->getActionType() ) {
            // finish, disqualify or show message
            case 2:
                switch( $this->step->getActionId() ) {
                    // finish
                    case 0:
                        if( !$this->updateResult() || !$this->saveStatus( 2 ) ) {
                            // can't save results | status
                            throw new \Exception( t( 'Something went wrong' ) );
                        }

                        return true;
                    break;

                    // disqualify
                    case 1:
                        if( !$this->updateResult() || !$this->saveStatus( 0 ) ) {
                            // can't save results | status
                            throw new \Exception( t( 'Something went wrong' ) );
                        }

                        return true;
                    break;

                    // message
                    case 2:
                        if( !$this->updateResult() ) {
                            // can't save results
                            throw new \Exception( t( 'Something went wrong' ) );
                        }

                        throw new \survey\exceptions\CustomMessage( esc_html( $this->step->getEmsg() ) );
                    break;
                }
            break;

            // go to another step
            case 3:
                $step = steps();
                if( $step->setId( $this->step->getActionId() )->getObject() && $step->getSurveyId() == $this->getSurveyId() ) {
                    if( !$this->updateResult( $step->getId() ) ) {
                        // can't save results
                        throw new \Exception( t( 'Something went wrong' ) );
                    }

                    return true;
                }

                // the new step does not exist
                throw new \Exception( t( 'Something went wrong' ) );
            break;
        }

        return true;
    }

    private function verifyConditions() {
        $query = 'SELECT * FROM ';
        $query .= $this->table( 'step_cond' );
        $query .= ' WHERE survey = ? AND step = ? AND points >= ? LIMIT 1';

        $survey_id = $this->getSurveyId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $survey_id, $this->step_id, $this->lastPoints );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $result->num_rows ) {
            return $fields;
        }

        return false;
    }

    private function updateResult( int $newStep = NULL ) {
        if( empty( $this->response ) ) $this->response = [];
        if( empty( $this->response['sv'] ) ) $this->response['sv'] = [];

        // update values from the current step
        $this->response['st'][$this->step_id]['vl'] = $this->lastInputs;
        $this->response['pt'][$this->step_id]       = $this->lastPoints_v;

        if( !empty( $this->strValues ) ) {
            $this->response['sv'] = $this->strValues + $this->response['sv'];
        }

        if( $newStep ) {
            // next step
            $this->response['st'][$this->step_id]['ns'] = $newStep;
            // previous step
            if( !isset( $this->response['st'][$newStep]['vl'] ) ) {
                $this->response['st'][$newStep]['vl']   = [];
            }
            $this->response['st'][$newStep]['ps']       = $this->step_id;
            // current step
            $this->response['cs']                       = $newStep;
        }

        return $this->saveData( cms_json_encode( $this->response ) );
    }
    
    private function saveData( string $response ) {
        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET results = ?';
        $query .= ' WHERE id = ?';

        $res_id = $this->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $response, $res_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

    private function saveStatus( int $status ) {
        // Survey
        $survey = $this->getSurvey();

        // Disqualify
        if( $status == 0 ) {

            $cAfter = function() {
                filters()->do_filter( 'after-response-disqualify', $this->getRespondent(), $this->getResponses() );
            };

        // Try to finish
        } else if( $status == 2 ) {

            if( !$survey ) {
                return false;
            }

            $minToFinish = (int) $survey->meta()->get( 'minPts', 0 );

            if( $minToFinish && $minToFinish > $this->getResponsePoints() ) {

                // Not enough points to finish
                $status = 0;
                $cAfter = function() {
                    filters()->do_filter( 'after-response-disqualify', $this->getRespondent(), $this->getResponses() );
                };

            } else {

                // Get survey object
                $surveyObj = $survey->getObject();

                // Autovalidate this response
                if( $surveyObj && $survey->autovalidate() ) {
                    $status = 3;
                    $cAfter = function() {
                        filters()->do_filter( 'after-response-finish', $this->getRespondent(), $this->getResponses() );
                    };
                }

            }

        }

        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET status = ?, fin = IF(status = 2 OR status = 3 OR exp > NOW(), NOW(), fin)';
        $query .= ' WHERE id = ?';

        $res_id = $this->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $status, $res_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // Insert results
            if( $status == 3 ) $this->insertResults();
            // Actions after update
            if( isset( $cAfter ) ) $cAfter();
            // Update status
            $this->info->status = $status;
            
            // Webhook
            $pbURL = $survey->meta()->get( 'Webhook', NULL );
            if( $pbURL && filter_var( $pbURL, FILTER_VALIDATE_URL ) )
            filters()->do_filter( 'webhook:after-survey', $this->export(), $pbURL, $this->survey, $res_id );

            return true;
        }

        return false;
    }

    public function validateResponse() {
        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET status = 3';
        $query .= ' WHERE id = ? AND status = 2';

        $res_id = $this->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $res_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            // Insert results
            return $this->insertResults();           
        }

        throw new \Exception( t( 'Unknown' ) );
    }

    public function attachFile( int $question, int $media, string $info = NULL ) {
        $query = 'INSERT INTO ';
        $query .= $this->table( 'survey_attachments' );
        $query .= ' (survey, response, question, media, info) VALUES (?, ?, ?, ?, ?)';

        $survey_id  = $this->getSurveyId();
        $res_id     = $this->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiiis', $survey_id, $res_id, $question, $media, $info );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            return $id;
        }

        return false;
    }

    public function deleteAttachment( int $id ) {
        $query = 'DELETE FROM ';
        $query .= $this->table( 'survey_attachments' );
        $query .= ' WHERE id = ? AND survey = ? AND response = ?';

        $survey_id  = $this->getSurveyId();
        $res_id     = $this->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $id, $survey_id, $res_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return $id;
        }

        return false; 
    }

    public function deleteAttachments( int $question = NULL ) {
        $query = 'DELETE FROM ';
        $query .= $this->table( 'survey_attachments' );
        $query .= ' WHERE survey = ? AND response = ?';
        if( $question )
        $query .= ' AND question = ' . $question;

        $survey_id  = $this->getSurveyId();
        $res_id     = $this->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $survey_id, $res_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false; 
    }

    public function restartResponse() {
        if( $this->getSurvey()->meta()->get( 'restart', false ) ) {
            $query = 'UPDATE ';
            $query .= $this->table( 'results' );
            $query .= ' SET status = 1, results = "", fin = NULL, exp = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?';

            $res_id = $this->getId();
            $exp    = RESPONSE_TIME_LIMIT;

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $exp, $res_id );
            $e = $stmt->execute();
            $stmt->close();

            if( $e ) {
                $this->deleteAttachments();
                return true;
            }
        }

        return false;
    }

    private function updateTime( int $min ) {
        if( $this->neverExpires || empty( $this->setting['time'] ) ) {
            return ;
        }

        if( isset( $this->response['ut'] ) && array_search( $this->step_id, $this->response['ut'] ) !== false ) {
            return;
        }

        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET results = ?, exp = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?';

        $this->response['ut'][] = $this->step_id;

        $res_id = $this->getId();
        $result = cms_json_encode( $this->response );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sii', $result, $min, $res_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            return true;
        }

        return false;
    }

    private function checkResponse() : void {
        if( $this->getStatus() == 1 && $this->isExpired() ) {
            $this->saveStatus( 0 );
        }
    }

    private function setMainStep() {
        $step   = steps();
        $step   ->setSurveyId( $this->info->survey )
                ->setMain();

        if( ( $stepObj = $step->fetch( 1 ) ) && !empty( $stepObj ) && $step->setObject( current( $stepObj ) ) ) {
            $this->step     = $step;
            $this->step_id  = $step->getId();
            $this->setting  = $step->getSetting();
            if( !empty( $this->setting['time'] ) ) {
                $this->updateTime( (int) $this->setting['time'] );
            }
            return $step->getId();
        }

        return false;
    }

}
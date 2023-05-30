<?php

namespace survey;

class proc extends \util\db {

    private $values;
    private $answer;

    function __construct() {
        parent::__construct();
    }

    private function procAnswer() {
        if( isset( $this->answer['int_group'] ) ) {
            $this->valueFromIntGroup();
        } else if( isset( $this->answer['int_cascade'] ) ) {
            $this->valueFromIntCascade();
        } else if( isset( $this->answer['text'] ) ) {
            $this->valueFromText();
        } else if( isset( $this->answer['date'] ) ) {
            $this->valueFromDate();
        } else if( isset( $this->answer['attachments'] ) ) {

        }
    }

    private function valueFromIntGroup() : void {
        foreach( $this->answer['int_group'] as $value ) {
            $this->values[] = [ 'question' => $this->answer['id'], 'type' => 1, 'value' => $value ];
        }
    }

    private function valueFromIntCascade() : void {
        foreach( $this->answer['int_cascade'] as $value => $val ) {
            if( is_array( $val ) ) {
                foreach( $val as $value2 => $value3 ) {
                    $this->values[] = [ 'question' => $this->answer['id'], 'type' => 1, 'value' => $value, 'value2' => $value2, 'value3' => $value3 ];
                }
            } else
            $this->values[] = [ 'question' => $this->answer['id'], 'type' => 1, 'value' => $value, 'value2' => $val ];
        }
    }

    private function valueFromText() : void {
        $this->values[] = [ 'question' => $this->answer['id'], 'type' => 2, 'value_str' => $this->answer['text'] ];
    }

    private function valueFromDate() : void {
        $this->values[] = [ 'question' => $this->answer['id'], 'type' => 3, 'value_date' => date( 'Y-m-d H:i:s', $this->answer['date'] ) ];
    }

    public function addVariable( string $variable, string $value, int $type = 2 ) {
        $res_id = $this->getId();

        $query = 'INSERT INTO ';
        $query .= $this->table( 'result_vars' );
        $query .= ' (result, type, var, value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'iiss', $res_id, $type, $variable, $value );
        $e      = $stmt->execute();
        $stmt   ->close();

        if( $e ) {
            // Hook
            actions()->do_action( 'after-survey-variable-added', $res_id, $type, $variable, $value );

            return true;
        }

        return false;
    }

    protected function insertResults() : void {
        if( isset( $this->response['st'] ) )
        foreach( $this->response['st'] as $step_id => $step ) {
            if( isset( $step['vl'] ) )
            foreach( $step['vl'] as $question => $value ) {
                $this->answer       = $value;
                $this->answer['id'] = $question;
                $this->procAnswer();
            }
        }

        // Save collector's id
        $this->values[] = [ 'type' => 4, 'value' => $this->getCollectorId() ];

        // Save survey's points
        $this->values[] = [ 'type' => 5, 'value' => $this->getResponsePoints() ];

        // Save respondent's country
        if( $this->getCountry() )
        $this->values[] = [ 'type' => 6, 'value_str' => $this->getCountry() ];

        // Save survey's duration
        $this->values[] = [ 'type' => 7, 'value' => ( time() - strtotime( $this->getDate() ) ) ];

        // Save survey's date
        $this->values[] = [ 'type' => 10, 'value_date' => date( 'Y-m-d H:i:s' ) ];

        $survey_id  = $this->getSurveyId();
        $res_id     = $this->getId();

        $query  = 'SELECT * FROM ';
        $query  .= $this->table( 'result_vars' );
        $query  .= ' WHERE result = ?';

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'i', $res_id );
        $stmt   ->execute();
        $vars   = $stmt->affected_rows;
        $result = $stmt->get_result();

        while( ( $row = $result->fetch_assoc() ) ) {
            if( $row['type'] == 1 ) {
                // Save tracking ids
                $this->values[] = [ 'type' => 8, 'value_str' => cms_json_encode( [ $row['var'] => $row['value'] ] ) ];
            } else if( $row['type'] == 2 ) {
                // Save variables
                $this->values[] = [ 'type' => 9, 'value_str' => cms_json_encode( [ $row['var'] => $row['value'] ] ) ];
            }
        }

        if( $vars ) {
            $query  = 'DELETE FROM ';
            $query  .= $this->table( 'result_vars' );
            $query  .= ' WHERE result = ?';
    
            $stmt   = $this->db->stmt_init();
            $stmt   ->prepare( $query );
            $stmt   ->bind_param( 'i', $res_id );
            $stmt   ->execute();
        }

        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'answers' );
        $query  .= ' (survey, result, type, question, value, value2, value3, value_str, value_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt->prepare( $query );

        foreach( $this->values as $value ) {
            $stmt->bind_param( 'iiiiiiiss', $survey_id, $res_id, $value['type'], $value['question'], $value['value'], $value['value2'], $value['value3'], $value['value_str'], $value['value_date'] );
            $stmt->execute();
        }

        $stmt->close();
    }

    public function export() {
        $steps      = new \query\survey\steps;
        $questions  = new \query\survey\questions;
        $options    = new \query\survey\q_options;
        $response   = [];

        if( isset( $this->response['st'] ) )
        foreach( $this->response['st'] as $step_id => $step ) {
            $qs = [];
            if( isset( $step['vl'] ) )
            foreach( $step['vl'] as $question_id => $value ) {
                $data       = [];
                $questions  ->resetInfo()
                            ->setId( $question_id );

                if( $questions->getObject() ) {

                    if( isset( $value['int_group'] ) ) {
                        
                        foreach( $value['int_group'] as $option_id ) {
                            $options->setId( $option_id )
                                    ->resetInfo();
                            if( $options->getObject() )
                            $data[$option_id] = $options->getTitle();
                        }

                    } else if( isset( $value['int_cascade'] ) ) {
      
                        foreach( $value['int_cascade'] as $option_id => $val ) {
                            if( is_array( $val ) ) {
                                $data2 = [];
                                foreach( $val as $value2 => $value3 ) {
                                    $data3 = [];
                                    $options->setId( $value3 )
                                            ->resetInfo();
                                    if( $options->getObject() )
                                    $data3[$value3] = $options->getTitle();

                                    if( empty( $data3 ) )
                                    continue;

                                    $options->setId( $value2 )
                                            ->resetInfo();
                                    if( $options->getObject() )
                                    $data2[$option_id] = [
                                        'type'  => 'option',
                                        'name'  => $options->getTitle(),
                                        'value' => $data3
                                    ];
                                }

                                if( empty( $data2 ) )
                                continue;

                                $options->setId( $option_id )
                                        ->resetInfo();
                                if( $options->getObject() )
                                $data[$option_id] = [
                                    'type'  => 'option',
                                    'name'  => $options->getTitle(),
                                    'value' => $data2
                                ];
                            } else {
                                $data2 = [];
                                $options->setId( $val )
                                        ->resetInfo();
                                if( $options->getObject() )
                                $data2[$val] = $options->getTitle();

                                $options->setId( $option_id )
                                        ->resetInfo();
                                if( $options->getObject() )
                                $data[$option_id] = [
                                    'type'  => 'option',
                                    'name'  => $options->getTitle(),
                                    'value' => $data2
                                ];
                            }
                        }

                    } else if( isset( $value['text'] ) ) {

                        $data = $value['text'];

                    } else if( isset( $value['date'] ) ) {

                        $data = $value['date'];

                    } else if( isset( $value['attachments'] ) ) {

                        foreach( $value['attachments'] as $attachment_id => $attachment ) {
                            $data[$attachment_id] = [
                                'type'      => $attachment['extension'],
                                'filename'  => $attachment['name'],
                                'URL'       => mediaLinks( (int) $attachment['media'] )->getItemURL()
                            ];
                        }

                    }

                    if( empty( $data ) )
                    continue;

                    $qs[$question_id] = [
                        'type'      => $questions->getType(),
                        'name'      => $questions->getTitle(),
                        'value'     => $data,
                        'points'    => $value['points']
                    ];

                }

            }

            $steps  ->resetInfo()
                    ->setId( $step_id );

            $response[$step_id] = [
                'type'      => 'step',
                'name'      => ( $steps->getObject() ? $steps->getName() : '' ),
                'points'    => $this->response['pt'][$step_id] ?? 0,
                'questions' => $qs
            ];
        }

        return $response;
    }

}
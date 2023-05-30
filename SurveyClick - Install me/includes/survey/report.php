<?php

namespace survey;

class report extends report_options {

    private $survey;
    private $question;
    private $user;
    private $types          = 1;
    private $groupBy        = 'a.question, a.value, a.value2, a.value3';

    function __construct( int $survey = 0, int $user = 0 ) {
        parent::__construct();
        $this->setSurveyId( $survey );
        $this->setUserId( $user );
    }

    public function setSurveyId( int $survey ) {
        if( $survey ) $this->survey = $survey;
        return $this;
    }

    public function setUserId( int $user ) {
        if( $user ) $this->user = $user;
        else if( !$user && me() ) $this->user = me()->getId();
        return $this;
    }

    public function setTypes( $type ) {
        $this->types = $type;
        return $this;
    }

    public function setGroupBy( string $type = '' ) {
        switch( $type ) {
            case 'question':
                $this->groupBy = 'a.question, a.value, a.value2, a.value3';
            break;

            default:
                $this->groupBy = 'a.question';
        }
        return $this;
    }

    public function setQuestionId( int $question ) {
        if( $question ) $this->question = $question;
        return $this;
    }

    public function results() {
        $aConds = [];
        $query  = 'SELECT a.question, COUNT(a.value) as `count`, a.value, a.value2, a.value3, a.value_str, a.value_date, a.type, a.result FROM ' . $this->table( 'answers a' );

        if( !empty( $this->answersCond  ) ) {
            $query .= ' INNER JOIN ';
            $i      = 0;
            foreach( $this->answersCond as $type => $conds ) {
                foreach( $conds as $cond ) {
                    $a[] = '(SELECT * FROM ' . $this->table( 'answers' ) . ' WHERE survey = ' . $this->survey . ' AND type = ' . $type .' AND ' . $cond . ') a' . ++$i. ' ON a.result = a' . $i . '.result';
                }
            }
            $query .= implode( ' INNER JOIN ', $a );
        }
        
        if( !empty( $this->labels ) ) {
            $query .= ' RIGHT JOIN ';
            $query .= '(SELECT * FROM ' . $this->table( 'label_items' ) . ' WHERE label IN (' . implode( ',', array_map( 'intval', $this->labels ) ) . ')) l ON l.result = a.result';
        }

        $query .= ' WHERE a.survey = ' . $this->survey;

        if( is_array( $this->types ) ) {
            $query .= ' AND a.type IN (' . implode( ',', array_map( 'intval', $this->types ) ) . ')'; 
        } else {
            $query .= ' AND a.type = ' . (int) $this->types;
        }

        if( $this->question ) {
            $query .= ' AND a.question = ' . $this->question;
        }

        $query .= ' GROUP BY ';
        $query .= $this->groupBy;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            $optId1     = $row['value'];
            $optId2     = $row['value2'];
            $optId3     = $row['value3'];
            $qId        = $row['question'];
            $count      = $row['count'];

            if( $optId3 ) {
                
                $data[$qId]['opts'][$optId1]['opts'][$optId2]['opts'][$optId3] = $count;
                if( !isset( $data[$qId]['opts'][$optId1]['opts'][$optId2]['count'] ) )
                $data[$qId]['opts'][$optId1]['opts'][$optId2]['count'] = $count;
                else $data[$qId]['opts'][$optId1]['opts'][$optId2]['count'] += $count;

            } else if( $optId2 ) {

                $data[$qId]['opts'][$optId1]['opts'][$optId2] = $count;
                if( !isset( $data[$qId]['opts'][$optId1]['count'] ) )
                $data[$qId]['opts'][$optId1]['count'] = $count;
                else $data[$qId]['opts'][$optId1]['count'] += $count;

            } else {

                $data[$qId]['opts'][$optId1] = $count;
                if( !isset( $data[$qId]['count'] ) )
                $data[$qId]['count'] = $count;
                else $data[$qId]['count'] += $count;
            }
        }

        $stmt->close();

        $this->lastReport = $data;

        return $data;
    }

    public function saveHistory( string $title, int $temp = NULL, bool $isNew = false ) {
        if( !$this->user ) 
        return false;

        // New report
        if( $temp == 1 && $isNew ) $this->deleteTemporaryReports();

        $query = 'INSERT INTO ';
        $query .= $this->table( 'saved_reports' );
        $query .= ' (survey, user, temp_pos, title, options, result) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), options = VALUES(options), result = VALUES(result), date = NOW()';

        $options    = cms_json_encode( $this->currentOptions );
        $result     = cms_json_encode( $this->lastReport );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiisss', $this->survey, $this->user, $temp, $title, $options, $result );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            return $id;
        }

        return false;
    }

    public function deleteReport( int $id ) {
        if( !$this->user ) 
        return false;

        $query = 'DELETE FROM ';
        $query .= $this->table( 'saved_reports' );
        $query .= ' WHERE id = ? AND survey = ? AND user = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $id, $this->survey, $this->user );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            actions()->do_action( 'after-deleted-report', $this->id, $this->survey, $this->user );
            return true;
        }

        return false;
    }

    private function deleteTemporaryReports() {
        if( !$this->user ) 
        return false;

        $query = 'DELETE FROM ';
        $query .= $this->table( 'saved_reports' );
        $query .= ' WHERE survey = ? AND user = ? AND temp_pos > 1';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->survey, $this->user );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            return true;
        }

        return false;
    }

}
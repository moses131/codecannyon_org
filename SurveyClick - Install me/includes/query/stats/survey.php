<?php

namespace query\stats;

class survey extends \util\db {

    private $survey;

    function __construct() {
        parent::__construct();
    }

    public function setSurveyId( int $id ) {
        $this->survey = $id;
        return $this;
    }

    // Generate statistics
    public function results( int $from = NULL, int $to = NULL ) {
        $query = 'SELECT SUM(r.approved) as approved, SUM(r.rejected) as rejected, SUM(r.abandoned) as abandoned, SUM(r.duration) as duration FROM (SELECT CASE WHEN status = 3 THEN 1 ELSE 0 END as approved, CASE WHEN status = 3 THEN UNIX_TIMESTAMP(fin) - UNIX_TIMESTAMP(date) ELSE 0 END as duration, CASE WHEN status = 0 AND fin IS NOT NULL THEN 1 ELSE 0 END as rejected, CASE WHEN status = 0 AND fin IS NULL THEN 1 ELSE 0 END as abandoned FROM ';
        $query .= $this->table( 'results' ); 
        $query .= ' WHERE survey = ?';
        if( $from )
        $query .= ' AND date >= FROM_UNIXTIME(' . $from . ')';
        if( $to )
        $query .= ' AND date <= FROM_UNIXTIME(' . $to . ')';
        $query .= ') r';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->survey );
        $stmt->execute();
        $stmt->bind_result( $approved, $rejected, $abandoned, $duration );
        $stmt->fetch();
        $stmt->close();

        return [ 'total' => ( $approved + $rejected + $abandoned ), 'submited' => ( $approved + $rejected ), 'approved' => $approved, 'rejected' => $rejected, 'abandoned' => $abandoned, 'duration' => $duration, 'average_duration' => ( $approved ? round( $duration / $approved ) : NULL ) ];
    }

}
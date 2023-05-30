<?php

namespace query\stats;

class responses extends \util\db {

    private $survey;
    private $user;
    private $visitor;
    private $collector;
    private $status;
    private $last_report    = [];
    private $last_report_from;
    private $last_report_to;
    private $date_format    = 'Y-m-d';

    function __construct() {
        parent::__construct();
    }

    public function setSurveyId( int $id ) {
        $this->survey = $id;
        return $this;
    }

    public function setUserId( int $id ) {
        $this->user = $id;
        return $this;
    }

    public function setVisitorIp( string $id ) {
        $this->visitor = $id;
        return $this;
    }

    public function setCollectorId( array $ids ) {
        $this->collector = $id;
        return $this;
    }

    public function setStatus( int $id ) {
        $this->status = $id;
        return $this;
    }

    public function clearSurvey() {
        $this->survey = NULL;
        return $this;
    }

    public function clearUser() {
        $this->user = NULL;
        return $this;
    }

    public function clearVisitorIp() {
        $this->visitor = NULL;
        return $this;
    }

    public function clearCollector() {
        $this->collector = NULL;
        return $this;
    }

    public function clearStatus() {
        $this->status = NULL;
        return $this;
    }

    public function autoFillDates() {
        $period = new \DatePeriod(
            new \DateTime( $this->last_report_from ),
            new \DateInterval( 'P1D' ),
            new \DateTime( $this->last_report_to )
       );

       $dates = [];

       foreach( $period as $date ) {
            $date           = $date->format( $this->date_format );
            $dates[$date]   = isset( $this->last_report[$date] ) ? $this->last_report[$date] : (object) [ 'total' => 0, 'date' => $date ];
        }

        return $dates;
    }

    private function groupBy( $type = 'day' ) {
        switch( $type ) {
            case 'year':
                $this->date_format = 'Y';
                return '%Y';
            break;

            case 'month':
                $this->date_format = 'Y-m-d';
                return '%Y-%m';
            break;

            case 'week':
                $this->date_format = 'Y-m-d';
                return '%Y-%u';
            break;

            default:
                $this->date_format = 'Y-m-d';
                return '%Y-%m-%d';
        }
    }

    public function dateFormat( string $date_format = 'y-m-d' ) {
        $this->date_format = $date_format;
        return $this;
    }

    public function reportDay( int $year, int $month, int $day ) {
        $str_date   = implode( '-', [ $year, $month, $day ] );
        $dtime      = strtotime( $str_date );
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', $dtime ) ), 
            explode( '-', date( 'Y-m-d', strtotime( 'tomorrow', $dtime )  ) )
        );
    }

    public function reportYearMonth( int $year, int $month, int $day = NULL, bool $saveReport = false ) {
        if( !empty( $day ) ) {
            return $this->reportDay( $year, $month, $day );
        }
        $str_date = implode( '-', [ $year, $month, '01' ] );
        return $this->generateReport( 
            explode( '-', date( 'Y-m-01', strtotime( $str_date ) ) ), 
            explode( '-', date( 'Y-m-t 23:59:59', strtotime( $str_date ) ) ),
            $saveReport
        );
    }

    public function reportToday() {
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', strtotime( 'today' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'today' ) ) )
        );
    }

    public function reportYesterday() {
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', strtotime( 'yesterday' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'yesterday' ) ) )
        );
    }

    public function reportThisWeek( bool $saveReport = false ) {
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', strtotime( 'monday this week - ' .  ( 1 - ( !me()->getFirstDayW() ?: 0 ) ) . ' day' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'monday next week - ' . ( 1 - ( !me()->getFirstDayW() ?: 0 ) ) . ' day' ) ) ),
            $saveReport,
            'week'
        );
    }

    public function reportLastWeek( bool $saveReport = false ) {
        return $this->generateReport(
            explode( '-', date( 'Y-m-d', strtotime( '2 weeks ago monday - ' .  ( 1 - ( !me()->getFirstDayW() ?: 0 ) ) . ' day' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( '1 weeks ago sunday - ' .  ( 1 - ( !me()->getFirstDayW() ?: 0 ) ) . ' day' ) ) ),
            $saveReport,
            'week'
        );
    }
    
    public function reportThisMonth( bool $saveReport = false ) {
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', strtotime( 'first day of this month' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'last day of this month' ) ) ),
            $saveReport,
            'month'
        );
    }

    public function reportLastMonth( bool $saveReport = false ) {
        return $this->generateReport( 
            explode( '-', date( 'Y-m-d', strtotime( 'first day of last month' ) ) ), 
            explode( '-', date( 'Y-m-d 23:59:59', strtotime( 'last day of last month' ) ) ),
            $saveReport,
            'month'
        );
    }

    // Generate report
    public function generateReport( array $from, array $to, bool $saveReport = false, string $groupBy = 'day' ) {
        $query  = 'WITH t AS (SELECT DATE_FORMAT(date, ?) as date, date as o_date FROM ';
        $query  .= $this->table( 'results' ); 
        $qarr   = [];

        if( $this->user ) {
            $qarr[] = 'user = ' . (int) $this->user;
        }

        if( $this->visitor ) {
            $qarr[] = 'survey = "' . $this->dbp( $this->visitor ) . '"';
        }

        if( $this->survey ) {
            $qarr[] = 'survey = ' . (int) $this->survey;
        }

        if( $this->status !== NULL ) {
            $qarr[] = '`status` = ' . (int) $this->status;
        }

        if( $this->collector ) {
            $qarr[] = 'collector = ' . (int) $this->collector;
        }

        $query .= ' WHERE ';

        if( !empty( $qarr ) ) {
            $query .= implode( ' AND ', $qarr );
            $query .= ' AND ';
        }

        $query .= 'date >= ? AND date <= ?) SELECT COUNT(*) as total, t.o_date as o_date, t.date as `date` FROM t GROUP BY t.date';

        $group  = $this->groupBy( $groupBy );
        $from   = implode( '-', $from );
        $to     = implode( '-', $to );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sss', $group, $from, $to );
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            $data[date( $this->date_format, strtotime( $row['o_date'] ) )]   = $this->filters->do_filter( 'responses_stats_info_values', (object) $row );
        }

        if( $saveReport ) {
            $this->last_report      = $data;
            $this->last_report_from = $from;
            $this->last_report_to   = $to;

            return $this;
        }

        $stmt->close();

        return $data;
    }

}
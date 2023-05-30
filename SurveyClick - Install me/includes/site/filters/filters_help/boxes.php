<?php

class boxes {

    function __construct() {
        if( me() ) {
            switch( me()->viewAs ) {
                case 'respondent':
                    filters()->add_filter( 'boxes_respondent', function( $filter, $boxes ) {
                        $boxes['commissions_today']         = [ $this, 'respondent_commissions_today' ];
                        $boxes['commissions_yesterday']     = [ $this, 'respondent_commissions_yesterday' ];
                        $boxes['commissions_this_week']     = [ $this, 'respondent_commissions_this_week' ];
                        $boxes['commissions_last_week']     = [ $this, 'respondent_commissions_last_week' ];
                        $boxes['commissions_this_month']    = [ $this, 'respondent_commissions_this_month' ];
                        $boxes['commissions_last_month']    = [ $this, 'respondent_commissions_last_month' ];

                        $boxes['earnings_today']            = [ $this, 'respondent_earnings_today' ];
                        $boxes['earnings_yesterday']        = [ $this, 'respondent_earnings_yesterday' ];
                        $boxes['earnings_this_week']        = [ $this, 'respondent_earnings_this_week' ];
                        $boxes['earnings_last_week']        = [ $this, 'respondent_earnings_last_week' ];
                        $boxes['earnings_this_month']       = [ $this, 'respondent_earnings_this_month' ];
                        $boxes['earnings_last_month']       = [ $this, 'respondent_earnings_last_month' ];
                        
                        $boxes['responses_today']           = [ $this, 'respondent_responses_today' ];
                        $boxes['responses_yesterday']       = [ $this, 'respondent_responses_yesterday' ];
                        $boxes['responses_this_week']       = [ $this, 'respondent_responses_this_week' ];
                        $boxes['responses_last_week']       = [ $this, 'respondent_responses_last_week' ];
                        $boxes['responses_this_month']      = [ $this, 'respondent_responses_this_month' ];
                        $boxes['responses_last_month']      = [ $this, 'respondent_responses_last_month' ];

                        $boxes['balance']               = [ $this, 'balance' ];
                        $boxes['loyalty_points']        = [ $this, 'loyalty_points' ];
                        return $boxes;
                    } );
                break;

                case 'moderator':
                    filters()->add_filter( 'boxes_moderator', [
                        [ $this, 'test' ]
                    ] );
                break;

                case 'admin':
                case 'owner':
                    filters()->add_filter( 'boxes_' . me()->viewAs, function( $filter, $boxes ) {
                        $boxes['surveys_total']             = [ $this, 'owner_surveys_total' ];
                        $boxes['surveys_active']            = [ $this, 'owner_surveys_active' ];

                        $boxes['users_today']               = [ $this, 'owner_users_today' ];
                        $boxes['users_yesterday']           = [ $this, 'owner_users_yesterday' ];
                        $boxes['users_this_week']           = [ $this, 'owner_users_this_week' ];
                        $boxes['users_last_week']           = [ $this, 'owner_users_last_week' ];
                        $boxes['users_this_month']          = [ $this, 'owner_users_this_month' ];
                        $boxes['users_last_month']          = [ $this, 'owner_users_last_month' ];
                        $boxes['users_total']               = [ $this, 'owner_users_total' ];
                        $boxes['users_verified']            = [ $this, 'owner_users_verified' ];

                        $boxes['deposits_today']            = [ $this, 'owner_deposits_today' ];
                        $boxes['deposits_yesterday']        = [ $this, 'owner_deposits_yesterday' ];
                        $boxes['deposits_this_week']        = [ $this, 'owner_deposits_this_week' ];
                        $boxes['deposits_last_week']        = [ $this, 'owner_deposits_last_week' ];
                        $boxes['deposits_this_month']       = [ $this, 'owner_deposits_this_month' ];
                        $boxes['deposits_last_month']       = [ $this, 'owner_deposits_last_month' ];

                        $boxes['commissions_today']         = [ $this, 'owner_commissions_today' ];
                        $boxes['commissions_yesterday']     = [ $this, 'owner_commissions_yesterday' ];
                        $boxes['commissions_this_week']     = [ $this, 'owner_commissions_this_week' ];
                        $boxes['commissions_last_week']     = [ $this, 'owner_commissions_last_week' ];
                        $boxes['commissions_this_month']    = [ $this, 'owner_commissions_this_month' ];
                        $boxes['commissions_last_month']    = [ $this, 'owner_commissions_last_month' ];

                        $boxes['wcommissions_today']        = [ $this, 'owner_wcommissions_today' ];
                        $boxes['wcommissions_yesterday']    = [ $this, 'owner_wcommissions_yesterday' ];
                        $boxes['wcommissions_this_week']    = [ $this, 'owner_wcommissions_this_week' ];
                        $boxes['wcommissions_last_week']    = [ $this, 'owner_wcommissions_last_week' ];
                        $boxes['wcommissions_this_month']   = [ $this, 'owner_wcommissions_this_month' ];
                        $boxes['wcommissions_last_month']   = [ $this, 'owner_wcommissions_last_month' ];

                        $boxes['surveyors']                 = [ $this, 'owner_surveyors' ];

                        $boxes['responses_today']           = [ $this, 'owner_responses_today' ];
                        $boxes['responses_yesterday']       = [ $this, 'owner_responses_yesterday' ];
                        $boxes['responses_this_week']       = [ $this, 'owner_responses_this_week' ];
                        $boxes['responses_last_week']       = [ $this, 'owner_responses_last_week' ];
                        $boxes['responses_this_month']      = [ $this, 'owner_responses_this_month' ];
                        $boxes['responses_last_month']      = [ $this, 'owner_responses_last_month' ];

                        $boxes['surveys_today']             = [ $this, 'owner_surveys_today' ];
                        $boxes['surveys_yesterday']         = [ $this, 'owner_surveys_yesterday' ];
                        $boxes['surveys_this_week']         = [ $this, 'owner_surveys_this_week' ];
                        $boxes['surveys_last_week']         = [ $this, 'owner_surveys_last_week' ];
                        $boxes['surveys_this_month']        = [ $this, 'owner_surveys_this_month' ];
                        $boxes['surveys_last_month']        = [ $this, 'owner_surveys_last_month' ];

                        $boxes['subscriptions_today']       = [ $this, 'owner_subscriptions_today' ];
                        $boxes['subscriptions_yesterday']   = [ $this, 'owner_subscriptions_yesterday' ];
                        $boxes['subscriptions_this_week']   = [ $this, 'owner_subscriptions_this_week' ];
                        $boxes['subscriptions_last_week']   = [ $this, 'owner_subscriptions_last_week' ];
                        $boxes['subscriptions_this_month']  = [ $this, 'owner_subscriptions_this_month' ];
                        $boxes['subscriptions_last_month']  = [ $this, 'owner_subscriptions_last_month' ];

                        return $boxes;
                    } );
                break;

                case 'surveyor':
                    filters()->add_filter( 'boxes_surveyor', function( $filter, $boxes ) {
                        $boxes['responses_today']           = [ $this, 'surveyor_responses_today' ];
                        $boxes['responses_yesterday']       = [ $this, 'surveyor_responses_yesterday' ];
                        $boxes['responses_this_week']       = [ $this, 'surveyor_responses_this_week' ];
                        $boxes['responses_last_week']       = [ $this, 'surveyor_responses_last_week' ];
                        $boxes['responses_this_month']      = [ $this, 'surveyor_responses_this_month' ];
                        $boxes['responses_last_month']      = [ $this, 'surveyor_responses_last_month' ];
                        
                        $boxes['responses_today_ms']        = [ $this, 'surveyor_responses_today_ms' ];
                        $boxes['responses_yesterday_ms']    = [ $this, 'surveyor_responses_yesterday_ms' ];
                        $boxes['responses_this_week_ms']    = [ $this, 'surveyor_responses_this_week_ms' ];
                        $boxes['responses_last_week_ms']    = [ $this, 'surveyor_responses_last_week_ms' ];
                        $boxes['responses_this_month_ms']   = [ $this, 'surveyor_responses_this_month_ms' ];
                        $boxes['responses_last_month_ms']   = [ $this, 'surveyor_responses_last_month_ms' ];

                        $boxes['surveys_total']             = [ $this, 'surveyor_surveys_total' ];
                        $boxes['surveys_active']            = [ $this, 'surveyor_surveys_active' ];
                        return $boxes;
                    } );
                break;
            }
        }
    }

    public function loyalty_points() {
        return me()->getLoyaltyPoints();
    }

    public function respondent_commissions_today() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportToday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_responses_today' => $done, 'commissions_today' => cms_money_format( $sum ) ];
    }

    public function respondent_commissions_yesterday() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportYesterday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_yesterday' => $done, 'commissions_yesterday' => cms_money_format( $sum ) ];
    }

    public function respondent_commissions_this_week() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportThisWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_this_week' => $done, 'commissions_this_week' => cms_money_format( $sum ) ];
    }

    public function respondent_commissions_last_week() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportLastWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_last_week' => $done, 'commissions_last_week' => cms_money_format( $sum ) ];
    }

    public function respondent_commissions_this_month() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportThisMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_this_month' => $done, 'commissions_this_month' => cms_money_format( $sum ) ];
    }

    public function respondent_commissions_last_month() {
        list( $st, $done, $sum ) = [ stats()->setUserId( me()->getId() ), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportLastMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'commission_last_month' => $done, 'commissions_last_month' => cms_money_format( $sum ) ];
    }

    public function balance() {
        return me()->getBalanceF();
    }

    public function respondent_responses_today() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportToday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function respondent_responses_yesterday() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportYesterday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function respondent_responses_this_week() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportThisWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function respondent_responses_last_week() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportLastWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function respondent_responses_this_month() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportThisMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function respondent_responses_last_month() {
        $report = responsesStats()->setUserId( me()->getId() )->setStatus( 3 )->reportLastMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_earnings_today() {
        $st = earningsStats()->setStatus( 3 )->reportToday();
        if( empty( $st ) ) {
            $st = (object) [ 'responses_done' => 0, 'sum' => 0 ];
        } else {
            $st = current( $st );
            if( $st->sum > 1000 ) {
                $st->sum = (int) $st->sum;
            }
        }
        return [ 'responses_today' => $st->responses_done, 'earnings_today' => cms_money_format( $st->sum ) ];
    }

    public function owner_earnings_yesterday() {
        $st = earningsStats()->setStatus( 3 )->reportYesterday();
        if( empty( $st ) ) {
            $st = (object) [ 'responses_done' => 0, 'sum' => 0 ];
        } else {
            $st = current( $st );
            if( $st->sum > 1000 ) {
                $st->sum = (int) $st->sum;
            }
        }
        return [ 'responses_yesterday' => $st->responses_done, 'earnings_yesterday' => cms_money_format( $st->sum ) ];
    }

    public function owner_earnings_this_week() {
        $st = earningsStats()->setStatus( 3 )->reportThisWeek();
        if( empty( $st ) ) {
            $st = (object) [ 'responses_done' => 0, 'sum' => 0 ];
        } else {
            $st = current( $st );
            if( $st->sum > 1000 ) {
                $st->sum = (int) $st->sum;
            }
        }
        return [ 'responses_this_week' => $st->responses_done, 'earnings_this_week' => cms_money_format( $st->sum ) ];
    }

    public function owner_earnings_last_week() {
        $st = earningsStats()->setStatus( 3 )->reportLastWeek();
        if( empty( $st ) ) {
            $st = (object) [ 'responses_done' => 0, 'sum' => 0 ];
        } else {
            $st = current( $st );
            if( $st->sum > 1000 ) {
                $st->sum = (int) $st->sum;
            }
        }
        return [ 'responses_last_week' => $st->responses_done, 'earnings_last_week' => cms_money_format( $st->sum ) ];
    }

    public function owner_earnings_this_month() {
        $st = earningsStats()->setStatus( 3 )->reportThisMonth();
        if( empty( $st ) ) {
            $st = (object) [ 'responses_done' => 0, 'sum' => 0 ];
        } else {
            $st = current( $st );
            if( $st->sum > 1000 ) {
                $st->sum = (int) $st->sum;
            }
        }
        return [ 'responses_this_month' => $st->responses_done, 'earnings_this_month' => cms_money_format( $st->sum ) ];
    }

    public function owner_deposits_today() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportToday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'count_deposits_today' => $done, 'deposits_today' => cms_money_format( $sum ) ];
    }

    public function owner_deposits_yesterday() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportYesterday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'deposits_yesterday' => $done, 'deposits_yesterday' => cms_money_format( $sum ) ];
    }

    public function owner_deposits_this_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportThisWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'count_deposits_this_week' => $done, 'deposits_this_week' => cms_money_format( $sum ) ];
    }

    public function owner_deposits_last_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportLastWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'count_deposits_last_week' => $done, 'deposits_last_week' => cms_money_format( $sum ) ];
    }

    public function owner_deposits_this_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportThisMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'count_deposits_this_month' => $done, 'deposits_this_month' => cms_money_format( $sum ) ];
    }

    public function owner_deposits_last_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 1 )->setStatus( 2 )->reportLastMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'count_deposits_last_month' => $done, 'deposits_last_month' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_today() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportToday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_today' => $done, 'commissions_today' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_yesterday() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportYesterday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_yesterday' => $done, 'commissions_yesterday' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_this_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportThisWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_this_week' => $done, 'commissions_this_week' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_last_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportLastWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_last_week' => $done, 'commissions_last_week' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_this_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportThisMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_this_month' => $done, 'commissions_this_month' => cms_money_format( $sum ) ];
    }

    public function owner_commissions_last_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 6 )->setStatus( 2 )->reportLastMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'responses_last_month' => $done, 'commissions_last_month' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_today() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportToday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_today' => $done, 'wcommissions_today' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_yesterday() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportYesterday();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_yesterday' => $done, 'wcommissions_yesterday' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_this_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportThisWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_this_week' => $done, 'wcommissions_this_week' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_last_week() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportLastWeek();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_last_week' => $done, 'wcommissions_last_week' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_this_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportThisMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_this_month' => $done, 'wcommissions_this_month' => cms_money_format( $sum ) ];
    }

    public function owner_wcommissions_last_month() {
        list( $st, $done, $sum ) = [ stats(), 0, 0 ];
        if( $st ) {
            $st = $st->setTypeId( 8 )->setStatus( 2 )->reportLastMonth();
            if( !empty( $st ) ) {
                $st     = current( $st );
                $done   = $st->total;
                $sum    = $st->sum >= 1000 ? (int) $st->sum : $st->sum;
            }
        }
        return [ 'wresponses_last_month' => $done, 'wcommissions_last_month' => cms_money_format( $sum ) ];
    }

    public function owner_users_today() {
        $st = usersStats()->reportToday();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_today' => $st->total, 'surveyors_today' => $st->surveyor, 'verified_today' => $st->verified ];
    }

    public function owner_users_yesterday() {
        $st = usersStats()->reportYesterday();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_yesterday' => $st->total, 'surveyors_yesterday' => $st->surveyor, 'verified_yesterday' => $st->verified ];
    }

    public function owner_users_this_week() {
        $st = usersStats()->reportThisWeek();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_this_week' => $st->total, 'surveyors_this_week' => $st->surveyor, 'verified_this_week' => $st->verified ];
    }

    public function owner_users_last_week() {
        $st = usersStats()->reportLastWeek();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_last_week' => $st->total, 'surveyors_last_week' => $st->surveyor, 'verified_last_week' => $st->verified ];
    }

    public function owner_users_this_month() {
        $st = usersStats()->reportThisMonth();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_this_month' => $st->total, 'surveyors_this_month' => $st->surveyor, 'verified_this_month' => $st->verified ];
    }

    public function owner_users_last_month() {
        $st = usersStats()->reportLastMonth();
        if( empty( $st ) ) {
            $st = (object) [ 'total' => 0, 'surveyor' => 0, 'verified' => 0 ];
        } else {
            $st = current( $st );
        }
        return [ 'users_last_month' => $st->total, 'surveyors_last_month' => $st->surveyor, 'verified_last_month' => $st->verified ];
    }

    public function owner_users_total() {
        return users()->count();
    }

    public function owner_users_verified() {
        return users()->setIsVerified()->count();
    }

    public function owner_surveyors() {
        return users()->setIsSurveyor()->count();
    }

    public function owner_surveys_total() {
        return surveys()->count();
    }

    public function owner_surveys_active() {
        return surveys()->setType( 2 )->setStatus( 2 ) ->count();
    }

    public function owner_surveys_today( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) ) $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportToday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_surveys_yesterday( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) )  $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportYesterday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_surveys_this_week( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) ) $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportThisWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_surveys_last_week( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) ) $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportLastWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_surveys_this_month( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) ) $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportThisMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_surveys_last_month( $opt ) {
        $st = surveysStats();
        if( isset( $opt['category'] ) ) $st->setCategoryId( (int) $opt['category'] );
        $report = $st->reportLastMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_today() {
        $report = responsesStats()->setStatus( 3 )->reportToday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_yesterday() {
        $report = responsesStats()->setStatus( 3 )->reportYesterday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_this_week() {
        $report = responsesStats()->setStatus( 3 )->reportThisWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_last_week() {
        $report = responsesStats()->setStatus( 3 )->reportLastWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_this_month() {
        $report = responsesStats()->setStatus( 3 )->reportThisMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_responses_last_month() {
        $report = responsesStats()->setStatus( 3 )->reportLastMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_today() {
        $report = subscriptionsStats()->reportToday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_yesterday() {
        $report = subscriptionsStats()->reportYesterday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_this_week() {
        $report = subscriptionsStats()->reportThisWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_last_week() {
        $report = subscriptionsStats()->reportLastWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_this_month() {
        $report = subscriptionsStats()->reportThisMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function owner_subscriptions_last_month() {
        $report = subscriptionsStats()->reportLastMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_today() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportToday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_yesterday() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportYesterday();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_this_week() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportThisWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_last_week() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportLastWeek();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_this_month() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportThisMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_last_month() {
        $report = me()->getSurveyResponsesStats();
        if( !$report ) return 0;
        $report = $report->setStatus( 3 )->reportLastMonth();
        if( empty( $report ) ) $report = (object) [ 'total' => 0 ];
        else $report = current( $report );
        return $report->total;
    }

    public function surveyor_responses_today_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportToday();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_today_ms' => $total, 'commissions_today_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_responses_yesterday_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportYesterday();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_yesterday_ms' => $total, 'commissions_yesterday_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_responses_this_week_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportThisWeek();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_this_week_ms' => $total, 'commissions_this_week_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_responses_last_week_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportLastWeek();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_last_week_ms' => $total, 'commissions_last_week_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_responses_this_month_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportThisMonth();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_this_month_ms' => $total, 'commissions_this_month_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_responses_last_month_ms() {
        list( $report, $total, $sum ) = [ me()->getMySurveysResponsesStats(), 0, 0 ];
        if( $report ) {
            $report = $report->setStatus( 3 )->reportLastMonth();
            if( !empty( $report ) ) {
                $report = current( $report );
                $total  = $report->total;
                $sum    = $report->sum >= 1000 ? (int) $report->sum : $report->sum;
            }
        }
        return [ 'responses_last_month_ms' => $total, 'commissions_last_month_ms' => cms_money_format( $sum ) ];
    }

    public function surveyor_surveys_total() {
        return (int) me()->getSurveys()->count();
    }

    public function surveyor_surveys_active() {
        return (int) me()->getSurveys()->setStatus( 4 )->count();
    }

}
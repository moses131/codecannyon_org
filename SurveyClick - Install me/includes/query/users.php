<?php

namespace query;

class users extends \util\db {

    private $id;
    private $session_id;
    protected $info;
    private $preferences;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    private $limits;
    private $team;
    private $survey_selected;
    private $membership;
    // db query
    protected $select       = '*';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'users_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function checkUserBySession() {
        if( !isset( $_COOKIE['user_session'] ) ) {
            return false;
        }

        $query = 'SELECT id, user, valid, conf FROM ';
        $query .= $this->table( 'sessions' );
        $query .= ' WHERE session = ? AND expiration > NOW()';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( "s", $_COOKIE['user_session'] );
        $stmt->execute();
        $stmt->bind_result( $id, $user_id, $valid, $conf );
        $stmt->fetch();
        $stmt->close();

        if( $user_id !== NULL ) {
            $this->session_id = $id;
            return (object) [ 'user_id' => $user_id, 'valid' => $valid, 'conf' => $conf ];
        }

        return false;
    }

    public function setIdByUsername( string $username ) {
        $stmt = $this->db->stmt_init();
        $stmt->prepare( "SELECT * FROM " . $this->table( 'users' ) . ' WHERE name = ?' );
        $stmt->bind_param( "s", $username );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $this->id   = $fields->id;
            $this->info = $this->filters->do_filter( 'users_info_values', $fields );
            return $fields->id;
        }

        return false;
    }

    public function setIdByEmail( string $email ) {
        $stmt = $this->db->stmt_init();
        $stmt->prepare( "SELECT * FROM " . $this->table( 'users' ) . ' WHERE email = ?' );
        $stmt->bind_param( "s", $email );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $this->id   = $fields->id;
            $this->info = $this->filters->do_filter( 'users_info_values', $fields );
            return $fields->id;
        }

        return false;
    }

    public function setIdByNameOrEmail( string $name ) {
        $stmt = $this->db->stmt_init();
        $stmt->prepare( "SELECT * FROM " . $this->table( 'users' ) . ' WHERE name = ? OR email = ?' );
        $stmt->bind_param( "ss", $name, $name );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $this->id   = $fields->id;
            $this->info = $this->filters->do_filter( 'users_info_values', $fields );
            return $fields->id;
        }

        return false;
    }

    public function search( string $name ) {
        if( $name !== '' ) {

            filters()->add_filter( 'users_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(name) AGAINST ("*' . $this->dbp( $name ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_name'] = [ [ 'MATCH(name)', 'AGAINST', '*' . $name . '*' ], 'OR', [ 'MATCH(full_name)', 'AGAINST', '*' . $name . '*' ], 'OR', [ 'MATCH(email)', 'AGAINST', '*' . $name . '*' ] ];
        }
        return $this;
    }

    public function setIsSurveyor() {
        $this->conditions['surveyor'] = [ 'surveyor', '=', 1 ];
        return $this;
    }

    public function setTeamId() {
        $this->conditions['team'] = [ 'team', '=', 1 ];
        return $this;
    }

    public function setIsVerified() {
        $this->conditions['verified'] = [ 'verified', '=', 1 ];
        return $this;
    }

    public function setIsBanned() {
        $this->conditions['ban'] = [ 'ban', '>', time() ];
        return $this;
    }

    public function setHasPerm( int $perm, string $op = '=' ) {
        $this->conditions['perm'] = [ 'perm', $op, $perm ];
        return $this;
    }

    public function setCountryId( int $id ) {
        $this->conditions['country'] = [ 'country', '=', $id ];
        return $this;
    }

    public function setLangId( int $id ) {
        $this->conditions['lang'] = [ 'lang', '=', $id ];
        return $this;
    }

    public function setRefId( int $id ) {
        $this->conditions['refid'] = [ 'refId', '=', $id ];
        return $this;
    }

    public function setObject( $info ) {
        $this->info = (object) $info;
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

    public function getSessionId() {
        return $this->session_id;
    }

    public function getName() {
        return $this->info->name;
    }

    public function getFullName() {
        return $this->info->full_name;
    }

    public function getDisplayName() {
        return ( $this->info->dfname && trim( $this->info->full_name ) !== '' ? $this->info->full_name : $this->info->name );
    }

    public function getBirthday() {
        return $this->info->birthday;
    }

    public function getAge() {
        if( !$this->info->birthday )
        return false;

        $birthday   = new \DateTime( $this->info->birthday );

        if( $this->info->tz ) {
            $timezone   = new \DateTimeZone( $this->info->tz );
            $birthday   ->setTimezone( $timezone );
        } else {
            $timezone   = NULL;
        }

        return $birthday->diff( new \DateTime( 'now', $timezone ) )->y;
    }

    public function getGender() {
        return $this->info->gender;
    }

    public function getTeamId() {
        return $this->info->team;
    }

    public function getPassword() {
        return $this->info->password;
    }

    public function getEmail() {
        return $this->info->email;
    }

    public function getAvatarMarkup() {
        if( $this->info->avatar && ( $imageURL = mediaLinks( $this->info->avatar )->getItemURL() ) )
        return '<img src="' . $imageURL . '" alt="">';
        return filters()->do_filter( 'default_user_avatar', '<div class="avt avt-' . strtoupper( $this->info->name[0] ) . '"><span>' . strtoupper( $this->info->name[0] ) . '</span></div>' );
    }

    public function getAvatarURL() {
        if( $this->info->avatar && ( $imageURL = mediaLinks( $this->info->avatar )->getItemURL() ) )
        return $imageURL;
        return false;
    }

    public function getAvatar() {
        return $this->info->avatar;
    }
    
    public function getAddress() {
        return $this->info->address;
    }

    public function getBalance() {
        return ( $this->info->balance + $this->info->bonus );
    }

    public function getRealBalance() {
        return $this->info->balance;
    }

    public function getBonus() {
        return $this->info->bonus;
    }

    public function setBalance( float $amount ) {
        $this->info->balance = $this->info->balance + $this->info->bonus + $amount;
        return $this;
    }

    public function getBalanceF() {
        return cms_money_format( ( $this->info->balance + $this->info->bonus ) );
    }

    public function getRealBalanceF() {
        return cms_money_format( $this->info->balance );
    }

    public function getBonusF() {
        return cms_money_format( $this->info->bonus );
    }

    public function getLoyaltyPoints() {
        return $this->info->lpoints;
    }

    public function getPerm() {
        return $this->info->perm;
    }

    public function hasPerm( int $minPerm = 0 ) {
        return $this->info->perm >= $minPerm ?: false;
    }

    public function getIPAddress() {
        return $this->info->ipaddr;
    }

    public function getLastAction() {
        return $this->info->last_action;
    }

    public function getRefId() {
        return $this->info->refId;
    }

    public function getLoginFailAttempts() {
        return $this->info->fail_attempts;
    }

    public function getBannedUntil() {
        return $this->info->ban;
    }

    public function getCountryId() {
        if( !$this->info->country )
        return DEFAULT_COUNTRY;
        return $this->info->country;
    }

    public function getCountryLastChanged() {
        return $this->info->lcc;
    }

    public function getCurrentCountry( string $key = '' ) {
        if( $key !== '' ) {
            return getCountry( $this->info->country )[$key];
        }
        return getCountry( $this->info->country );
    }

    public function getLanguageId() {
        if( !$this->info->lang )
        return DEFAULT_LANGUAGE;
        return $this->info->lang;
    }

    public function getLanguage( string $key = '' ) {
        if( $key !== '' ) {
            return getLanguage( $this->info->lang )[$key];
        }
        return getLanguage( $this->info->lang );
    }

    public function getTz() {
        return $this->info->tz;
    }

    public function getFirstDayW() {
        return $this->info->fdweek;
    }

    public function getHFormat() {
        return $this->info->f_hour;
    }

    public function getDFormat() {
        return $this->info->f_date;
    }

    public function get2StepVerification() {
        return $this->info->twosv;
    }

    public function getPermalink( string $path = '' ) {
        $link = $this->filters->do_filter( 'user_permalink', false, $this->info->id, $this->info->name );
        if( $path !== '' ) {
            $link = $link . '/' . $path;
        }
        return $link;
    }

    public function getDisplayFullName() {
        return $this->info->dfname;
    }

    public function getLastCountryChanged() {
        return $this->info->lcc;
    }

    public function getAllowTransfer() {
        return $this->info->trans;
    }

    public function isInactive() {
        if( strtotime( $this->info->last_action ) < strtotime( '-7 days' ) ) {
            return true;
        }
        return false;
    }

    public function isModerator() {
        return $this->info->perm >= 1;
    }

    public function isAdmin() {
        return $this->info->perm >= 2;
    }

    public function isOwner() {
        return $this->info->perm >= 3;
    }

    public function isSurveyor() {
        return $this->info->surveyor;
    }

    public function hasEmailVerified() {
        return $this->info->valid;
    }

    public function isVerified() {
        return $this->info->verified;
    }

    public function isBanned() {
        return $this->info->ban && strtotime( $this->info->ban ) > time();
    }

    public function hasProfileCompleted() {
        if( !empty( $this->info->birthday ) && !empty( $this->info->gender ) && !empty( $this->info->country ) ) {
            return true;
        }

        return false;
    }

    public function getPermsArray( bool $hidelp = false ) {
        $perms = [ 0 => t( 'Respondent' ) ];
        if( $this->isSurveyor() )
        $perms[4] = t( 'Surveyor' );
        if( $this->isModerator() && ( !$hidelp || !$this->isAdmin() ) ) 
        $perms[1] = t( 'Moderator' ); 
        if( $this->isAdmin() && ( !$hidelp || !$this->isOwner() ) ) 
        $perms[2] = t( 'Administrator' );
        
        if( $this->isOwner() )
        $perms[3] = t( 'Owner' );
        return $perms;
    }

    public function getInfoListMarkup() {
        $list = [];
        if( $this->isSurveyor() )
        $list['surveyor'] = '<i class="fas fa-user-check"></i>' . t( 'Surveyor' );
        if( $this->info->perm == 1 )
        $list['moderator'] = '<i class="fas fa-user-shield"></i>' . t( 'Moderator' ); 
        if( $this->info->perm == 2 )
        $list['admin'] = '<i class="fas fa-user-shield"></i>' . t( 'Administrator' );
        if( $this->isOwner() )
        $list['owner'] = '<i class="fas fa-user-shield"></i>' . t( 'Owner' );
        if( $this->isBanned() )
        $list['banned'] = '<i class="fas fa-user-slash"></i>' . t( 'Banned' );

        return '<ul class="uinfol">' . implode( "\n", array_map( function( $v ) { return '<li>' . $v . '</li>'; }, filters()->do_filter( 'user_info_list', $list ) ) ) . '</ul>';
    }

    public function manageTeam( string $option ) {
        return $this->manageSurvey( $option, NULL, $this->team->getId() );
    }

    public function manageSurvey( string $option, $survey = NULL, int $teamId = NULL ) {
        $id = $this->info->id ?? $this->id;

        if( !$teamId ) {
            if( !( $surveys = $this->selectSurvey( $survey ) ) )
            return false;

            if( $this->isAdmin() || ( $this->survey_selected->userInfo && !$this->survey_selected->userInfo->team ) )
            return true;

            if( !$this->survey_selected->userInfo )
            return false;

            $teamId = $this->survey_selected->userInfo->team;
        }

        if( !isset( $this->membership[$teamId] ) ) {
            $membership                 = new \query\team\members;
            $this->membership[$teamId]  = $membership->userMemberInfo( $this->id, $teamId );
        }

        if( empty( $this->membership[$teamId] ) )
        return false;

        if( $this->membership[$teamId]->perm == 2 )
        return true;

        $perms  = !empty( $this->membership[$teamId]->perms ) ? json_decode( $this->membership[$teamId]->perms, true ) : [];

        switch( $option ) {
            case 'view': 
                return true;

            case 'add-survey':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['ps'] ) || !empty( $perms['ps'] ) );
            
            case 'edit-survey':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['es'] ) );

            case 'approve-response':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['ps'] ) || !empty( $perms['ar'] ) );

            case 'reject-response':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['rr'] ) );

            case 'add-response':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['adr'] ) || !empty( $perms['adr'] ) );

            case 'manage-question':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['mq'] ) || !empty( $perms['mq'] ) );

            case 'manage-collector':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['mc'] ) || !empty( $perms['mc'] ) );

            case 'view-result':
                return ( $this->membership[$teamId]->perm > 0 || !isset( $perms['vr'] ) || !empty( $perms['vr'] ) );
            
            case 'send-invitation':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['inv'] ) );

            case 'cancel-invitation':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['cinv'] ) );

            case 'edit-team':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['et'] ) );

            case 'edit-settings':
                return ( $this->membership[$teamId]->perm > 0 || !empty( $perms['est'] ) );
        }

        return false;
    }

    public function invoicing() {
        return new \site\invoicing( $this );
    }

    public function actions() {
        return new \user\user_actions( $this );
    }

    public function admin_actions() {
        return new \user\admin_actions( $this );
    }

    public function manage() {
        return new \user\user_manage( $this );
    }

    public function forms() {
        return new \user\forms( $this );
    }

    public function form_actions() {
        return new \user\form_actions( $this );
    }

    public function admin_forms() {
        return new \user\admin_forms( $this );
    }

    public function admin_form_actions() {
        return new \user\admin_form_actions( $this );
    }

    public function team_forms() {
        return new \user\team_forms( $this );
    }

    public function team_form_actions() {
        return new \user\team_form_actions( $this );
    }

    public function website_options() {
        return new \user\site_options( $this );
    }

    public function mail( string $template = '' ) {
        $mail = new \services\email( $this );
        if( $template != '' )
        $mail->setTemplate( $template );
        return $mail;
    }

    public function getVouchers( bool $activeOnly = true ) {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $vouchers   = new user_vouchers;
        $vouchers   ->setUserId( $id );
        if( $activeOnly )
        $vouchers->setNotExpired()->setNotUsed()->setStatus( 1 );
        return $vouchers;
    }

    public function getSurveys() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $surveys    = new survey\user_surveys;
        $surveys    ->setUserId( $id );
        return $surveys;
    }

    public function selectSurvey( $survey = NULL ) {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) 
        return ;

        if( !$survey ) {
            if( $this->survey_selected )
            return $this->survey_selected;
            return false;
        }

        if( is_object( $survey ) )
            $surveys    = $survey;
        else {
            $surveys    = new survey\surveys;
            $surveys    ->setId( $survey );
        }

        if( $surveys->getObject() ) {
            $this->survey_selected  = $surveys;
            $this->survey_selected  ->userInfo = NULL;
            $userInfo               = new survey\user_surveys;
            $this->survey_selected  ->userInfo  = $userInfo->userSurveyInfo( $surveys->getId(), $id );
            return $this->survey_selected;
        }

        return false;
    }

    public function getSelectedSurvey() {
        return $this->survey_selected;
    }

    public function getSurveyResponses() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $surveys    = new survey\user_survey_responses;
        $surveys    ->setUserId( $id );
        return $surveys;
    }

    public function getFavorites() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $favorites  = new favorites;
        $favorites  ->setUser( $id )
                    ->setUserId( $id );
        return $favorites;
    }

    public function getSaved() {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $saved  = new saved;
        $saved  ->setUser( $id )
                ->setUserId( $id );
        return $saved;
    }

    public function getResults() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $results    = new \query\survey\results;
        $results    ->setUser( $id )
                    ->setUserId( $id );
        return $results;
    }

    public function getAlerts() {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $alerts = new alerts;
        $alerts ->setUserId( $id );
        return $alerts;
    }

    public function getTransactions( int $type = 0 ) {
        $id             = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $transactions   = new transactions;
        $transactions   ->setUserId( $id );
        if( $type )
        $transactions->setTypeId( $type );
        return $transactions;
    }

    public function getStats( int $type = 0 ) {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $stats  = new \query\stats\transactions;
        $stats  ->setUserId( $id );
        if( $type )
        $stats->setTypeId( $type );
        return $stats;
    }

    public function getEarningsStats( int $type = 0 ) {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $stats  = new \query\stats\earnings;
        $stats  ->setUserId( $id );
        return $stats;
    }

    public function getSurveyResponsesStats() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $stats      = new \query\stats\survey_responses;
        $stats      ->setUserId( $id );
        return $stats;
    }

    public function getMySurveysResponsesStats() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $stats      = new \query\stats\my_surveys_responses;
        $stats      ->setUserId( $id );
        return $stats;
    }

    public function getCountry() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $countries  = new countries;
        $countries  ->setId( $this->info->country );
        return $countries;
    }

    public function intents( int $type = NULL, int $status = NULL ) {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $intents    = new user_intents;
        $intents    ->setUserId( $id );
        if( $type )
        $intents    ->setTypeId( $type );
        if( $status )
        $intents    ->setStatus( $status );
        return $intents;
    }

    // Options
    public function getOptions() {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $options    = new user_options;
        $options    ->setUserId( $id );
        return $options;
    }

    public function getOption( string $name ) {
        $id         = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $options    = new user_options;
        $options    ->setUserId( $id )
                    ->infoSave( $name );
        return $options;
    }

    // Limits
    public function limits() {
        $id = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $limits = new user_limits( $id );
        return $limits;
    }

    public function myLimits() {
        if( !$this->limits )
        $this->limits = $this->limits();
        return $this->limits;
    }

    // Team
    public function team( int $team = NULL ) {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $team   = $team ?? ( $this->info->team ?? NULL );
        if( !$team ) return ;
        $team = new team\teams( $team, $id );
        return $team;
    }

    public function myTeam( int $team = NULL, bool $saveTeam = true ) {
        if( !$this->team || !$saveTeam ) {
            if( !( $team = $this->team( $team ) ) || !$team->getObject() ) return ;
            $this->team                         = $team;
            $membership                         = new \query\team\members;
            $this->membership[$team->getId()]   = $membership->userMemberInfo( $this->id, $team->getId() );
            if( !$this->membership[$team->getId()] ) return ;
        }
        return $this->team;
    }

    public function myTeams() {
        $id     = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $teams  = new \query\team\user_teams;
        $teams  ->setUserId( $id )
                ->setApproved();
        return $teams;
    }

    public function getTeamMemberId() {
        if( $this->team ) return $this->membership[$this->team->getId()]->id ?? 0;
        else if( !empty( $this->info->team ) ) return $this->membership[$this->info->team]->id ?? 0;
        return 0;
    }

    public function getTeamMemberPermissions() {
        if( $this->team ) return $this->membership[$this->team->getId()]->perm ?? 0;
        else if( !empty( $this->info->team ) ) return $this->membership[$this->info->team]->perm ?? 0;
        return 0;
    }

    public function getTeamMembership() {
        if( $this->team ) return $this->membership[$this->team->getId()] ?? [];
        else if( !empty( $this->info->team ) ) return $this->membership[$this->info->team] ?? [];
        return [];
    }

    public function getTeamChatLastAction() {
        if( $this->team ) return $this->membership[$this->team->getId()]->last_action_chat ?? NULL;
        else if( !empty( $this->info->team ) ) return $this->membership[$this->info->team]->last_action_chat ?? NULL;
        return ;
    }

    public function getDate() {
        return $this->info->date;
    }

    public function custom_time( $datetime_str = NULL, int $just_str = 0, string $format = '' ) {
        $date_time  = new \util\date_time;
        $date_time  ->currentUser( 
            $this->getDFormat(), 
            $this->getHFormat(), 
            $this->getTz() 
        );

        return custom_time( $datetime_str, $just_str, $format, $date_time );
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['last_act']       = 'last_action';
        $list['last_act_desc']  = 'last_action DESC';
        $list['points']         = 'points';
        $list['points_desc']    = 'points DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';

        return $this->filters->do_filter( 'users_order_by_values', $list );
    }

    public function orderBy( $values ) {
        if( is_string( $values ) ) {
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
        return $this->filters->do_filter( 'users_per_page', $this->items_per_page );
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
        if( ( $count = $this->filters->do_filter( 'users_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'users' ); 
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'users_count', $count );

        return false;
    }

    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'users' );
        $query .= ' WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'users_info_values', $fields );
        }

        return false;
    }

    // Fetch entries
    public function fetch( int $max = 0, int $offset = 0 ) {
        $limit = '';
        
        if( $max != 0 ) {
            if( $max > 0 )
            $limit = ' LIMIT ' . ( $offset ? $offset . ',' : '' ) . $max;
        } else {
            $count = $this->count();
                
            if( !$count ) {
                return [];
            }

            $items_per_page = $this->itemsPerPage();

            if( $items_per_page ) {
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

                $limit = ' LIMIT ' . ( ( $current_page - 1 ) * $per_page ) . ', ' . $per_page;
            }
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'users' ); 
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'users_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'users_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

    
    public function ownSurveys() {
        $id = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'surveys' );
        $query .= ' WHERE user = ?';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        return $count;
    }

    public function updateLastAction() {
        $id = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET last_action = NOW() WHERE id = ?';
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            return true;
        }

        return false;;
    }

    public function updateTeamLastAction() {
        $id = $this->info->id ?? $this->id;
        if( empty( $id ) ) return ;
        $m  = $this->getTeamMembership();
        if( empty( $m ) ) return ;

        $query  = 'UPDATE ';
        $query .= $this->table( 'teams_members' );
        $query .= ' SET last_action_chat = NOW() WHERE id = ?';
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $m->id );
        $e = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            return true;
        }

        return false;;
    }

}
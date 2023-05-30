<?php

namespace query\team;

class members extends \util\db {

    private $id;
    protected $info;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'u.*, tm.id as tm_id, tm.inviter as tm_inviter, tm.team as tm_team, tm.perm as tm_perm, tm.perms as tm_perms, tm.approved as tm_approved, tm.date as tm_date';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'team_members_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function excludeUserId( int $id ) {
        $this->conditions['exclude_' . $id] = [ 'tm.user', '!=', $id ];
        return $this;
    }

    public function setTeamId( int $id ) {
        $this->conditions['team'] = [ 'tm.team', '=', $id ];
        return $this;
    }

    public function setApproved( int $type = 1 ) {
        $this->conditions['approved'] = [ 'tm.approved', '=', $type ];
        return $this;
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject() {
        if( empty( $this->info ) ) {
            $this->info = $this->info();
        }
        return $this->info;
    }

    public function getId() : int {
        return $this->info->tm_id;
    }

    public function getUserId() {
        return $this->info->id;
    }

    public function getInviterId() {
        return $this->info->tm_inviter;
    }

    public function getPerm() {
        return $this->info->tm_perm;
    }

    public function getPerms() {
        return $this->info->tm_perms;
    }

    public function getPermsAsArray() {
        return ( !empty( $this->info->tm_perms ) ? json_decode( $this->info->tm_perms, true ) : [] );
    }

    public function isApproved() {
        return $this->info->tm_approved;
    }

    public function getDate() {
        return $this->info->tm_date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function isOwner() {
        return ( $this->user == $this->info->user );
    }

    public function isAdmin() {
        if( $this->isOwner() || $this->isAdministrator() ) 
        return true;
    }

    public function getInviter() {
        $users  = new \query\users;
        $users  ->setId( $this->info->tm_inviter );
        return $users;
    }

    public function getUserObject( $info = NULL ) {
        $users    = new \query\users;
        if( !empty( $info ) ) {
            $users->setObject( $info );
        } else {
            $users->setObject( $this->info );
        }
        return $users;
    }

    private function orderBy_values() {
        $list               = [];
        $list['id']         = 'tm.id';
        $list['id_desc']    = 'tm.id DESC';
        $list['date']       = 'tm.date';
        $list['date_desc']  = 'tm.date DESC';

        return $this->filters->do_filter( 'team_members_order_by_values', $list );
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
        return $this->filters->do_filter( 'team_members_per_page', $this->items_per_page );
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'team_members_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'teams_members tm' );
        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'team_members_count', $count );

        return false;
    }

    public function userMemberInfo( int $user, int $team ) {
        $query = 'SELECT id, perm, perms, last_action_chat FROM ';
        $query .= $this->table( 'teams_members' );
        $query .= ' WHERE user = ? AND team = ? AND approved = 1 LIMIT 1';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $user, $team );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $fields;
        }

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'teams_members tm' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'users u' );
        $query .= ' ON (tm.user = u.id) ';
        $query .= ' WHERE tm.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'team_members_info_values', $fields );
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
        $query .= $this->table( 'teams_members tm' );
        $query .= ' LEFT JOIN ';
        $query .= $this->table( 'users u' );
        $query .= ' ON (tm.user = u.id) ';
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
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'team_members_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'team_members_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

}
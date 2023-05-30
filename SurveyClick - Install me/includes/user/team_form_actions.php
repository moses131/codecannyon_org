<?php

namespace user;

class team_form_actions extends \util\db {

    private $user;
    private $user_obj;

    function __construct( $user ) {
        parent::__construct();

        if( gettype( $user ) == 'object' ) {
            $this->user_obj     = $user;
            $this->user         = $this->user_obj->getId();
        } else if( $user == 0 ) {
            $this->user_obj     = me();
            $this->user         = $this->user_obj->getId();
        } else {
            $this->setUser( $user );
        }
    }

    public function setUser( int $user ) {
        $users = users( $user );
        if( $users->getObject() ) {
            $this->user         = $users->getId();
            $this->user_obj     = $users;
        }
        return $this;
    }

    public function edit_team( object $team, array $data ) {
        $data = filters()->do_filter( 'edit-team-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) ) {
            throw new \Exception( t( 'Something went wrong' ) );
        }

        $query  = 'UPDATE ';
        $query .= $this->table( 'teams' );
        $query .= ' SET name = ? WHERE id = ?';

        $t_id = $team->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['name'], $t_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-edit-team', $team, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function invite( object $team, array $data ) {
        $limit  = $team->limits()->teamMembers();
        if( $limit > 0 && $limit <= $team->members()->count() ) {
            throw new \Exception( t( 'Your team has reached the members limit' ) );
        }

        $data           = filters()->do_filter( 'invite-team-form-sanitize-data', $data );
        $data['name']   = isset( $data['name'] ) ? trim( $data['name'] ) : false;

        if( empty( $data['name'] ) )
        throw new \Exception( t( 'Something went wrong' ) );

        $user   = users();

        if( !$user->setIdByNameOrEmail( $data['name'] ) )
        throw new \Exception( t( 'This member does not exist' ) );

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'teams_members' );
        $query .= ' (user, team, approved, inviter) VALUES(?, ?, 0, ?)';

        $t_id   = $team->getId();
        $t_uid  = $user->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $t_uid, $t_id, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-team-invite', $team, $user, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function remove_member( object $team, int $user, array $data ) {
        $query  = 'DELETE FROM ';
        $query .= $this->table( 'teams_members' );
        $query .= ' WHERE user = ? AND team = ?';

        $t_id   = $team->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $user, $t_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-member-removed', $team, $user, $data );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function edit_member_permissions( object $team, object $user, array $data ) {
        // User is not a member of the team
        if( !( $uTeam = $user->myTeam( $team->getId() ) ) )
        throw new \Exception( t( 'Unexpected' ) );

        $data   = filters()->do_filter( 'edit-member-permissions-form-sanitize-data', $data );

        if( !isset( $data['perm'] ) ) {
            throw new \Exception( t( 'Something went wrong' ) );
        }

        if( filters()->do_filter( 'custom-error-edit-member-permissions', false, $team, $user, $data ) ) {
            throw new \Exception( $errors );
        }

        switch( $data['perm'] ) {
            case 'admin': $perm = 1; break;

            default: 
                $perm   = 0;
                $perms  = cms_json_encode( array_merge( [ 'ps' => 0, 'es' => 0, 'ar' => 0, 'rr' => 0, 'adr' => 0, 'et' => 0, 'mq' => 0, 'mc' => 0, 'vr' => 0, 'inv' => 0, 'cinv' => 0, 'est' => 0 ], ( $data['perms'] ?? [] ) ) );
        }

        $m_id   = $user->getTeamMemberId();

        $query  = 'UPDATE ' . $this->table( 'teams_members' );
        $query .= ' SET perm = ?, perms = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'isi', $perm, $perms, $m_id );
        $e  = $stmt->execute();
        $stmt->close();

        if( $e ) {            
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_collaborator( int $user, object $survey ) {
        $team   = $this->user_obj->myTeam();
        if( !$team || $this->user_obj->getTeamMemberPermissions() < 2 || !$team->userIsMember( $user ) )
        throw new \Exception( t( 'Unexpected' ) );

        $query  = 'INSERT INTO ' . $this->table( 'usr_surveys' );
        $query .= ' (user, survey, team) VALUES (?, ?, ?)';

        $t_id   = $team->getId();
        $s_id   = $survey->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iii', $user, $s_id, $t_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-collaborator-added', $user, $survey );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function remove_collaborator( int $user, object $survey ) {
        $team   = $this->user_obj->myTeam();
        if( !$team || $this->user_obj->getTeamMemberPermissions() < 2 || !$team->userIsMember( $user ) )
        throw new \Exception( t( 'Unexpected' ) );

        $query  = 'DELETE FROM ' . $this->table( 'usr_surveys' );
        $query .= ' WHERE user = ? AND survey = ?';

        $t_id   = $team->getId();
        $s_id   = $survey->getId();
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $user, $s_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-collaborator-removed', $user, $survey );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

}
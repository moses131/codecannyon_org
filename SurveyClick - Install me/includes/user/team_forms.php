<?php

namespace user;

class team_forms {

  private $user;
  private $user_obj;
  private $last_form;

  function __construct( $user ) {
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

  public function getForm() {
      return $this->last_form;
  }

  public function modifyForm( callable $fct ) {
      $this->last_form = $fct;
      return $this;
  }

  private function modifyLastForm( $form ) {
      if( is_callable( $this->last_form ) )
      call_user_func( $this->last_form, $form );
  }

  public function edit_team( object $team, array $attributes = [] ) {
    $form = new \markup\front_end\form_fields( [
      'name'    => [ 'type' => 'text', 'label' => t( 'Name' ), 'value' => $team->getName(), 'required' => 'required' ],
      'button'  => [ 'type' => 'button', 'label' => t( 'Save' ) ]
    ] );

    $this->modifyLastForm( $form );
    $fields                   = $form->build();
    $attributes['data-ajax']  = ajax()->get_call_url( 'manage-team2', [ 'action2' => 'edit', 'team' => $team->getId() ] );
    $attributes['enctype']    = 'multipart/form-data';

    $markup = '<form class="form edit_team_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_team_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $markup .= $fields;
    $markup .= '</form>';

    return $markup;
  }

  public function invite( object $team, array $attributes = [] ) {
    $form = new \markup\front_end\form_fields( [
      'name'    => [ 'type' => 'text', 'label' => t( 'Username or email address' ), 'required' => 'required' ],
      'button'  => [ 'type' => 'button', 'label' => t( 'Invite' ) ]
    ] );

    $this->modifyLastForm( $form );
    $fields                   = $form->build();
    $attributes['data-ajax']  = ajax()->get_call_url( 'manage-team2', [ 'action2' => 'invite', 'team' => $team->getId() ] );
    $attributes['enctype']    = 'multipart/form-data';

    $markup = '<form class="form invite_member_team_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'invite_member_team_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $markup .= $fields;
    $markup .= '</form>';

    return $markup;
  }

  public function edit_member_permissions( object $team, object $user, array $attributes = [] ) {
    // Check if the user is a member
    if( !( $userTeam = $user->myTeam( $team->getId() ) ) ) return ;

    $form = new \markup\front_end\form_fields( [
      'perm'      => [ 'type' => 'select', 'label' => t( 'Permissions' ), 'options' => [ 'member' => t( 'Member' ), 'admin' => t( 'Admin' ) ] ],
      'perms'     => [ 'type' => 'checkboxes', 'label' => t( 'Custom permissions' ), 'options' => [
          'ps'    => t( 'Create new surveys' ),
          'es'    => t( 'Edit surveys' ),
          'ar'    => t( 'Approve responses' ),
          'rr'    => t( 'Reject responses' ),
          'adr'   => t( 'Add responses' ),
          'et'    => t( 'Edit team' ),
          'mq'    => t( 'Manage questions (add, edit, remove)' ),
          'mc'    => t( 'Manange collectors (add, edit, remove)' ),
          'vr'    => t( 'View results, generate reports, save reports, etc' ),
          'inv'   => t( 'Send invitations' ),
          'cinv'  => t( 'Cancel invitations' ),
          'est'   => t( 'Edit settings (logo, meta-tags, etc)' ),
      ], 'when' => [ '=', 'data[perm]', 'member' ] ],
      'button'    => [ 'type' => 'button', 'label' => t( 'Save' ) ]
    ] );

    $form->setValues( [
        'perm'  => ( $user->getTeamMemberPermissions() == 1 ? 'admin' : 'member' ),
        'perms' => [
            'ps'    => ( $user->manageTeam( 'add-survey' ) ? 'ps' : '' ),
            'es'    => ( $user->manageTeam( 'edit-survey' ) ? 'es' : '' ),
            'ar'    => ( $user->manageTeam( 'approve-response' ) ? 'ar' : '' ),
            'rr'    => ( $user->manageTeam( 'reject-responses' ) ? 'rr' : '' ),
            'adr'   => ( $user->manageTeam( 'add-response' ) ? 'adr' : '' ),
            'et'    => ( $user->manageTeam( 'edit-team' ) ? 'et' : '' ),
            'mq'    => ( $user->manageTeam( 'manage-question' ) ? 'mq' : '' ),
            'mc'    => ( $user->manageTeam( 'manage-collector' ) ? 'mc' : '' ),
            'vr'    => ( $user->manageTeam( 'view-result' ) ? 'vr' : '' ),
            'inv'   => ( $user->manageTeam( 'send-invidation' ) ? 'inv' : '' ),
            'cinv'  => ( $user->manageTeam( 'cancel-invitation' ) ? 'cinv' : '' ),
            'est'   => ( $user->manageTeam( 'edit-settings' ) ? 'est' : '' )
        ]
    ] );

    $this->last_form            = $form;
    $fields                     = $form->build();
    $attributes['data-ajax']    = ajax()->get_call_url( 'manage-team2', [ 'action2' => 'member-permissions', 'member' => $user->getId(), 'team' => $team->getId() ] );
    $attributes['enctype']      = 'multipart/form-data';

    $markup = '<form id="team_member_perms" class="form team_member_perms_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'team_member_perms_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $markup .= $fields;
    $markup .= '</form>';

    return $markup;
  }

}
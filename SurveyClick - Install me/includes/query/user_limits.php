<?php

namespace query;

class user_limits extends \util\db {

    private $id;
    private $plan;

    function __construct( int $id = NULL ) {
        parent::__construct();

        if( $id )
        $this->setUserId( $id );
    }

    public function setUserId( int $id ) {
        $this->id = $id;

        if( !( $this->plan = $this->getSubscription() ) )
        $this->plan = $this->getFreeSubscription();
    }

    public function setFreeSubscription() {
        $this->plan = $this->getFreeSubscription();
        return $this;
    }

    public function getPlanId() {
        return $this->plan['id'];
    }

    public function getSubscriptionId() {
        return $this->plan['subId'];
    }

    public function getPrice() {
        return $this->plan['price'];
    }

    public function getPlanName() {
        return $this->plan['name'];
    }

    public function isFree() {
        return $this->plan['isFree'];
    }

    public function surveys() {
        return $this->plan['surveys'];
    }
    
    public function responses() {
        return $this->plan['responses'];
    }

    public function questions() {
        return $this->plan['questions'];
    }

    public function collectors() {
        return $this->plan['collectors'];
    }

    public function teamMembers() {
        return $this->plan['tmembers'];
    }

    public function removeBrand() {
        return $this->plan['rBrand'];
    }

    public function space() {
        return $this->plan['space'];
    }

    public function autorenew() {
        return $this->plan['autorenew'];
    }

    public function expiration() {
        return $this->plan['expiration'];
    }

    public function getPlan() {
        return $this->plan;
    }

    public function getFreeSubscription() {
        if( ( $options = get_option( 'default_plan' ) ) && ( $options = json_decode( $options, true ) ) )
        return array_merge( $this->getPlanFromDefaults(), $options );
        return $this->getPlanFromDefaults();
    }

    private function getSubscription() {
        $query = 'SELECT p.*, s.id as subid, s.autorenew as autorenew, s.expiration as expiration FROM ';
        $query .= $this->table( 'subscriptions s' );
        $query .= ' RIGHT JOIN ';
        $query .= $this->table( 'plans p' );
        $query .= ' ON p.id = s.plan';
        $query .= ' WHERE s.user = ? AND s.paid = 1';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields )
        return $this->getPlanFromSubscription( $fields );
        return false;
    }

    private function getPlanFromSubscription( object $fields ) {
        return [
            'id'        => $fields->id,
            'subId'     => $fields->subid,
            'isFree'    => false,
            'price'     => $fields->price,
            'name'      => $fields->name,
            'surveys'   => $fields->sur,
            'responses' => $fields->res_p_sur,
            'questions' => $fields->que_p_sur,
            'collectors'=> $fields->col,
            'tmembers'  => $fields->tm,
            'rBrand'    => $fields->r_brand,
            'space'     => $fields->space,
            'available' => ( $fields->visible >= 2 ),
            'autorenew' => $fields->autorenew,
            'expiration'=> $fields->expiration
        ];
    }

    private function getPlanFromDefaults() {
        return [
            'id'        => 0,
            'subId'     => 0,
            'isFree'    => true,
            'price'     => 0.00,
            'name'      => t( 'Free' ),
            'surveys'   => 5,
            'responses' => 50,
            'questions' => 10,
            'collectors'=> 1,
            'tmembers'  => 0,
            'rBrand'    => false,
            'space'     => 0,
            'available' => true,
            'autorenew' => true,
            'expiration'=> false
        ];
    }

}
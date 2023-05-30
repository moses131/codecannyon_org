<?php

namespace user;

class user_manage extends \util\db {

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

    public function add_credit( float $balance, float $bonus_balance = 0.00 ) {
        if( !$this->user )
        throw new \Exception( t( 'User not set' ) );

        $query = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET balance = balance + ?, bonus = bonus + ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ddi', $balance, $bonus_balance, $this->user );
        $e  = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            actions()->do_action( 'user-manage-add-credit', $this->user_obj, $balance, $bonus_balance );
            return true;
        }
        
        throw new \Exception( t( 'Unexpected' ) );
    }

    public function extend_subscription( $subscription, int $months = 1 ) {
        if( !$this->user )
        throw new \Exception( t( 'User not set' ) );
        
        if( !$subscription ) {
            $subscription   = subscriptions();
            $subscription   ->getObjectFromUserSubscription( $this->user );
        } if( is_numeric( $subscription ) ) {
            $subId          = $subscription;
            $subscription   = subscription()
                            ->setId( $subId );
        }

        if( !$subscription->getObject() || $this->user != $subscription->getUserId() || !( $plan = $subscription->getPlan() ) || !$plan->getObject() )
            throw new \Exception( t( 'Unexpected' ) );
        else if( $this->user_obj->getBalance() < ( $price = $plan->priceMonths( $months ) * $months ) )
            throw new \Exception( t( 'Balance is too low' ) );

        $subId  = $subscription->getId();

        $query  = 'UPDATE ';
        $query .= $this->table( 'subscriptions' );
        $query .= ' SET expiration = DATE_ADD(expiration, INTERVAL +? MONTH) WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $months, $subId );
        $e  = $stmt->execute();
        $stmt->close();
    
        if( $e ) {
            actions()->do_action( 'after:upgrade', 'wallet', 'extend', $this->user_obj, $months, $plan );

            $query = 'INSERT INTO ';
            $query .= $this->table( 'transactions' );
            $query .= ' (user, type, amount, details, status) VALUES (?, 5, ?, ?, 2)';

            $details = cms_json_encode( [ 'Method' => 'wallet' ] );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ids', $this->user, $price, $details );
            $e  = $stmt->execute();
            $stmt->close();

            // Save credit
            $this->add_credit( -$price );

            return true;
        }
        
        throw new \Exception( t( 'Unexpected' ) );
    }

}
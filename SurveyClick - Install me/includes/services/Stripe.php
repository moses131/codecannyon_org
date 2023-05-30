<?php

namespace services;

class Stripe implements \interfaces\Payment_method {

    private $amount;
    private $title;
    private $description;
    private $invoice;
    private $returnURL;
    private $cancelURL;
    private $params = [ 'gateway' => 'stripe' ];

    function __construct( array $opts = [] ) {
        $this->title        = sprintf( '%s deposit', esc_html( get_option( 'website_name' ) ) );
        $this->description  = t( 'Deposit' );
        $this->invoice      = uniqid();
        $this->returnURL    = $opts['returnURL'] ?? null;
        $this->cancelURL    = $opts['cancelURL'] ?? null;

        \Stripe\Stripe::setApiKey( get_option( 'stripe_secret_key' ) );
    }

    public function setAmount( float $amount ) {
        $this->amount = $amount;
        return $this;
    }

    public function setTitle( string $title ) {
        $this->title = $title;
        return $this;
    }

    public function setDescription( string $description ) {
        $this->description = $description;
        return $this;
    }

    public function setInvoice( string $invoice ) {
        $this->invoice = $invoice;
        return $this;
    }

    public function setReturnURL( string $url ) {
        $this->returnURL = $url;
        return $this;
    }

    public function setCancelURL( string $url ) {
        $this->cancelURL = $url;
        return $this;
    }

    public function setParam( string $pname, $pvalue ) {
        $this->params[$pname] = $pvalue;
        return $this;
    }

    public function executePayment() {
        if( !isset( $_GET['transactionId'] ) ) {
            throw new \Exception( 'Payment canceled' );
        }
        
        $trans  = new \query\transactions;

        // Return if the transaction exists
        if( $trans->transactionInfo( $_GET['transactionId'] ) )
        return true;

        $session    = \Stripe\Checkout\Session::retrieve( $_GET['transactionId'] );
        $customer   = \Stripe\Customer::retrieve( $session->customer );
        $voucher    = '';

        if( isset( $_GET['voucher'] ) )
        $voucher = me()->actions()->applyVoucher( (int) $_GET['voucher'], (double) $total );

        return [
            'id'            => $session->id,
            'status'        => $session->payment_status == 'paid' ? 'approved' : 'failed',
            'total'         => ( $session->amount_total / 100 ),
            'description'   => [
                'method'    => 'stripe',
                'id'        => $session->id,
                'payer'     => [
                    'name'      => $session->customer_details->name,
                    'phone'     => $session->customer_details->phone,
                    'email'     => $session->customer_details->email,
                    'address'   => cms_json_encode( $session->customer_details->address ),
                ],
                'voucher'   => $voucher
            ] 
        ];
    }

    private function returnURL() {
        $this->returnURL .= '&' . http_build_query( $this->params );
        return $this->returnURL;
    }

    private function cancelURL() {
        $this->cancelURL .= '&' . http_build_query( $this->params );
        return $this->cancelURL;
    }

    public function getRedirectURL() {
        $session = \Stripe\Checkout\Session::create( [
            'line_items' => [ [
                'name'      => $this->title,
                'amount'    => $this->amount * 100,
                'currency'  => PAYMENT_CURRENCY,
                'quantity'  => 1,
            ] ],
            'mode'          => 'payment',
            'success_url'   => $this->returnURL() . '&transactionId={CHECKOUT_SESSION_ID}',
            'cancel_url'    => $this->cancelURL()
        ] );

        return $session['url'];
    }
}
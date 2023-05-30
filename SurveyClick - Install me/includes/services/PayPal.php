<?php

namespace services;

class PayPal implements \interfaces\Payment_method {

    private $apiContext;
    private $amount;
    private $title;
    private $description;
    private $invoice;
    private $returnURL;
    private $cancelURL;
    private $params = [ 'gateway' => 'paypal' ];

    function __construct( array $opts = [] ) {
        $this->title        = sprintf( '%s deposit', esc_html( get_option( 'website_name' ) ) );
        $this->description  = t( 'Deposit' );
        $this->invoice      = uniqid();
        $this->returnURL    = $opts['returnURL'] ?? null;
        $this->cancelURL    = $opts['cancelURL'] ?? null;
        $this->apiContext   = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
            get_option( 'paypal_client_id' ),
            get_option( 'paypal_secret' )
            )
        );
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
        if( !isset( $_GET['paymentId'] ) || !isset( $_GET['PayerID'] ) )
        throw new \Exception( 'Payment canceled' );
        
        $trans  = new \query\transactions;

        // Return if the transaction exists
        if( $trans->transactionInfo( $_GET['paymentId'] ) )
        return true;

        $payment    = \PayPal\Api\Payment::get( $_GET['paymentId'], $this->apiContext );

        $execution  = new \PayPal\Api\PaymentExecution();
        $execution  ->setPayerId( $_GET['PayerID'] );

        try {
            $result = $payment->execute( $execution, $this->apiContext );
            $tId    = $result->getId();
            $payer  = $result->getPayer()->getPayerInfo();
            $total  = 0;

            foreach( $result->getTransactions() as $transaction ) {
                $total += $transaction->getAmount()->getTotal();
            }

            $voucher = '';
            
            if( isset( $_GET['voucher'] ) )
            $voucher = me()->actions()->applyVoucher( (int) $_GET['voucher'], (double) $total );

            return [
                'id'            => $tId,
                'status'        => $result->getState(),
                'total'         => $total,
                'description'   => [
                    'method'    => 'paypal',
                    'id'        => $tId,
                    'payer'     => [
                        'name'      => $payer->getFirstName() . ' ' . $payer->getLastName(),
                        'email'     => $payer->getEmail(),
                        'country'   => $payer->getCountryCode(),
                    ],
                    'voucher'   => $voucher
                ] 
            ];
        } catch( Exception $ex ) {
            ResultPrinter::printError( 'Get Payment', 'Payment', null, null, $ex );
            exit(1);
        }
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
        $payer          = new \PayPal\Api\Payer();
        $payer          ->setPaymentMethod( 'paypal' );

        $amount         = new \PayPal\Api\Amount();
        $amount         ->setCurrency( PAYMENT_CURRENCY )
                        ->setTotal( $this->amount );

        $transaction    = new \PayPal\Api\Transaction();
        $transaction    ->setAmount( $amount )
                        ->setDescription( $this->description )
                        ->setInvoiceNumber( $this->invoice );

        $redirectUrls   = new \PayPal\Api\RedirectUrls();
        $redirectUrls   ->setReturnUrl( $this->returnURL() )
                        ->setCancelUrl( $this->cancelURL() );

        $payment        = new \PayPal\Api\Payment();
        $payment        ->setIntent( 'order' )
                        ->setPayer( $payer )
                        ->setRedirectUrls( $redirectUrls )
                        ->setTransactions( [ $transaction ] );
        try {
            $payment->create( $this->apiContext );
        } catch( Exception $e ) {
            echo $ex->getMessage();
            exit(1);
        }

        return $payment->getApprovalLink();
    }
}
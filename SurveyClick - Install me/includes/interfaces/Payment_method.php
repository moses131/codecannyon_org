<?php

namespace interfaces;

interface Payment_method {

    public function setAmount( float $amount );

    public function setTitle( string $title );

    public function setDescription( string $description );

    public function setReturnURL( string $url );

    public function setCancelURL( string $url );

    public function executePayment();

    public function getRedirectURL();
    
}
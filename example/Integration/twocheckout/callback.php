<?php
include_once '../../../lib/PHPpayments/Loader.php';

$paymentmethod = "Payment_Integration_Twocheckout";
$obj_payment = PHPpayments_Loader::load ( $paymentmethod );

//set fields that needs to be validated in ipn response, query own db first
$obj_payment->addFieldOrder ( "currency", "EUR" );
$obj_payment->addFieldOrder ( "total", "100");
//enable sandbox environment
$obj_payment->enableTestMode ();
//set credentials
$obj_payment->addFieldSetting ( "account", "???account #???" );
$obj_payment->addFieldSetting ( "secret", "???secret word???");
//validate the callback
$obj_result = $obj_payment->validateIpn ( $_POST );

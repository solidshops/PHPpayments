<?php
include_once '../../../lib/PHPpayments/Loader.php';

$paymentmethod = "Payment_Integration_Sisow";
$obj_payment = PHPpayments_Loader::load ( $paymentmethod );

//set fields that needs to be validated in ipn response, query own db first
$obj_payment->addFieldOrder ( "currency", "EUR" );
$obj_payment->addFieldOrder ( "total", "100");
//enable sandbox environment
$obj_payment->enableTestMode ();
//set credentials
$obj_payment->addFieldSetting ( "account", "2537309337" );
$obj_payment->addFieldSetting ( "secret", "d0a3672f0dddedec6536969f45c60e7e6e623492");
//validate the callback
$obj_result = $obj_payment->validateIpn ( $_GET );

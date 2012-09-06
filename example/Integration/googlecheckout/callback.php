<?php
include_once '../../../lib/PHPpayments/Loader.php';


$paymentmethod = "Payment_Integration_Googlecheckout";
$obj_payment = PHPpayments_Loader::load ( $paymentmethod );


//get payment details from google
$arr_datadetail = $obj_payment->getDetail ($arr_data [$key] ['serial-number'] );

//set fields that needs to be validated in ipn response, query own db first
$obj_payment->addFieldOrder ( "currency", "EUR" );
$obj_payment->addFieldOrder ( "total", "100");
//enable sandbox environment
$obj_payment->enableTestMode ();
//set credentials
$obj_payment->addFieldSetting ( "account", "???Google Merchant ID???" );
$obj_payment->addFieldSetting ( "secret", "???Google Merchant key???");
//validate the callback
$obj_result = $obj_payment->validateIpn ( $arr_datadetail );

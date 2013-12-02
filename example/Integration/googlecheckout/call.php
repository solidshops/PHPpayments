<?php

include_once '../../../src/PHPpayments/Loader.php';

$domain = "http://www.domain.com";
$paymentmethod = "Payment_Integration_Googlecheckout";

$obj_payment = P\PHPpayments\Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "account", "???Google Merchant ID???" );
$obj_payment->addFieldSetting ( "secret", "???Google Merchant key???");

//include dummy order data
include_once "../../orderdata.php";

//set urls for after payment
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/confirmorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCancel ( $domain . "/cancelorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//enable sandbox environment
$obj_payment->enableTestMode ();

//map our data structure with payment providers structure
$obj_payment->preparePayment ();

//redirect to payment provider
echo $obj_payment->url_integration;
//$obj_payment->processPayment ();

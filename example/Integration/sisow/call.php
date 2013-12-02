<?php

include_once '../../../src/PHPpayments/Loader.php';

$domain = "http://www.domain.com";
$paymentmethod = "Payment_Integration_Sisow";

$obj_payment = \PHPpayments\Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "account", "???" );
$obj_payment->addFieldSetting ( "secret", "???");

//include dummy order data
include_once "../../orderdata.php";

//set urls for after payment
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/confirmorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCancel ( $domain . "/cancelorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//enable sandbox environment
$obj_payment->enableTestMode ();

//set extra payment options specific bank in this case
$obj_payment->setPaymentOption ( 99);

//map our data structure with payment providers structure
$obj_payment->preparePayment ();

//redirect to payment provider
echo $obj_payment->url_integration;
//$obj_payment->processPayment ();

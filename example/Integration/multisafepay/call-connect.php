<?php

include_once '../../../lib/PHPpayments/Loader.php';

$domain = "http://www.domain.com";
$paymentmethod = "Payment_Integration_Multisafepay";

$obj_payment = PHPpayments_Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "payment_product", "connect" );
$obj_payment->addFieldSetting ( "account", "???" );
$obj_payment->addFieldSetting ( "secret", "???");
$obj_payment->addFieldSetting ( "secret2", "???" );


//include dummy order data
include_once "../../orderdata.php";

//set urls for after payment
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/confirmorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCancel ( $domain . "/cancelorder/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//dump payment providers
$obj_payment->getPaymentOptions ();

//enable sandbox environment
$obj_payment->enableTestMode ();

//map our data structure with payment providers structure
$obj_payment->preparePayment ();

//redirect to payment provider
echo $obj_payment->url_integration;
//$obj_payment->processPayment ();

<?php

include_once '../../../lib/PHPpayments/Loader.php';

$domain = "http://www.domain.com";
$paymentmethod = "Payment_Integration_Paypalwebsitepaymentsstandard";

$obj_payment = PHPpayments_Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "account", "yourpaypalemail@domain.com" );

//include dummy order data
include_once "../../orderdata.php";

//set urls for after payment
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/orderconfirmation/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCancel ( $domain . "/ordercancel/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//enable sandbox environment
$obj_payment->enableTestMode ();

//map our data structure with payment providers structure
$obj_payment->preparePayment ();

//redirect to payment provider
echo $obj_payment->url_integration;
//$obj_payment->processPayment ();

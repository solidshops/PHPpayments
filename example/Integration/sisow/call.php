<?php

include_once '../../../lib/PHPpayments/Loader.php';

$domain = "http://www.domain.com";
$paymentmethod = "Payment_Integration_Sisow";

$obj_payment = PHPpayments_Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "account", "???" );
$obj_payment->addFieldSetting ( "secret", "???");
//set order data
$obj_payment->addFieldOrder ( "guid", "12313212313212.3" );
$obj_payment->addFieldOrder ( "id", "1000" );
$obj_payment->addFieldOrder ( "currency", "EUR" );
$obj_payment->addFieldOrder ( "total", 100 );
$obj_payment->addFieldOrder ( "email", "name@domain.com");
//set billing data	
$obj_payment->addFieldBilling ( "firstname", "John" );
$obj_payment->addFieldBilling ( "lastname", "Doe " );
$obj_payment->addFieldBilling ( "companyname", "Billing company" );
$obj_payment->addFieldBilling ( "address1", "Billing street1" );
$obj_payment->addFieldBilling ( "address2", "Billing street2" );
$obj_payment->addFieldBilling ( "city","Billing city" );
$obj_payment->addFieldBilling ( "state", "Billing state");
$obj_payment->addFieldBilling ( "zip", "Billing zip" );
$obj_payment->addFieldBilling ( "country", "Billing country" );
$obj_payment->addFieldBilling ( "phone", "Billing phone");
//set shipping data if available	
$obj_payment->addFieldShipping ( "firstname", "Jane" );
$obj_payment->addFieldShipping ( "lastname", "Doe " );
$obj_payment->addFieldShipping ( "companyname", "Shipping company" );
$obj_payment->addFieldShipping ( "address1", "Shipping street1" );
$obj_payment->addFieldShipping ( "address2", "Shipping street2" );
$obj_payment->addFieldShipping ( "city", "Shipping city" );
$obj_payment->addFieldShipping ( "state", "Shipping state" );
$obj_payment->addFieldShipping ( "zip", "Shipping zip" );
$obj_payment->addFieldShipping ( "country", "Shipping country" );
$obj_payment->addFieldShipping ( "phone", "Shipping phone" );
//enable sandbox environment
$obj_payment->enableTestMode ();
//set urls
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/orderconfirmation/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCancel ( $domain . "/ordercancel/guid/" . $obj_payment->arr_order['guid'] );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//set extra payment options specific bank in this case
$obj_payment->setPaymentOption ( 99);

//map our data structure with payment providers structure
$obj_payment->preparePayment ();
//redirect to payment provider

echo $obj_payment->url_integration;
//$obj_payment->processPayment ();

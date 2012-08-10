<?php
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

?>
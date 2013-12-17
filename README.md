#PHPpayments


Payment wrapper class for PHP created by @SolidShops


##Installation
You can [download the latest version](http://github.com/solidshops/phppayments/zipball/master) or use composer:

```json
{
	"require": {
		"solidshops/phppayments": "dev-master"
	}
}
```

##Supported payment methods
**Offline:**

* Banktransfer
* Cash on delivery
* Cheque
* Pickup

**Integration:**

* [Authorize](http://www.authorize.net/)
* [Mollie](https://www.mollie.nl/)
* [MultiSafePay](https://www.multisafepay.com/)
* [Ogone](http://www.ogone.com/)
* [Paypal website payments standard](https://www.paypal.com)
* [Sisow](https://www.sisow.nl/)
* [2checkout](https://www.2checkout.com/)

##Example

```php
$paymentmethod = "Payment_Integration_Paypalwebsitepaymentsstandard";
$obj_payment = \PHPpayments\Loader::load ( $paymentmethod );

//set credentials
$obj_payment->addFieldSetting ( "account", "yourpaypalemail@domain.com" );

//set order data
$obj_payment->addFieldOrder ( "guid", "123132123132123" );
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

//set urls for after payment
$domain = "http://www.domain.com";
$obj_payment->setUrlSite ( $domain );
$obj_payment->setUrlSuccess ( $domain . "/aftersuccess" );
$obj_payment->setUrlCancel ( $domain . "/aftercancel" );
$obj_payment->setUrlCallback ( $domain . "/callback" );

//enable sandbox environment
$obj_payment->enableTestMode ();

//map our data structure with payment providers structure
$obj_payment->preparePayment ();

//redirect to payment provider
$obj_payment->processPayment ();

```
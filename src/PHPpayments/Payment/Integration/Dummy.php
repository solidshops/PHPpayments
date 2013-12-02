<?php

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;
class Payment_Integration_Dummy extends Payment_Integration implements Payment_IntegrationInterface {
	//the base url of the integration endpoint
	public $url_submit = "http://production.domain.com/cgi-bin/webscr"; 
	//the name of the integration
	public $shortname = "dummy"; 
	
	/*
	 * Wether the payment should be send to the real or test environment.
	 */
	public function enableTestMode() {
		parent::enableTestMode ();
		$this->url_submit = "http://acceptance.domain.com/cgi-bin/webscr";
	}
	
	/*
	 * The url where the user is redirect to should be set to in this method.
	 * Depending on the paymentprovider: -> you should redirect the user and add
	 * fields to the uri as parameters -> do a SOAP or REST call and get a url
	 */
	public function preparePayment() {
		$this->url_integration = "";
	}
	
	/*
	 * Most paymentproviders send a message to your server when a payment is
	 * completed or cancelled. The uri where the notification is send to is set
	 * with "->setUrlCallback()" and the value is given to the paymentprovider
	 * in the redirect url with "->preparePayment()" @return void
	 */
	public function validateIpn($arr_params) {
		try {
			//The PaymentResult object is instantiated
			parent::validateIpn ();
			//if all checks are fine
			$this->payment_result->confirmed = 1;
			$this->payment_result->log .= "extra logging";
		} catch ( Exception $e ) {
			//when an error occured
			$this->payment_result->confirmed = 0;
			$this->payment_result->log .= "CATCH:" . print_r ( $e, true );
			$this->payment_result->error = 001;
		}
		return $this->payment_result;
	}
}

?>
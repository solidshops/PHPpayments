<?php

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;


class Payment_Integration_Paypalwebsitepaymentsstandard extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "https://www.paypal.com/cgi-bin/webscr";
	public $shortname ="paypal";
	
	public function enableTestMode() {
		parent::enableTestMode ();
		$this->url_submit = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	}
	public function preparePayment() {
		
		
		$this->arr_payment ['charset'] = "utf-8";
		$this->arr_payment ['business'] = $this->arr_settings ['account'];
		$this->arr_payment ['cmd'] = "_xclick";
		$this->arr_payment ['notify_url'] = $this->url_callback;
		$this->arr_payment ['return'] = $this->url_return_success;
		$this->arr_payment ['rm'] = "1";
		$this->arr_payment ['cbt'] = "Return to " . $this->url_site;
		
		$this->arr_payment ['undefined_quantity'] = "0";
		$this->arr_payment ['no_note'] = "1";
		$this->arr_payment ['no_shipping'] = "1";
		$this->arr_payment ['address_override'] = "0"; //without this setting, paypal account country must be equal to billing address country
		

		$this->arr_payment ['first_name'] = $this->arr_billing ['firstname'];
		$this->arr_payment ['last_name'] = $this->arr_billing ['lastname'];
		$this->arr_payment ['address1'] = $this->arr_billing ['address1'];
		$this->arr_payment ['address2'] = $this->arr_billing ['address2'];
		$this->arr_payment ['city'] = $this->arr_billing ['city'];
		$this->arr_payment ['state'] = $this->arr_billing ['state'];
		$this->arr_payment ['zip'] = $this->arr_billing ['zip'];
		$this->arr_payment ['country'] = $this->arr_billing ['country'];
		
		//$arr_paypal ['cn'] = "note note note";
		//$arr_paypal['invoice'] = "ss_";
		

		$this->arr_payment ['item_name'] = "Your Purchase (Order reference #" . $this->arr_order ['id'] . ")";
		//$arr_paypal ['item_number'] = $arr_orderids['id'];
		$this->arr_payment ['amount'] = $this->arr_order ['total'];
		$this->arr_payment ['currency_code'] = $this->arr_order ['currency'];
		
		$this->url_integration = $this->url_submit . '?' . http_build_query ( $this->arr_payment );

	}
	
	//https://cms.paypal.com/cms_content/US/en_US/files/developer/IPN_PHP_41.txt
	public function validateIpn($arr_params) {
		try {
			
			parent::validateIpn ();
			
			//create HTTP vars
			$req = 'cmd=_notify-validate';
			foreach ( $arr_params as $key => $value ) {
				$value = urlencode ( stripslashes ( $value ) );
				$req .= "&$key=$value";
			}
			
			//check fields with original orderdata
			if (isset ( $this->arr_order ['total'] )) {
				if (( double ) $arr_params ['mc_gross'] != ( double ) $this->arr_order ['total']) {
					$this->payment_result->error = 004;
					$this->payment_result->confirmed = 0;
				}
			}
			if (isset ( $this->arr_order ['currency'] )) {
				if ($arr_params ['mc_currency'] != $this->arr_order ['currency']) {
					$this->payment_result->error = 005;
					$this->payment_result->confirmed = 0;
				}
			}
			
			if ($this->payment_result->error == "") {
				//validation request to paypal
				$ch = curl_init (); // Starts the curl handler 
				curl_setopt ( $ch, CURLOPT_URL, $this->url_submit ); // Sets the paypal address for curl 
				curl_setopt ( $ch, CURLOPT_FAILONERROR, 1 );
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // Returns result to a variable instead of echoing 
				curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 ); // Sets a time limit for curl in seconds (do not set too low) 
				curl_setopt ( $ch, CURLOPT_POST, 1 ); // Set curl to send data using post 
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $req ); // Add the request parameters to the post 
				$res = curl_exec ( $ch ); // run the curl process (and return the result to $result 
				curl_close ( $ch );
				
				//check response
				$this->payment_result->log .= print_r ( $res, true ) . "\n";
				if (strcmp ( $res, "VERIFIED" ) == 0) {
					$this->payment_result->confirmed = 1;
					$this->payment_result->transaction = $_POST ['txn_id'];
				} else {
					$this->payment_result->confirmed = 0;
					$this->payment_result->error = 001;
					if (strcmp ( $res, "INVALID" ) == 0) {
						// log for manual investigation
						$this->payment_result->error = 002;
					}
					$this->payment_result->confirmed = 0;
				}
			
			}
		
		} catch ( Exception $e ) {
			$this->payment_result->log .= "CATCH" . print_r ( $e, true );
			$this->payment_result->error = 001;
			$this->payment_result->confirmed = 0;
		}
		
		return $this->payment_result;
	}

}

?>
<?php
// account = partner_id
// secret = profile_id
require_once ('Multisafepay/MultiSafepay.combined.php');

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;
class Payment_Integration_Multisafepay extends Payment_Integration implements Payment_IntegrationInterface {
	public $url_submit = "";
	public $shortname = "multisafepay";
	public $obj_msp = null;
	public $msp_payment_product = null;
	
	function __construct(array $arr_options = null) {
		parent::__construct ( $arr_options );
		$this->obj_msp = new MultiSafepay ();
	}
	public function addFieldSetting($key, $value) {
		$key = strtolower ( $key );
		$this->arr_settings [$key] = $value;
		switch ($key) {
			case "account" :
				$this->obj_msp->merchant ['account_id'] = $this->arr_settings ['account'];
				break;
			case "secret" :
				$this->obj_msp->merchant ['site_id'] = $this->arr_settings ['secret'];
				break;
			case "secret2" :
				$this->obj_msp->merchant ['site_code'] = $this->arr_settings ['secret2'];
				break;
			case "payment_product" ://connect or fastcheckout
				$this->msp_payment_product = $this->arr_settings ['payment_product'];
				break;
		}
	}
	public function getPaymentOptions() {
		//var_dump ( $this->obj_msp->getGateways () );
	}
	public function enableTestMode() {
		$this->obj_msp->test = true;
		return parent::enableTestMode ();
	}
	public function preparePayment() {

		

		
		$this->obj_msp->merchant ['notification_url'] = $this->url_callback;
		$this->obj_msp->merchant ['cancel_url'] = $this->url_return_cancel;
		$this->obj_msp->merchant ['redirect_url'] = $this->url_return_success;
		
		/*
		 * Customer Details - supply if available
		 */
		$this->obj_msp->customer ['locale'] = 'nl';
		
		$this->obj_msp->customer ['firstname'] = $this->arr_billing ['firstname'];
		$this->obj_msp->customer ['lastname'] = $this->arr_billing ['lastname'];
		$this->obj_msp->customer ['zipcode'] = $this->arr_billing ['zip'];
		$this->obj_msp->customer ['city'] = $this->arr_billing ['city'];
		$this->obj_msp->customer ['country'] = strtoupper ( $this->arr_billing ['country'] );
		$this->obj_msp->customer ['phone'] = $this->arr_billing ['phone'];
		$this->obj_msp->customer ['email'] = $this->arr_order ['email'];
		$this->obj_msp->parseCustomerAddress ( $this->arr_billing ['address1'] );
		
		$this->obj_msp->delivery ['firstname'] = $this->arr_shipping ['firstname'];
		$this->obj_msp->delivery ['lastname'] = $this->arr_shipping ['lastname'];
		$this->obj_msp->delivery ['zipcode'] = $this->arr_shipping ['zip'];
		$this->obj_msp->delivery ['city'] = $this->arr_shipping ['city'];
		$this->obj_msp->delivery ['country'] = strtoupper ( $this->arr_shipping ['country'] );
		$this->obj_msp->delivery ['phone'] = $this->arr_shipping ['phone'];
		$this->obj_msp->delivery ['email'] = $this->arr_order ['email'];
		
		$this->obj_msp->parseCustomerAddress ( $this->arr_shipping ['address1'] );
		
		/*
		 * Transaction Details
		 */
		$this->obj_msp->transaction ['id'] = $this->arr_order ['id'];// + rand ( 0, 100000 );
		$this->obj_msp->transaction ['currency'] = $this->arr_order ['currency'];
		$this->obj_msp->transaction ['amount'] = ( int ) ($this->arr_order ['total'] * 100);
		$this->obj_msp->transaction ['description'] = 'Order #' . $this->obj_msp->transaction ['id'];
		$this->obj_msp->transaction ['items'] = 'Order: #' + $this->arr_order ['id'];
		
		
		// $msp->transaction ['gateway'] = 'IDEAL';
		if($this->paymentoption <> ""){
			$this->obj_msp->transaction ['gateway'] = $this->paymentoption;
		}
		
		switch($this->msp_payment_product){
			case "connect":
				$url = $this->obj_msp->startTransaction ();
				break;
			case "fastcheckout":
				$url = $this->obj_msp->startCheckout ();
				break;
		}

		
		if ($this->obj_msp->error) {
			//echo "Error " . $this->obj_msp->error_code . ": " . $this->obj_msp->error;
			return false;
		} elseif (! $this->obj_msp->error) {
			$this->url_integration = $url;
			return true;
		}
	}
	public function validateIpn($arr_params) {
		
		try {
				
			parent::validateIpn ();
				
	
			// transaction id (same as the transaction->id given in the transaction request)
			$transactionid = $arr_params['transactionid'];
			
			// (notify.php?type=initial is used as notification_url and should output a link)
			$initial       = ($arr_params['type'] == "initial");
			
	
			
			/*
			 * Transaction Details
			*/
			$this->obj_msp->transaction['id']            = $transactionid;
			
			
			// returns the status
			$status = $this->obj_msp->getStatus();
			
			if ($this->obj_msp->error && !$initial){ // only show error if we dont need to display the link
				echo "Error " . $this->obj_msp->error_code . ": " . $this->obj_msp->error;
				exit();
			}
			
			switch ($status) {
				case "initialized": // waiting
					$this->payment_result->confirmed = 0;
					break;
				case "completed":   // payment complete
					$this->payment_result->confirmed = 1;
					break;
				case "uncleared":   // waiting (credit cards or direct debit)
					$this->payment_result->confirmed = 0;
					break;
				case "void":        // canceled
					$this->payment_result->confirmed = 0;
					break;
				case "declined":    // declined
					$this->payment_result->confirmed = 0;
					break;
				case "refunded":    // refunded
					$this->payment_result->confirmed = 0;
					break;
				case "expired":     // expired
					$this->payment_result->confirmed = 0;
					break;
				default:
			}
			
			$this->ipn_result->transaction = $transactionid;
			$this->payment_result->log .= "Transaction $transactionid recorded with Bank status: $status";;
			
			if (!$initial){
						// link to notify.php for MultiSafepay back-end (for delayed payment notifications)
				// backend expects an "ok" if no error occurred
				echo "ok";
			}
		
		} catch ( Exception $e ) {
			$this->payment_result->log .= "CATCH" . print_r ( $e, true );
			$this->payment_result->error = 001;
			$this->payment_result->confirmed = 0;
		}
		
		return $this->payment_result;
		
		
		
	}
}

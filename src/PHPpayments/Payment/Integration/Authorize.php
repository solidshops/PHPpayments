<?php
//http://www.authorize.net/support/AIM_guide.pdf
//http://www.phpmoot.com/php-authorize-net-payment-gatewa-sim-method/


use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;


class Payment_Integration_Authorize extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "https://secure.authorize.net/gateway/transact.dll";
	public $shortname = "authorize";
	
	public function enableTestMode() {
		parent::enableTestMode ();
		$this->url_submit = "https://test.authorize.net/gateway/transact.dll";
		//$this->url_submit = "https://developer.authorize.net/tools/paramdump/index.php"; //debug
	

	}
	
	public function preparePayment() {
		
		//test
		if ($this->testmode == true) {
			$this->arr_payment ['x_Test_Request'] = 'TRUE';
		}
		
		//system
		$this->arr_payment ['x_Version'] = '3.0';
		$this->arr_payment ['x_Show_Form'] = 'PAYMENT_FORM';
		$this->arr_payment ['x_Login'] = $this->arr_settings ['account'];
		$this->arr_payment ['x_fp_sequence'] = $this->arr_order ['id']; //invoice nr
		$this->arr_payment ['x_fp_timestamp'] = time ();
		
		$this->arr_payment ['x_receipt_link_method'] = "POST";
		$this->arr_payment ['x_receipt_link_text'] = "Return to store";
		$this->arr_payment ['x_Receipt_Link_URL'] = $this->url_return_success; // Specify the url where authorize.net will send the user on success/failure
		

		$this->arr_payment ['orderguid'] = $this->arr_order ['guid'];
		
		if(isset($this->arr_settings ['shopguid'])){
			$this->arr_payment ['shopguid'] = $this->arr_settings ['shopguid'];
		}
		 
		
		$this->arr_payment ['x_first_name'] = $this->arr_billing ['firstname'];
		$this->arr_payment ['x_last_name'] = $this->arr_billing ['lastname'];
		$this->arr_payment ['x_company'] = $this->arr_billing ['companyname'];
		$this->arr_payment ['x_address'] = $this->arr_billing ['address1'];
		$this->arr_payment ['x_city'] = $this->arr_billing ['city'];
		$this->arr_payment ['x_state'] = $this->arr_billing ['state'];
		$this->arr_payment ['x_zip'] = $this->arr_billing ['zip'];
		$this->arr_payment ['x_country'] = $this->arr_billing ['country'];
		$this->arr_payment ['x_phone'] = $this->arr_billing ['phone'];
		$this->arr_payment ['x_fax'] = "";
		$this->arr_payment ['x_email'] = $this->arr_order ['email'];
		$this->arr_payment ['x_cust_id'] = "";
		$this->arr_payment ['x_customer_ip'] = $this->arr_settings ['ip'];
		
		//shipping
		$this->arr_payment ['x_ship_to_first_name'] = $this->arr_shipping ['firstname'];
		$this->arr_payment ['x_ship_to_last_name'] = $this->arr_shipping ['lastname'];
		$this->arr_payment ['x_ship_to_company'] = $this->arr_shipping ['companyname'];
		$this->arr_payment ['x_ship_to_address'] = $this->arr_shipping ['address1'];
		$this->arr_payment ['x_ship_to_city'] = $this->arr_shipping ['city'];
		$this->arr_payment ['x_ship_to_state'] = $this->arr_shipping ['state'];
		$this->arr_payment ['x_ship_to_zip'] = $this->arr_shipping ['zip'];
		$this->arr_payment ['x_ship_to_country'] = $this->arr_shipping ['country'];
		
		//order
		$this->arr_payment ['x_Description'] = "";
		$this->arr_payment ['x_Amount'] = $this->arr_order ['total'];
		$this->arr_payment ['x_Invoice_num'] = $this->arr_order ['orderID'];
		$this->arr_payment ['x_Cust_ID'] = "";
		
		//hash
		$data = $this->arr_payment ['x_Login'] . '^' . $this->arr_payment ['x_Invoice_num'] . '^' . $this->arr_payment ['x_fp_timestamp'] . '^' . $this->arr_payment ['x_Amount'] . '^';
		
		$this->arr_payment ['x_fp_hash'] = $this->hmac ( $this->arr_settings ['secret'], $data );
		
		$this->url_integration = $this->url_submit . '?' . http_build_query ( $this->arr_payment );
	
	}
	
	/**
	 * RFC 2104 HMAC implementation for php.
	 *
	 * @author Lance Rushing
	 * @param string key
	 * @param string date
	 * @return string encoded hash
	 */
	private function hmac($key, $data) {
		$b = 64; // byte length for md5
		

		if (strlen ( $key ) > $b) {
			$key = pack ( "H*", md5 ( $key ) );
		}
		
		$key = str_pad ( $key, $b, chr ( 0x00 ) );
		$ipad = str_pad ( '', $b, chr ( 0x36 ) );
		$opad = str_pad ( '', $b, chr ( 0x5c ) );
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		
		return md5 ( $k_opad . pack ( "H*", md5 ( $k_ipad . $data ) ) );
	}
	
	public function validateIpn($arr_params) {
		try {
			
			parent::validateIpn ();
			
			$md5source = $this->arr_settings ['secret'] . $this->arr_settings ['account'] . $arr_params ['x_trans_id'] . $arr_params ['x_amount'];
			$md5 = md5 ( $md5source );
			
			$this->ipn_result->transaction = $arr_params ['x_trans_id'];
			
			if ($arr_params ['x_response_code'] == '1') {
				//
				if (strtoupper ( $md5 ) != $arr_params ['x_MD5_Hash']) {
					$this->ipn_result->confirmed = 1;
				} else {
					$this->ipn_result->confirmed = 0;
				}
			} else {
				$this->ipn_result->confirmed = 0;
			}
		
		} catch ( Exception $e ) {
			$this->ipn_result->log .= "CATCH" . print_r ( $e, true );
			$this->ipn_result->error = 001;
			$this->ipn_result->confirmed = 0;
		}
		
		return $this->ipn_result;
	}

}

?>

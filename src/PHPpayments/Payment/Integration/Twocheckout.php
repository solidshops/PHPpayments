<?php
//http://www.2checkout.com/documentation/Advanced_User_Guide.pdf
//INS test tool http://developers.2checkout.com/inss
use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;


class Payment_Integration_Twocheckout extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "https://www.2checkout.com/checkout/purchase";
	public $shortname ="twocheckout";
	
	public function enableTestMode() {
		parent::enableTestMode ();
	
	}
	
	public function preparePayment() {
		
		//IF ENABLE TESTMODE
		if($this->testmode == true)
		{
			$this->arr_payment ['demo'] = 'Y';
		}
		
		$this->arr_payment ['fixed'] = 'Y';
		$this->arr_payment ['skip_landing'] = 1;
		
		//FILL FIELDS
		$this->arr_payment ['sid'] = $this->arr_settings ['account'];
		$this->arr_payment ['cart_order_id'] = $this->arr_order ['id'];
		$this->arr_payment ['total'] = $this->arr_order ['total'];
		$this->arr_payment ['return_url'] = $this->url_return_success;
		$this->arr_payment ['x_receipt_link_url'] =  $this->url_return_success;
		$this->arr_payment ['currency'] = $this->arr_order ['currency'];
		
		//CUSTOM PARAMETER FOR ORDER ID, THIS GOES TO THE INS AS FIELD NAME 'vendor_order_id', MAX 50 CHARACTERS
		$this->arr_payment ['merchant_order_id'] = $this->arr_order ['guid'];
		
		//BILLING INFORMATION
		$this->arr_payment['first_name'] = $this->arr_billing['firstname'];
		$this->arr_payment['last_name'] = $this->arr_billing['lastname'];
		$this->arr_payment['street_address'] = $this->arr_billing['address1'];
		$this->arr_payment['street_address2'] = $this->arr_billing['address2'];
		$this->arr_payment['city'] = $this->arr_billing['city'];
		
		if($this->arr_billing['state'] == "")
			$this->arr_payment['state'] = "XX"; //FALL BACK TO DEFAULT "OUTSIDE US" OPTION ON 2CHECKOUT
		else
			$this->arr_payment['state'] = $this->arr_billing['state'];
		
		$this->arr_payment['zip'] = $this->arr_billing['zip'];
		$this->arr_payment['country'] = $this->arr_billing['country'];
		$this->arr_payment['phone'] = $this->arr_billing['phone'];
		
		$this->arr_payment['email'] = $this->arr_order['email'];
		
		
		
		//BUILD URL TO PROCESS PAYMENT
		$this->url_integration = $this->url_submit . '?' . http_build_query ( $this->arr_payment );

	
	}
	
	
	public function validateIpn($arr_params) {
		try {
			
			parent::validateIpn ();
			
			$hashbase = $arr_params['sale_id'].$arr_params['vendor_id'].$arr_params['invoice_id']."tango";
			$rehash = strtoupper(md5($hashbase));
			if($rehash == $arr_params['md5_hash'])
			{
				$this->ipn_result->confirmed = 1;//SET TO PAID
				$this->ipn_result->transaction = $arr_params['sale_id'];//TRANSACTION ID FROM PROVIDER
			}
			else
			{
				//HASH WAS NOT THE SAME, ORDER CHANGED / HACK ATTEMPT
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
<?php
//account = partner_id
//secret = profile_id
require_once ( 'Mollie/iDEAL/Payment.php');

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;

class Payment_Integration_Mollieideal extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "";
	public $shortname = "mollieideal";
	

	public function getPaymentOptions() 
	{
		$obj_ideal = new Mollie_iDEAL_Payment ($this->arr_settings['account']);
		if ($this->testmode == true) {
			$obj_ideal->setTestmode(true);
		}
		$arr_banks = $obj_ideal->getBanks();
		if(!is_array($arr_banks)) // If no banks were available, return an empty array. Not sure if we can add a custom error or return false in this case
			$arr_banks = array();

		return $arr_banks;
	}
	
	public function enableTestMode() 
	{
		return parent::enableTestMode();
	}
	
	public function preparePayment() 
	{
		$obj_ideal = new Mollie_iDEAL_Payment ($this->arr_settings['account']);

		if(isset($this->arr_settings['secret']) && !empty($this->arr_settings['secret']))
			$obj_ideal->setProfileKey($this->arr_settings['secret']);

		
		//test
		if ($this->testmode == true) {
			$obj_ideal->setTestmode(true);
		}
		
		
		$bank = $this->paymentoption; // The chosen bank id
		$amount = $this->arr_order['total']*100;
		$description = "order: " . $this->arr_order ['id'];
		$return_url = $this->url_return_success; // Solidshops got $this->url_return_cancel as well, but Mollie doesn't support it
		$report_url = $this->url_callback;
		if($obj_ideal->createPayment($bank, $amount, $description, $return_url, $report_url))
		{
			$this->url_integration = $obj_ideal->getBankURL();
			return true;
		}
		
		return false;
	}
	
	public function validateIpn($arr_params)
	{
		try {
			$obj_ideal = new Mollie_iDEAL_Payment ($this->arr_settings['account']);
			
			
			parent::validateIpn();
			
			

			$obj_ideal->checkPayment($arr_params['transaction_id']);
			
			if($obj_ideal->getBankStatus() == 'Success')
			{
				$this->payment_result->confirmed = 1;
			}
			elseif($this->obj_ideal->getBankStatus() != 'CheckedBefore')
			{
				$this->payment_result->confirmed = 0;
			}
			$this->payment_result->log .= "Transaction ".$arr_params['transaction_id']." recorded with Bank status: " . $obj_ideal->getBankStatus();
		
		} catch ( Exception $e ) {
			$this->payment_result->log .= "CATCH" . print_r ( $e, true );
			$this->payment_result->error = 001;
			$this->payment_result->confirmed = 0;
		}
		
		return $this->payment_result;
	}
}
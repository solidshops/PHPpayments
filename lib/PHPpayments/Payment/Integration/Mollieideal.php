<?php

require_once ( 'Mollie/iDEAL/Payment.php');

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;

class Payment_Integration_Mollieideal extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "";
	public $shortname = "mollieideal";
	
	public function getPaymentOptions() 
	{
		$arr_banks = $iDEAL->getBanks();
		if(!is_array($arr_banks)) // If no banks were available, return an empty array. Not sure if we can add a custom error or return false in this case
			$arr_banks = array();

		return $arr_banks;
	}
	
	public function enableTestMode() 
	{
		$iDEAL->setTestMode();

		return parent::enableTestMode();
	}
	
	public function preparePayment() 
	{
		$mideal = new Mollie_iDEAL_Payment ($this->arr_settings['partner_id']);

		if(isset($this->arr_settings['profile_key']) && !empty($this->arr_settings['profile_key']))
			$mideal->setProfileKey($this->arr_settings['profile_key']);

		$bank = $this->paymentoption; // The chosen bank id
		$amount = $this->arr_order['total'];
		$description = "order: " . $this->arr_order ['id'];
		$return_url = $this->url_return_success; // Solidshops got $this->url_return_cancel as well, but Mollie doesn't support it
		$report_url = $this->url_callback;
		if($mideal->createPayment($bank, $amount, $description, $return_url, $report_url))
		{
			$this->url_integration = $mideal->getBankURL();
			return true;
		}
		
		return false;
	}
	
	public function validateIpn($arr_params)
	{
		try {
			parent::validateIpn();
			
			$iDEAL = new Mollie_iDEAL_Payment ($this->arr_settings['partner_id']);

			$iDEAL->checkPayment($arr_params['transaction_id']);
			
			if($iDEAL->getBankStatus() == 'Success')
			{
				$this->payment_result->confirmed = 1;
			}
			elseif($iDEAL->getBankStatus() != 'CheckedBefore')
			{
				$this->payment_result->confirmed = 0;
			}
			$this->payment_result->log .= "Transaction ".$arr_params['transaction_id']." recorded with Bank status: " . $iDEAL->getBankStatus();
		
		} catch ( Exception $e ) {
			$this->payment_result->log .= "CATCH" . print_r ( $e, true );
			$this->payment_result->error = 001;
			$this->payment_result->confirmed = 0;
		}
		
		return $this->payment_result;
	}
}
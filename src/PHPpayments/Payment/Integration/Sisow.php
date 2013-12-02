<?php

require_once ( 'Sisow/sisow.cls5.php');

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;

class Payment_Integration_Sisow extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "";
	public $shortname = "sisow";
	
	public function getPaymentOptions() {
		$arr_banks = array ();
		if ($this->testmode == true) {
			$arr_banks ['99'] = "sisow test bank";
		} else {
			$arr_banks ['01'] = "ABN Amro Bank";
			$arr_banks ['02'] = "ASN Bank";
			$arr_banks ['04'] = "Friesland Bank";
			$arr_banks ['05'] = "ING Bank";
			$arr_banks ['06'] = "Rabobank";
			$arr_banks ['07'] = "SNS Bank";
			$arr_banks ['08'] = "RegioBank";
			$arr_banks ['09'] = "Triodos Bank";
			$arr_banks ['10'] = "Van Lanschot Bankiers";
			//$arr_banks ['de'] = "DIRECTebanking";
			$arr_banks ['mc'] = "MisterCash";
			//$arr_banks ['wg'] = "WebShop GiftCard";
		}
		return $arr_banks;
	}
	
	public function enableTestMode() {
		parent::enableTestMode ();
	
	}
	
	public function preparePayment() {
		$sisow = new Sisow ( $this->arr_settings ['account'], $this->arr_settings ['secret'] );
		
		$sisow->purchaseId = $this->arr_order ['id'];
		$sisow->description = "order: " . $this->arr_order ['id'];
		$sisow->amount = $this->arr_order ['total'];
		//$sisow->payment = "mistercash";
		$bank = $this->paymentoption;
		if ($bank == "mc") {
			$sisow->payment = "mistercash";
		} else {
			$sisow->payment = "";
			$sisow->issuerId = $bank;
		
		}
		
		$sisow->returnUrl = $this->url_return_success;
		$sisow->cancelUrl = $this->url_return_cancel;
		$sisow->notifyUrl = $this->url_callback;
		$sisow->callbackUrl = $this->url_callback;
		if (($ex = $sisow->TransactionRequest ()) < 0) {
		
		} else {
			$this->url_integration = $sisow->issuerUrl;
		}
	}
	
public function validateIpn($arr_params) {
		try {
			parent::validateIpn ();
			
			$sHash = sha1 ( $arr_params ['trxid'] . $arr_params ['ec'] . $arr_params ['status'] . $this->arr_settings ['account'] . $this->arr_settings ['secret'] );
			$this->payment_result->log .= "hashcalc:" . $sHash;
			$this->payment_result->log .= "hashget:" . $arr_params ['sha1'];
			
			if ($sHash == $arr_params ['sha1']) {
				switch ($arr_params ['status']) {
					case "Success" :
						$this->payment_result->confirmed = 1;
						break;
					case "Expired" :
						$this->payment_result->confirmed = 0;
						break;
					case "Cancelled" :
						$this->payment_result->confirmed = 0;
						break;
					case "Failure" :
						$this->payment_result->confirmed = 0;
						break;
				}
			} else {
				$this->payment_result->confirmed = 0;
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
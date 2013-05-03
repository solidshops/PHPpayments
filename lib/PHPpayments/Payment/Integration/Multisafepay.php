<?php
//account = partner_id
//secret = profile_id
require_once ( 'Multisafepay/MultiSafepay.combined.php');

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;

class Payment_Integration_Multisafepay extends  Payment_Integration implements  Payment_IntegrationInterface {
	
	public $url_submit = "";
	public $shortname = "multisafepay";
	

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
	$msp = new MultiSafepay();

/* 
 * Merchant Settings
 */
$msp->test                         = true;
$msp->merchant['account_id']       = '90057058';
$msp->merchant['site_id']          = '3844';
$msp->merchant['site_code']        = '707359';


$msp->merchant['notification_url'] = BASE_URL . 'notify.php?type=initial';
$msp->merchant['cancel_url']       = BASE_URL . 'index.php';
// optional automatic redirect back to the shop:
// $msp->merchant['redirect_url']     = BASE_URL . 'return.php'; 

/* 
 * Customer Details - supply if available
 */
$msp->customer['locale']           = 'nl';
$msp->customer['firstname']        = 'Jan';
$msp->customer['lastname']         = 'Modaal';
$msp->customer['zipcode']          = '1234AB';
$msp->customer['city']             = 'Amsterdam';
$msp->customer['country']          = 'NL';
$msp->customer['phone']            = '012-3456789';
$msp->customer['email']            = 'test@example.com';

$msp->parseCustomerAddress('Teststraat 21');
// or 
// $msp->customer['address1']         = 'Teststraat';
// $msp->customer['housenumber']      = '21';


 /* 
  * Delivery address - supply if available
  */
  
/*
$msp->delivery['firstname']        = 'Piet';
$msp->delivery['lastname']         = 'Modaal';
$msp->delivery['zipcode']          = '1234AB';
$msp->delivery['city']             = 'Amsterdam';
$msp->delivery['country']          = 'NL';
$msp->delivery['phone']            = '012-3456789';
$msp->delivery['email']            = 'test@example.com';

$msp->parseDeliveryAddress('Teststraat 22a');
*/

/* 
 * Transaction Details
 */
$msp->transaction['id']            = rand(100000000,999999999); // generally the shop's order ID is used here
$msp->transaction['currency']      = 'EUR';
$msp->transaction['amount']        = '56000'; // cents
$msp->transaction['description']   = 'Order #' . $msp->transaction['id'];
$msp->transaction['items']         = '<br/><ul><li>1 x Item1</li><li>2 x Item2</li></ul>';


/* 
 * Shopping cart
 */
$c_item = new MspItem(
   'Test product',
   'Dit is een test product',
    3,
    '12.00',
    'KG',
    '1'
);
$c_item->SetMerchantItemId('SH123TEST');
$msp->cart->AddItem($c_item);






// returns a payment url

$url = $msp->startCheckout();

//echo $msp->request_xml;

if ($msp->error){
  echo "Error " . $msp->error_code . ": " . $msp->error;
} elseif (!$msp->error){
  header("Location: ".$url);
}
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
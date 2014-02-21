<?php
namespace PHPpayments\Common;
abstract class Payment {
	public $paymentmethod = "";
	public $paymentoption = "";
	public $testmode = false;
	public $type = ""; // Offline/Integration/Gateway
	

	public $url_return_success = "";
	public $url_return_cancel = "";
	public $url_site = "";
	
	public $arr_allowCurrencyCode = array ();
	
	public $arr_settings = array ();
	public $arr_order = array ();
	public $arr_orderitems = array ();
	public $arr_billing = array ();
	public $arr_shipping = array ();
	public $arr_custom = array ();
	
	public $reference_order_id = "";
	public $reference_payment_id = "";
	
	function __construct(array $arr_options = null) {
		$this->paymentmethod = strtolower ( $arr_options ['paymentmethod'] );
		
		if(isset($_SERVER ['REMOTE_ADDR'])){
			$remote_addr = $_SERVER ['REMOTE_ADDR'];
			if($remote_addr <> ""){
				$this->arr_settings ['ip'] = $remote_addr;	
			}
		}else{
			$this->arr_settings ['ip'] = "127.0.0.1"; 
		}
		
		if(isset($_SERVER ['HTTP_ACCEPT_LANGUAGE'])){
			$http_accept_language = $_SERVER ['HTTP_ACCEPT_LANGUAGE'];
			if($http_accept_language <> ""){
				$this->arr_settings ['language'] = substr ( strtolower ( $_SERVER ['HTTP_ACCEPT_LANGUAGE'] ), 0, 2 );	
			}
		}else{
			$this->arr_settings ['language'] = "";
		}
		
		
	}
	public function enableTestMode() {
		$this->testmode = true;
	}
	
	public function getPaymentOptions() {
		return ;
	}
	public function setPaymentOption($name) {
		$this->paymentoption = $name;
	}
	public function getPaymentMethod() {
		return $this->paymentmethod;
	}
	
	public function addFieldOrder($key, $value) {
		/* guid
		 * id
		 * currency
		 * total
		 * +email
		 * +items_quantity
		 * +items_subtotal
		 * +items_tax
		 * +items_total
		 * +shipping_cost
		 * +shipping_tax
		 * +shipping_total
		 */
		$key = strtolower ( $key );
		if ($key == "orderid" && $this->arr_order ['guid'] == "") {
			$this->arr_order ['guid'] = $value;
		}
		$this->arr_order [$key] = $value;
	}
	
	public function addArrayOrderItem($key, $arr_orderitem) {
		/* reference
		 * quantity
		 * price
		 * subtotal
		 * tax
		 * total
		 */
		$this->arr_orderitems [$key] = $arr_orderitem;
	}
	
	
	public function addFieldBilling($key, $value) {
		/*firstname
		lastname
		companyname
		address1
		address2
		city
		state
		zip
		country
		phone
		*/
		$this->arr_billing [strtolower ( $key )] = $value;
	}
	
	public function addFieldShipping($key, $value) {
		/*firstname
		lastname
		companyname
		address1
		address2
		city
		state
		zip
		country
		phone*/
		$this->arr_shipping [strtolower ( $key )] = $value;
	}
	public function addFieldSetting($key, $value) {
		/*account
		secret
		secret2
		shopguid -> for callback in multi-tenant scenario*/
		$this->arr_settings [strtolower ( $key )] = $value;
	}
	
	public function addFieldCustom($key, $value) {
		$this->arr_custom [strtolower ( $key )] = $value;
	}
	
	public function isCurrencyValid($currencyCode) {
		$this->arr_allowCurrencyCode = array_map("strtoupper",$this->arr_allowCurrencyCode);
		if (! in_array ( strtoupper($currencyCode), $this->arr_allowCurrencyCode )) {
			return false;
		}
		return true;
	}
	
	public function checkFields() {
	
	}
	

	public function setUrlSuccess($url) {
		$this->url_return_success = $url;
	}
	public function setUrlCancel($url) {
		$this->url_return_cancel = $url;
	}
	public function setUrlSite($url) {
		$this->url_site = $url;
	}
	
	public function preparePayment(){
		
	}
	public function processPayment() {
	
	}

}

?>

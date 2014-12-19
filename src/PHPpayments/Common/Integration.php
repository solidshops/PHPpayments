<?php
namespace PHPpayments\Common;
abstract class Payment_Integration extends Payment implements Payment_IntegrationInterface  {
	
	public $url_submit = "";
	public $url_callback = "";
	public $url_integration = "";
	
	public $arr_payment = "";
	public $hash="";
	
	public $payment_result = null;
	
	function __construct(array $arr_options = null) {
		parent::__construct ( $arr_options );
		$this->type = "integration";
	}
	

	public function setUrlCallback($url) {
		$this->url_callback = $url;
	}
	
	public  function enableTestMode() {
		parent::enableTestMode ();
	}
	
	
	 public  function validateIpn($arr_params) {
		$this->payment_result = new PaymentResult ();
		
		$log = print_r ( $_SERVER, true ) . "\n";
		$log .= print_r ( $arr_params, true . "\n" );
		$log .= http_build_query($_POST). "\n"; 
		$this->payment_result->log = $log;
	
	}
	
	 public  function processPayment($arr_options = array()) {
		if($this->url_integration == ""){
			//echo "Error redirecting: no integration url provided";
			return false;
		}
		
		header ( "Location: " . $this->url_integration );
		
		if(isset($arr_options['exit']) && $arr_options['exit']){
		    exit ();
		}
	}

}

?>

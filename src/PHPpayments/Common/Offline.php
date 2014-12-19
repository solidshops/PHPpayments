<?php
namespace PHPpayments\Common;
abstract class Payment_Offline extends Payment implements Payment_OfflineInterface {
	function __construct(array $arr_options = null) {
		parent::__construct ( $arr_options );
		$this->type = "offline";
	}
	
public function processPayment($arr_options = array()) {

		$url = $this->url_return_success;
		
		header ( "Location: " . $url );
		
		if(isset($arr_options['exit']) && $arr_options['exit']){
		    exit ();
		}
	}
}


?>

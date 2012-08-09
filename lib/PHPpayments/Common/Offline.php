<?php
namespace lib\PHPpayments\Common;
abstract class Payment_Offline extends Payment implements OfflineInterface {
	function __construct(array $arr_options = null) {
		parent::__construct ( $arr_options );
		$this->type = "offline";
	}
	
public function processPayment() {

		$url = $this->url_return_success;
		
		header ( "Location: " . $url );
		exit ();
	}
}


?>
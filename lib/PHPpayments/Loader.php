<?php
/*
 * set_include_path ( get_include_path () . PATH_SEPARATOR .
 * dirname(__FILE__).'/Payment'); echo get_include_path();
 */
// include_once dirname(__FILE__).'/Payment';
class PHPpayments_Loader {
	static function load($paymentmethod) {
		$paymentmethod = strtolower ( $paymentmethod );

		$arr_folders = explode ( "_", $paymentmethod );
		
		require_once dirname ( __FILE__ ) . "/common/Payment.php";
		require_once dirname ( __FILE__ ) . "/common/PaymentResult.php";
		
		switch (trim ( strtolower ( ($arr_folders [1]) ) )) {
			case "gateway" :
				require_once dirname ( __FILE__ ) . "/common/GatewayInterface.php";
				require_once dirname ( __FILE__ ) . "/common/Gateway.php";
				require_once dirname ( __FILE__ ) . "/Payment/Gateway/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
			case "integration" :
				require_once dirname ( __FILE__ ) . "/common/IntegrationInterface.php";
				require_once dirname ( __FILE__ ) . "/common/Integration.php";
				require_once dirname ( __FILE__ ) . "/Payment/Integration/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
			case "Offline" :
				require_once dirname ( __FILE__ ) . "/common/OfflineInterface.php";
				require_once dirname ( __FILE__ ) . "/common/Offline.php";
				require_once dirname ( __FILE__ ) . "/Payment/Offline/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
		}
	
		return new $paymentmethod ( array (
				"paymentmethod" => $paymentmethod 
		) );
	}
}

?>
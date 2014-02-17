<?php
/*
 * set_include_path ( get_include_path () . PATH_SEPARATOR .
 * dirname(__FILE__).'/Payment'); echo get_include_path();
 */
// include_once dirname(__FILE__).'/Payment';
namespace PHPpayments;
class Loader {
	static function load($paymentmethod) {
		$paymentmethod = strtolower ( $paymentmethod );

		$arr_folders = explode ( "_", $paymentmethod );
		
		require_once dirname ( __FILE__ ) . "/Common/Payment.php";
		require_once dirname ( __FILE__ ) . "/Common/PaymentResult.php";
		
		switch (trim ( strtolower ( ($arr_folders [1]) ) )) {
			case "gateway" :
				require_once dirname ( __FILE__ ) . "/Common/GatewayInterface.php";
				require_once dirname ( __FILE__ ) . "/Common/Gateway.php";
				$file =   dirname ( __FILE__ ) . "/Payment/Gateway/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
			case "integration" :
				require_once dirname ( __FILE__ ) . "/Common/IntegrationInterface.php";
				require_once dirname ( __FILE__ ) . "/Common/Integration.php";
				$file =  dirname ( __FILE__ ) . "/Payment/Integration/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
			case "offline" :
				require_once dirname ( __FILE__ ) . "/Common/OfflineInterface.php";
				require_once dirname ( __FILE__ ) . "/Common/Offline.php";
				$file =  dirname ( __FILE__ ) . "/Payment/Offline/" . ucfirst ( $arr_folders [2] ) . ".php";
				break;
		}
		
		//load class on defaul location or fallback library
		$return = null;
		if (file_exists($file)) {
			require_once $file;
			$return =  new $paymentmethod ( array (
					"paymentmethod" => $paymentmethod
			) );
		} elseif(class_exists('\PHPpayments\FallbackLoader')) {
			//a custom PSR-0 fallback loader can be defined in your project
			$return =  \PHPpayments\FallbackLoader::load($paymentmethod);
		}
		
		return  $return ;

	}
}

?>

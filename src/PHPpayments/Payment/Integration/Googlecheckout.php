<?php
//http://www.google.de/support/forum/p/checkout-merchants/thread?tid=1f0ec0feddc5ad2f&hl=en
//http://code.google.com/intl/nl-NL/apis/checkout/samplecode.html
//http://www.google.com/support/forum/p/checkout-merchants/thread?tid=2f82e02c67451ba4&hl=en

/* TEST ACCOUNT: solidshops@gmail.com
 * Google Merchant ID: 761713994523855
 * Google Merchant Key: G3ksVuA4O96YroJ0c9bWXg
 * 
 * https://checkout.google.com/sell/settings?section=Integration
 * Notification as HTML (name/value pairs)
 * API version 2.0
 * 
 */

require_once ("Googlecheckout/googlecart.php");
require_once ("Googlecheckout/googleitem.php");
require_once ("Googlecheckout/googlerequest.php");
require_once ("Googlecheckout/googleresponse.php");
require_once ("Googlecheckout/googleresult.php");
require_once ("Googlecheckout/googlenotification.php");

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;


class Payment_Integration_Googlecheckout extends  Payment_Integration implements  Payment_IntegrationInterface {

	public $url_submit = "https://checkout.google.com/cws/v2/Merchant/";
	public $shortname = "googlecheckout";
	
	public function enableTestMode() {
		parent::enableTestMode ();
		$this->url_submit = "https://sandbox.google.com/checkout/cws/v2/Merchant/";
	}
	
	public function parseResponseXML($xml_response) {
		$Gresponse = new GoogleResponse ();
		list($root, $gc_data) = $Gresponse->GetParsedXML($xml_response);
		return $gc_data;
	}
	public function getDetail($sn) {
		if ($this->testmode == true) {
			$environment = 'sandbox';
		} else {
			$environment = 'production';
		}
		
		//added a line in the google curl function to disable SSL certificate check.
		$Gnot = new GoogleNotification ( $this->arr_settings ['account'], $this->arr_settings ['secret'], $environment );
		$arr_sn = explode ( "-", $sn );
		$arr_notifications = $Gnot->getNotifications ( '', '', array ($arr_sn [0] ) );
		$arr_notifications = $arr_notifications [0];
		return $arr_notifications;
		
		$arr_return = array ();
		$arr_return ['google-order-number'] = $arr_notifications ['new-order-notification'] ['google-order-number'] ['VALUE'];
		$arr_return ['serial-number'] = $sn;
		
		$arr_lastchange = end ( $arr_notifications ['order-state-change-notification'] );
		$arr_return ['new-financial-order-state'] = $arr_lastchange ['new-financial-order-state'] ['VALUE'];
		$arr_return ['new-fulfillment-order-state'] = $arr_lastchange ['new-fulfillment-order-state'] ['VALUE'];
		
		return $arr_return;
	}
	public function preparePayment() {
		if ($this->testmode == true) {
			$environment = 'sandbox';
		} else {
			$environment = 'production';
		}
		
		$Gcart = new GoogleCart ( $this->arr_settings ['account'], $this->arr_settings ['secret'], $environment, $this->arr_order ['currency'] );
		
		$Gcart->SetContinueShoppingUrl ( $this->url_return_success );
		
		$Gitem = new GoogleItem ( $this->arr_order ['id'], "Your Purchase (Order reference #" . $this->arr_order ['id'] . ")", 1, $this->arr_order ['total'] );
		$Gcart->AddItem ( $Gitem );
		
		$Gcart->SetMerchantPrivateData ( new MerchantPrivateData ( array ('shopguid' => $this->arr_settings ['shopguid'], 'orderguid' => $this->arr_order ['guid'], 'ip-address' => $_SERVER ['REMOTE_ADDR'] ) ) );
		
		$GcartXML = $Gcart->GetXML ();
		
		$Grequest = new GoogleRequest ( $Gcart->merchant_id, $Gcart->merchant_key, $environment, $Gcart->currency );
		list ( $status, $this->url_integration ) = $Grequest->SendServer2ServerCart ( $GcartXML, false );
		if ($this->url_integration == "") {
			echo "An error occurred while sending the basket to google. Check the google checkout 'Integration console'.";
			die ();
		}
	}
	
	
	public function validateIpn($arr_notifications) {
		try {
			parent::validateIpn ( $arr_notifications );
			
			$this->ipn_result->transaction = $arr_notifications ['new-order-notification'] ['google-order-number'] ['VALUE'];
			
			$arr_lastchange = end ( $arr_notifications ['order-state-change-notification'] );
		
			
			switch ($arr_lastchange ['new-financial-order-state'] ['VALUE']) {
				case "CHARGED" :
					$this->ipn_result->confirmed = 1;
					break;
				default :
					$this->ipn_result->confirmed = 0;
					break;
			}
			$Gresponse = new GoogleResponse ( $this->arr_settings ['account'], $this->arr_settings ['secret'] );
			$ack_response = $Gresponse->SendAck ( $arr_notifications ['serial-number'], false );
	
		} catch ( Exception $e ) {
			$this->ipn_result->log .= "CATCH" . print_r ( $e, true );
			$this->ipn_result->error = 001;
			$this->ipn_result->confirmed = 0;
		}
	
		return $this->ipn_result;
	}
	
	/*public function validateIpn($arr_params) {
		try {
			parent::validateIpn ( $arr_params );
			
			$key_transaction  = key($arr_params);
			
			$this->ipn_result->transaction = $arr_params[$key_transaction] ['google-order-number']['VALUE'];
			switch ($arr_params[$key_transaction]['order-summary'] ['financial-order-state']['VALUE']) {
				case "CHARGED" :
					$this->ipn_result->confirmed = 1;
					break;
				default :
					$this->ipn_result->confirmed = 0;
					break;
			}
			
			$sn = $arr_params [$key_transaction] ['serial-number'];
			$Gresponse = new GoogleResponse ( $this->arr_settings ['account'], $this->arr_settings ['secret'] );
			$ack_response = $Gresponse->SendAck ( $sn, false );
		
		} catch ( Exception $e ) {
			$this->ipn_result->log .= "CATCH" . print_r ( $e, true );
			$this->ipn_result->error = 001;
			$this->ipn_result->confirmed = 0;
		}
		
		return $this->ipn_result;
	}*/

}

?>

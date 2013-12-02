<?php
class Sisow
{
	protected static $issuers;
	protected static $lastcheck;

	private $response;

	// Merchant data
	private $merchantId;
	private $merchantKey;

	// Transaction data
	public $payment;	// empty=iDEAL; sofort=DIRECTebanking; mistercash=MisterCash; ...
	public $issuerId;	// mandatory; sisow bank code
	public $purchaseId;	// mandatory; max 16 alphanumeric
	public $entranceCode;	// max 40 strict alphanumeric (letters and numbers only)
	public $description;	// mandatory; max 32 alphanumeric
	public $amount;		// mandatory; min 0.45
	public $notifyUrl;
	public $returnUrl;	// mandatory
	public $cancelUrl;
	public $callbackUrl;

	// Status data
	public $status;
	public $timeStamp;
	public $consumerAccount;
	public $consumerName;
	public $consumerCity;

	// Result/check data
	public $trxId;
	public $issuerUrl;

	// Error data
	public $errorCode;
	public $errorMessage;

	// Status
	const statusSuccess = "Success";
	const statusCancelled = "Cancelled";
	const statusExpired = "Expired";
	const statusFailure = "Failure";
	const statusOpen = "Open";

	public function __construct($merchantid, $merchantkey) {
		$this->merchantId = $merchantid;
		$this->merchantKey = $merchantkey;
	}

	private function error() {
		$this->errorCode = $this->parse("errorcode");
		$this->errorMessage = urldecode($this->parse("errormessage"));
	}

	private function parse($search, $xml = false) {
		if ($xml === false) {
			$xml = $this->response;
		}
		if (($start = strpos($xml, "<" . $search . ">")) === false) {
			return false;
		}
		$start += strlen($search) + 2;
		if (($end = strpos($xml, "</" . $search . ">", $start)) === false) {
			return false;
		}
		return substr($xml, $start, $end - $start);
	}

	private function send($method, array $keyvalue = NULL, $return = 1) {
		$url = "https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/" . $method;
		$options = array(
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => $return,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POSTFIELDS => $keyvalue == NULL ? "" : http_build_query($keyvalue, '', '&'));
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$this->response = curl_exec($ch);
		curl_close($ch); 
		if (!$this->response) {
			return false;
		}
		return true;
	}

	private function getDirectory() {
		$diff = 24 * 60 *60;
		if (self::$lastcheck)
			$diff = time() - self::$lastcheck;
		if ($diff < 24 *60 *60)
			return 0;
		if (!$this->send("DirectoryRequest"))
			return -1;
		$search = $this->parse("directory");
		if (!$search) {
			$this->error();
			return -2;
		}
		self::$issuers = array();
		$iss = explode("<issuer>", str_replace("</issuer>", "", $search));
		foreach ($iss as $k => $v) {
			$issuerid = $this->parse("issuerid", $v);
			$issuername = $this->parse("issuername", $v);
			if ($issuerid && $issuername) {
				self::$issuers[$issuerid] = $issuername;
			}
		}
		self::$lastcheck = time();
		return 0;
	}

	// DirectoryRequest
	public function DirectoryRequest(&$output, $select = false, $test = false) {
		if ($test === true) {
			// kan ook via de gateway aangevraagd worden, maar is altijd hetzelfde
			if ($select === true) {
				$output = "<select id=\"sisowbank\" name=\"issuerid\">";
				$output .= "<option value=\"99\">Sisow Bank (test)</option>";
				$output .= "</select>";
			}
			else {
				$output = array("99" => "Sisow Bank (test)");
			}
			return 0;
		}
		$output = false;
		$ex = $this->getDirectory();
		if ($ex < 0) {
			return $ex;
		}
		if ($select === true) {
			$output = "<select id=\"sisowbank\" name=\"issuerid\">";
		}
		else {
			$output = array();
		}
		foreach (self::$issuers as $k => $v) {
			if ($select === true) {
				$output .= "<option value=\"" . $k . "\">" . $v . "</option>";
			}
			else {
				$output[$k] = $v;
			}
		}
		if ($select === true) {
			$output .= "</select>";
		}
		return 0;
	}

	// TransactionRequest
	public function TransactionRequest($keyvalue = NULL) {
		$this->trxId = $this->issuerUrl = "";
		if (!$this->merchantId)
			return -1;
		if (!$this->merchantKey)
			return -2;
		if (!$this->purchaseId)
			return -3;
		if ($this->amount < 0.45)
			return -4;
		if (!$this->description)
			return -5;
		if (!$this->returnUrl)
			return -6;
		if (!$this->issuerId && !$this->payment)
			return -7;
		if (!$this->entranceCode)
			$this->entranceCode = $this->purchaseId;
		$pars = array();
		$pars["merchantid"] = $this->merchantId;
		$pars["payment"] = $this->payment;
		$pars["issuerid"] = $this->issuerId;
		$pars["purchaseid"] = $this->purchaseId; 
		$pars["amount"] = round($this->amount * 100);
		$pars["description"] = $this->description;
		$pars["entrancecode"] = $this->entranceCode;
		$pars["returnurl"] = $this->returnUrl;
		$pars["cancelurl"] = $this->cancelUrl;
		$pars["callbackurl"] = $this->callbackUrl;
		$pars["notifyurl"] = $this->notifyUrl;
		$pars["sha1"] = sha1($this->purchaseId . $this->entranceCode . round($this->amount * 100) . $this->merchantId . $this->merchantKey);
		if ($keyvalue) {
			foreach ($keyvalue as $k => $v) {
				$pars[$k] = $v;
			}
		}
		if (!$this->send("TransactionRequest", $pars))
			return -8;
		$this->trxId = $this->parse("trxid");
		$this->issuerUrl = urldecode($this->parse("issuerurl"));
		if (!$this->issuerUrl) {
			$this->error();
			return -9;
		}
		return 0;
	}

	// StatusRequest
	public function StatusRequest($trxid = false) {
		if ($trxid === false)
			$trxid = $this->trxId;
		if (!$this->merchantId)
			return -1;
		if (!$this->merchantKey)
			return -2;
		if (!$trxid)
			return -3;
		$this->trxId = $trxid;
		$pars = array();
		$pars["merchantid"] = $this->merchantId;
		$pars["trxid"] = $this->trxId;
		$pars["sha1"] = sha1($this->trxId . $this->merchantId . $this->merchantKey);
		if (!$this->send("StatusRequest", $pars))
			return -4;
		$this->status = $this->parse("status");
		if (!$this->status) {
			$this->error();
			return -5;
		}
		$this->timeStamp = $this->parse("timestamp");
		$this->amount = $this->parse("amount") / 100.0;
		$this->consumerAccount = $this->parse("consumeraccount");
		$this->consumerName = $this->parse("consumername");
		$this->consumerCity = $this->parse("consumercity");
		$this->purchaseId = $this->parse("purchaseid");
		$this->description = $this->parse("description");
		$this->entranceCode = $this->parse("entrancecode");
		return 0;
	}
}

/*$t = new Sisow("2537278813", "f1bcac04ef461e7a84757e6394ba205b1f63936e");
$select = "";
$t->DirectoryRequest($select); // $select wordt gevuld met array("01" => "ABN Amro Bank", "02" => "ASN Bank", ...)
$t->DirectoryRequest($select, true); // $select wordt gevuld met een dropdown "<select ...><option...>...</select>"
$t->issuerId = "10";
$t->purchaseId = "20110329001";
$t->description = "Bestelling 20110329001";
$t->amount = 3.44;
$t->notifyUrl = "http://...";		// Verwerkings URL; niet verplicht, kan ook via returnURL
$t->returnUrl = "http://...";		// Success URL
$t->cancelUrl = "http://...";		// Niet Success URL; niet aanwezig, dan wordt returnURL hiervoor gebruikt
$t->TransactionRequest();
$t->StatusRequest();
$t->StatusRequest("0050000715466154");
if ($t->status == Sisow::statusSuccess)
{
	echo "Gelukt";
}*/

?>

<?php

namespace PHPpayments\Common;

interface Payment_IntegrationInterface {
	 function enableTestMode();
	 function preparePayment();
	 function validateIpn($arr_params);
}

?>
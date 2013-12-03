<?php
namespace PHPpayments\Common;
/*
 * errors
 * 001 -> An error occured
 * 002 -> The ipn result is not valid
 * 003 -> The ipn orderid does not match
 * 004 -> The ipn ordertotal does not match
 * 005 -> The ipn currency does not match
 * 
 */

class PaymentResult {
	public $confirmed = 0;
	public $transaction ="";
	public $log;
	public $error;

}

?>
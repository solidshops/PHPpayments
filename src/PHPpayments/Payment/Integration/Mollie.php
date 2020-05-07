<?php

use \PHPpayments\Common\Payment_Integration;
use \PHPpayments\Common\Payment_IntegrationInterface;

class Payment_Integration_Mollie extends Payment_Integration implements Payment_IntegrationInterface
{

    public $url_submit = "";
    public $shortname = "mollie";

    public function preparePayment()
    {

        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($this->arr_settings['secret']);

        $metadata = new \stdClass();
        $metadata->orderguid = $this->arr_order['guid'];

        try {

            $payment = $mollie->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => sprintf('%0.2f', $this->arr_order['total']),
                ],
                "description" => "order: " . $this->arr_order['id'],
                "redirectUrl" => $this->url_return_success,
                "webhookUrl" => $this->url_callback,
                "metadata" => $metadata,
            ]);

            $this->url_integration = $payment->_links->checkout->href;

            return true;

        } catch (\Exception $e) {
        }

        return false;

    }

    public function validateIpn($arr_params)
    {
        try {

            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey($this->arr_settings['secret']);

            parent::validateIpn();
            
            $payment = $mollie->payments->get($arr_params['id']);

            if ($this->arr_orderIds['guid'] != $payment->metadata->orderguid){
                 $this->payment_result->confirmed = 0;
                 $this->payment_result->log .= "guids do not match";
            } elseif ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
                /*
                 * The payment is paid and isn't refunded or charged back.
                 * At this point you'd probably want to start the process of delivering the product to the customer.
                 */
                $this->payment_result->confirmed = 1;
            } elseif ($payment->isOpen()) {
                /*
                 * The payment is open.
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->isPending()) {
                /*
                 * The payment is pending.
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->isFailed()) {
                /*
                 * The payment has failed.
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->isExpired()) {
                /*
                 * The payment is expired.
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->isCanceled()) {
                /*
                 * The payment has been canceled.
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->hasRefunds()) {
                /*
                 * The payment has been (partially) refunded.
                 * The status of the payment is still "paid"
                 */
                $this->payment_result->confirmed = 0;
            } elseif ($payment->hasChargebacks()) {
                /*
                 * The payment has been (partially) charged back.
                 * The status of the payment is still "paid"
                 */
                $this->payment_result->confirmed = 0;
            }
        } catch (Exception $e) {
            $this->payment_result->log .= "CATCH" . print_r($e, true);
            $this->payment_result->error = 001;
            $this->payment_result->confirmed = 0;
        }
        return $this->payment_result;

    }

}

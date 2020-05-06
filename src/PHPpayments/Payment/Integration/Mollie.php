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
        $mollie->setApiKey($this->arr_settings['account']);


        try {

            $payment = $mollie->payments->create([
                "amount" => [

                    "currency" => "EUR",

                    "value" => sprintf('%0.2f', $this->arr_order['total']),

                ],
                "description" => "order: " . $this->arr_order['id'],
                "redirectUrl" => $this->url_return_success,
                "webhookUrl" => $this->url_callback,
            ]);

            $this->url_integration = $payment->_links->checkout->href;

            return true;

        } catch (\Exception $e) {
        }

        return false;

    }



    public function validateIpn($arr_params)

    {



    }

}



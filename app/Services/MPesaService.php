<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MPesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $passKey;
    protected $accessTokenUrl;
    protected $processRequestUrl;
    protected $stkPushQueryUrl;
    protected $shortcode;
    protected $tillNumber;
    protected $initiatorName;
    protected $initiatorPassword;
    protected $b2cShortcode;
    protected $callbacks;
    protected $timestamp;
    protected $password;

    public function __construct()
    {
        $this->consumerKey          = config('mpesa.mpesa_consumer_key');
        $this->consumerSecret       = config('mpesa.mpesa_consumer_secret');
        $this->passKey              = config('mpesa.passkey');
        $this->shortcode            = config('mpesa.shortcode');
        $this->tillNumber           = config('mpesa.till_number');
        $this->initiatorName        = config('mpesa.initiator_name');
        $this->initiatorPassword    = config('mpesa.initiator_password');
        $this->b2cShortcode         = config('mpesa.b2c_shortcode');
        $this->callbacks            = config('mpesa.callbacks');
        $this->timestamp            = date('YmdHis');
        $this->password             = base64_encode($this->shortcode . $this->passKey . $this->timestamp);
        $this->accessTokenUrl       = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $this->processRequestUrl    = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $this->stkPushQueryUrl      = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
    }

    /**
     * Format phone number to M-Pesa format
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Remove leading 0 if present
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }

        // Ensure it starts with 254
        if (substr($phoneNumber, 0, 3) !== '254') {
            $phoneNumber = '254' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Get M-Pesa access token
     */
    public function getAccessToken()
    {
        $headers = ['Content-Type:application/json; charset=utf8'];

        $curl = curl_init($this->accessTokenUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $this->consumerKey . ':' . $this->consumerSecret);

        $response = curl_exec($curl);
        $result = json_decode($response);
        curl_close($curl);

        return $result->access_token ?? null;
    }

    /**
     * Perform STK Push request
     */
    public function stkPush($phoneNumber, $amount, $accountReference, $transactionDesc)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new \Exception('Failed to get access token');
        }

        // Format phone number (remove leading + and ensure it starts with 254)
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);


        $stkPushHeader = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        // Prepare request data with correct data types (numeric values)
        $curl_post_data = [
            'BusinessShortCode' => (int)$this->shortcode,
            'Timestamp' => $this->timestamp,
            'Password' => $this->password,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => (int)$phoneNumber,
            'PartyB' => (int)$this->shortcode,
            'PhoneNumber' => (int)$phoneNumber,
            'CallBackURL' => $this->callbacks['callback_url'],
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];

        // Log the request for debugging
        Log::info('STK Push Request Data:', [
            'raw' => $curl_post_data
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->processRequestUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkPushHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        // Log the response for debugging
        $responseData = json_decode($response, true);

        Log::info('STK Push Response:', [
            'http_code' => $httpCode,
            'raw' => $responseData,
            'error' => $error
        ]);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        return $responseData;
    }

    public function stkQuery($checkoutRequestID)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new \Exception('Failed to get access token');
        }

        $queryHeader = ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->stkPushQueryUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $queryHeader);

        $curl_post_data = [
            'BusinessShortCode' => (int)$this->shortcode,
            'Password' => $this->password,
            'Timestamp' => $this->timestamp,
            'CheckoutRequestID' => $checkoutRequestID
        ];

        $dataString = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

        $curl_response = curl_exec($curl);
        $data = json_decode($curl_response, true);
        curl_close($curl);

        Log::info('STK Push Query Response:', [
            'raw' => $data
        ]);

        return $data;
    }
}

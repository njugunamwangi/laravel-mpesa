<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MPesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $passKey;
    protected $shortcode;
    protected $tillNumber;
    protected $initiatorName;
    protected $initiatorPassword;
    protected $businessShortcode;
    protected $callbacks;
    protected $urls;
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
        $this->businessShortcode    = config('mpesa.business_shortcode');
        $this->callbacks            = config('mpesa.callbacks');
        $this->urls                 = config('mpesa.urls');
        $this->timestamp            = date('YmdHis');
        $this->password             = base64_encode($this->shortcode . $this->passKey . $this->timestamp);
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

        $curl = curl_init($this->urls['access_token_url']);
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
        curl_setopt($curl, CURLOPT_URL, $this->urls['process_request_url']);
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
        curl_setopt($curl, CURLOPT_URL, $this->urls['stk_push_query_url']);
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

    public function registerC2BUrls()
    {
        $accessToken = $this->getAccessToken();

        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $accessToken];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->urls['register_c2b_urls']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $curl_post_data = [
            'ShortCode' => $this->businessShortcode,
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $this->callbacks['c2b_confirmation_url'],
            'ValidationURL' => $this->callbacks['c2b_validation_url']
        ];

        $dataString = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        Log::info('C2B Register URLs Response:', [
            'raw' => $data
        ]);

        return $data;
    }

    public function c2bValidation()
    {
        header('Content-Type: application/json');

        $response = '{"ResultCode":0,"ResultDesc":"Validation received successfully"}';

        $mPesaResponse = file_get_contents('php://input');

        $logFile = 'c2b_validation.txt';

        $log = fopen($logFile, 'a');

        fwrite($log, $mPesaResponse);

        fclose($log);

        return $response;
    }

    public function c2bConfirmation()
    {
        header('Content-Type: application/json');

        $response = '{"ResultCode":0,"ResultDesc":"Confirmation received successfully"}';

        $mPesaResponse = file_get_contents('php://input');

        $logFile = 'c2b_confirmation.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $mPesaResponse);

        fclose($log);

        return $response;
    }

    public function securityCredential()
    {
        $password = $this->initiatorPassword;

        // Read the certificate file
        $certificatePath = storage_path('app/public/sandboxCertificate.cer');

        if (!file_exists($certificatePath)) {
            throw new \Exception('Certificate file not found: ' . $certificatePath);
        }

        $publicKey = file_get_contents($certificatePath);

        if (!$publicKey) {
            throw new \Exception('Failed to read certificate file');
        }

        // Create a temporary file for the certificate
        $tempCertFile = tempnam(sys_get_temp_dir(), 'mpesa_cert_');
        file_put_contents($tempCertFile, $publicKey);

        // Encrypt the password using the certificate
        $encrypted = '';
        $encryptedSuccess = openssl_public_encrypt($password, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        // Clean up temporary file
        unlink($tempCertFile);

        if (!$encryptedSuccess) {
            throw new \Exception('Failed to encrypt security credential');
        }

        return base64_encode($encrypted);
    }

    public function b2c($phone, $command, $amount, $remarks, $occasion)
    {
        $accessToken = $this->getAccessToken();

        $securityCredential = $this->securityCredential();

        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $accessToken];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->urls['b2c_url']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $curl_post_data = [
            'OriginatorConversationID' => Str::uuid(),
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $securityCredential,
            'CommandID' => $command,
            'Amount' => $amount,
            'PartyA' => $this->businessShortcode,
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->callbacks['b2c_timeout_url'],
            'ResultURL' => $this->callbacks['b2c_result_url'],
            'Occasion' => $occasion
        ];

        $dataString = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        Log::info('B2C Response:', [
            'raw' => $data
        ]);

        return $data;
    }
}

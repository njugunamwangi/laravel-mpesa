<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccessTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $consumer_key = config('mpesa.mpesa_consumer_key');
        $consumer_secret = config('mpesa.mpesa_consumer_secret');

        $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $headers = ['Content-Type:application/json; charset=utf8'];

        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $consumer_key . ':' . $consumer_secret);

        $response = curl_exec($curl);
        $result = json_decode($response);
        curl_close($curl);

        return $result->access_token;
    }
}

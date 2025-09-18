<?php

namespace App\Http\Controllers;

use App\Models\MPesaB2C;
use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class B2CController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function securityCredential()
    {
        $result = $this->mPesaService->securityCredential();

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function b2c()
    {
        $phone = '254708374149';
        $command = 'BusinessPayment';
        $amount = 1;
        $remarks = 'B2C Test';
        $occasion = 'B2C Test';

        $result = $this->mPesaService->b2c($phone, $command, $amount, $remarks, $occasion);

        Log::info('B2C Result:', [
            'raw' => $result
        ]);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    }

    public function result()
    {
        Log::info("B2C Result URL has been hit");

        header('Content-Type: application/json');

        $resultCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($resultCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'b2c_result.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);

        $responseData = json_decode($resultCallBackResponse, true);

        if(isset($responseData['Result']) && $responseData['Result']['ResultType'] == 0 && $responseData['Result']['ResultCode'] == 0) {
            $result = $responseData['Result'];

            MPesaB2C::create([
                'result_type' => $result['ResultType'],
                'result_code' => $result['ResultCode'],
                'result_desc' => $result['ResultDesc'],
                'originator_conversation_id' => $result['OriginatorConversationID'],
                'conversation_id' => $result['ConversationID'],
                'transaction_id' => $result['TransactionID'],
                'transaction_amount' => $result['ResultParameters']['ResultParameter'][0]['Value'],
                'is_registered_customer' => $result['ResultParameters']['ResultParameter'][6]['Value'] == 'Y' ? true : false,
                'receiver_party_public_name' => $result['ResultParameters']['ResultParameter'][2]['Value'],
                'transaction_date_time' => $result['ResultParameters']['ResultParameter'][3]['Value'],
                'b2c_charges_paid_account_available_funds' => $result['ResultParameters']['ResultParameter'][7]['Value'],
                'b2c_utility_account_available_funds' => $result['ResultParameters']['ResultParameter'][4]['Value'],
                'b2c_working_account_available_funds' => $result['ResultParameters']['ResultParameter'][5]['Value'],
            ]);
        }
    }

    public function timeout()
    {
        Log::info("B2C Timeout URL has been hit");

        header('Content-Type: application/json');

        $timeoutCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($timeoutCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'b2c_timeout.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }


}

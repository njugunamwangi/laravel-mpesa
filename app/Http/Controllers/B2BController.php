<?php

namespace App\Http\Controllers;

use App\Models\MPesaB2B;
use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class B2BController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function b2bPayBill()
    {
        $payBill = '600000';
        $amount = 1;
        $command = 'BusinessPayBill';
        $phone = '254708374149';
        $accountReference = '353353';
        $remarks = 'B2B Test';

        $response = $this->mPesaService->b2b($payBill, $amount, $command, $phone, $accountReference, $remarks);

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function b2bBuyGoods()
    {
        $payBill = '600000';
        $amount = 1;
        $command = 'BusinessBuyGoods';
        $phone = '254708374149';
        $accountReference = '353353';
        $remarks = 'B2B Test';

        $response = $this->mPesaService->b2b($payBill, $amount, $command, $phone, $accountReference, $remarks);

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function result()
    {
        Log::info("B2B Result URL has been hit");

        header('Content-Type: application/json');

        $resultCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($resultCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'b2b_result.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);

        // Parse the JSON response
        $responseData = json_decode($resultCallBackResponse, true);

        if(isset($responseData['Result']) && $responseData['Result']['ResultType'] == 0 && $responseData['Result']['ResultCode'] == 0) {
            $result = $responseData['Result'];

            // Helper function to safely get ResultParameter value
            $getResultParameter = function($index, $default = null) use ($result) {
                if (isset($result['ResultParameters']['ResultParameter'][$index]['Value'])) {
                    return $result['ResultParameters']['ResultParameter'][$index]['Value'];
                }
                return $default;
            };

            // Helper function to parse amount string
            $parseAmountString = function($amountString) {
                if (empty($amountString)) {
                    return [
                        'minimum' => 0,
                        'basic' => 0.0
                    ];
                }

                // Extract MinimumAmount and BasicAmount from string like "{Amount={CurrencyCode=KES, MinimumAmount=499800141, BasicAmount=4998001.41}}"
                preg_match('/MinimumAmount=(\d+)/', $amountString, $minMatches);
                preg_match('/BasicAmount=([\d.]+)/', $amountString, $basicMatches);

                return [
                    'minimum' => isset($minMatches[1]) ? (int)$minMatches[1] : 0,
                    'basic' => isset($basicMatches[1]) ? (float)$basicMatches[1] : 0.0
                ];
            };

            // Parse debit account balance
            $debitBalance = $parseAmountString($getResultParameter(6));

            // Parse initiator account balance
            $initiatorBalance = $parseAmountString($getResultParameter(7));

            MPesaB2B::create([
                'result_type' => $result['ResultType'],
                'result_code' => $result['ResultCode'],
                'result_desc' => $result['ResultDesc'],
                'originator_conversation_id' => $result['OriginatorConversationID'],
                'conversation_id' => $result['ConversationID'],
                'transaction_id' => $result['TransactionID'],
                'transaction_amount' => $getResultParameter(5),
                'receiver_party_public_name' => $getResultParameter(1),
                'transaction_date_time' => $getResultParameter(3),
                'debit_account_current_balance_minimum' => $debitBalance['minimum'],
                'debit_account_current_balance_basic' => $debitBalance['basic'],
                'initiator_account_current_balance_minimum' => $initiatorBalance['minimum'],
                'initiator_account_current_balance_basic' => $initiatorBalance['basic'],
            ]);
        }
    }

    public function timeout()
    {
        Log::info("B2B Timeout URL has been hit");

        header('Content-Type: application/json');

        $timeoutCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($timeoutCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'b2b_timeout.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }
}

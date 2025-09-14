<?php

namespace App\Http\Controllers;

use App\Models\MPesaSTK;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class STKPushController extends Controller
{
    protected $mPesaService;

    public $resultCode = 1;

    public $resultDesc = 'An error occurred';

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    /**
     * Handle the incoming request.
     */
    public function index()
    {
        $phoneNumber = '254715789160';
        $amount = 1;
        $accountReference = 'Laravel M-Pesa';
        $transactionDesc = 'STK Push Test';

        try {

            $result = $this->mPesaService->stkPush($phoneNumber, $amount, $accountReference, $transactionDesc);

            // Pretty print the result in logs
            Log::info('STK Push Final Result:', [
                'raw' => $result
            ]);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        }
    }

    public function confirm()
    {
        header('Content-Type: application/json');

        $stkCallbackResponse = file_get_contents('php://input');

        // Decode and pretty print the JSON
        $decodedData = json_decode($stkCallbackResponse, true);

        $resultCode = $decodedData['Body']['stkCallback']['ResultCode'];

        if ($resultCode == 0) {

            MPesaSTK::create([
                'result_desc' => $decodedData['Body']['stkCallback']['ResultDesc'],
                'result_code' => $resultCode,
                'merchant_request_id' => $decodedData['Body']['stkCallback']['MerchantRequestID'],
                'checkout_request_id' => $decodedData['Body']['stkCallback']['CheckoutRequestID'],
                'amount' => $decodedData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'],
                'mpesa_receipt_number' => $decodedData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'],
                'transaction_date' => $decodedData['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'],
                'phonenumber' => $decodedData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'],
            ]);

            $this->resultCode = $resultCode;
            $this->resultDesc = $decodedData['Body']['stkCallback']['ResultDesc'];
        }

        return response()->json([
            'result_code' => $this->resultCode,
            'result_desc' => $this->resultDesc
        ]);
    }

    public function query()
    {
        $checkoutRequestID = 'ws_CO_140920251509578715789160';
        $result = $this->mPesaService->stkQuery($checkoutRequestID);
        Log::info('STK Push Query Result:', [
            'raw' => $result
        ]);

        $resultCode = $result['ResultCode'];
        $message = '';

        if ($resultCode == 0) {
            $message = 'The transaction is successful';
        } elseif ($resultCode == 1) {
            $message = 'The balance is insufficient to complete the transaction';
        } elseif ($resultCode == 1032) {
            $message = 'Transaction cancelled by user';
        } elseif ($resultCode == 1037) {
            $message = 'Transaction timed out';
        } else {
            $message = 'The transaction is not successful';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

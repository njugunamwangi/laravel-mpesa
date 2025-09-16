<?php

namespace App\Http\Controllers;

use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionStatusController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function index()
    {
        $originatorConversationID = 'AG_20190826_0000777ab7d848b9e721';
        $transactionId = 'OEI2AK4Q16';

        $result = $this->mPesaService->transactionStatus($originatorConversationID, $transactionId);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function result()
    {
        Log::info("Transaction Status Result URL has been hit");

        header('Content-Type: application/json');

        $resultCallBackResponse = file_get_contents('php://input');

        $logFile = 'transaction_status_result.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $resultCallBackResponse);

        fclose($log);
    }

    public function timeout()
    {
        Log::info("Transaction Status Timeout URL has been hit");

        header('Content-Type: application/json');

        $timeoutCallBackResponse = file_get_contents('php://input');

        $logFile = 'transaction_status_timeout.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $timeoutCallBackResponse);

        fclose($log);
    }
}

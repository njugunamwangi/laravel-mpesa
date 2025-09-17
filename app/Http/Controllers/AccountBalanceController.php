<?php

namespace App\Http\Controllers;

use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountBalanceController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function index()
    {
        $result = $this->mPesaService->accountBalance();

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    }

    public function result()
    {
        Log::info("Account Balance Result URL has been hit");

        header('Content-Type: application/json');

        $resultCallBackResponse = file_get_contents('php://input');

        // Pretty print the JSON
        $prettyJson = json_encode(json_decode($resultCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'account_balance_result.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }

    public function timeout()
    {
        Log::info("Account Balance Timeout URL has been hit");

        header('Content-Type: application/json');

        $timeoutCallBackResponse = file_get_contents('php://input');

        // Pretty print the JSON
        $prettyJson = json_encode(json_decode($timeoutCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'account_balance_timeout.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }
}

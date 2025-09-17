<?php

namespace App\Http\Controllers;

use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReversalController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function index()
    {
        $transactionId = 'OEI2AK4Q16';
        $amount = 1;
        $remarks = 'Reversal Test';
        $occasion = 'Reversal Test';

        $result = $this->mPesaService->reversal($transactionId, $amount, $remarks, $occasion);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function result()
    {
        Log::info("Reversal Result URL has been hit");

        header('Content-Type: application/json');

        $resultCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($resultCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'reversal_result.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }

    public function timeout()
    {
        Log::info("Reversal Timeout URL has been hit");

        header('Content-Type: application/json');

        $timeoutCallBackResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($timeoutCallBackResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'reversal_timeout.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }

}

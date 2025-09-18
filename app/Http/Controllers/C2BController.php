<?php

namespace App\Http\Controllers;

use App\Models\MPesaC2B;
use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class C2BController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function register()
    {
        $result = $this->mPesaService->registerC2BUrls();

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function index()
    {
        $result = $this->mPesaService->c2b();

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function validation()
    {
        Log::info("C2B Validation URL has been hit");
        
        header('Content-Type: application/json');

        $mPesaResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($mPesaResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'c2b_validation.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }

    public function confirmation()
    {
        Log::info("C2B Confirmation URL has been hit");

        header('Content-Type: application/json');

        $mPesaResponse = file_get_contents('php://input');

        $prettyJson = json_encode(json_decode($mPesaResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $logFile = 'c2b_confirmation.json';

        $log = fopen($logFile, 'a');

        fwrite($log, $prettyJson . "\n");

        fclose($log);
    }
}

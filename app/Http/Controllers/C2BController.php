<?php

namespace App\Http\Controllers;

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

    public function validation()
    {
        $result = $this->mPesaService->c2bValidation();

        Log::info('C2B Validation Result:', [
            'raw' => $result
        ]);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function confirmation()
    {
        $result = $this->mPesaService->c2bConfirmation();

        Log::info('C2B Confirmation Result:', [
            'raw' => $result
        ]);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

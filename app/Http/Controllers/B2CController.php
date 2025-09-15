<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    }

    public function result()
    {
        Log::info("B2C Result URL has been hit");
    }

    public function timeout()
    {
        Log::info("B2C Timeout URL has been hit");
    }


}

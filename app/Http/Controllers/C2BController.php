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

        MPesaC2B::create([
            'Transaction_type' => $result['TransactionType'],
            'mpesa_receipt_number' => $result['TransID'],
            'Transaction_Time' => $result['TransTime'],
            'amount' => $result['TransAmount'],
            'Business_Shortcode' => $result['BusinessShortCode'],
            'Account_Number' => $result['BillRefNumber'],
            'Invoice_no' => $result['InvoiceNumber'],
            'Organization_Account_Balance' => $result['OrgAccountBalance'],
            'ThirdParty_Transaction_ID' => $result['ThirdPartyTransID'],
            'phonenumber' => $result['MSISDN'],
            'FirstName' => $result['FirstName'],
            'MiddleName' => $result['MiddleName'],
            'LastName' => $result['LastName'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

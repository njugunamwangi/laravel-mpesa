<?php

namespace App\Http\Controllers;

use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QRCodeController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    public function index()
    {
        $merchantName = 'Test Merchant';
        $refNo = 'Invoice Test';
        $amount = 1;
        $trxCode = 'PB';
        $cpi = '600986';
        $size = '300';

        $result = $this->mPesaService->qrCode($merchantName, $refNo, $amount, $trxCode, $cpi, $size);

        Log::info('QR Code Request Data:', [
            'raw' => $result
        ]);

        $qrCode = $result['QRCode'];

        if(isset($qrCode)) {
            $image = "data:image/png;base64," . $qrCode;
        } else {
            $image = null;
        }

        return view('qr-code', compact('image'));
    }
}

<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\AccountBalanceController;
use App\Http\Controllers\B2BController;
use App\Http\Controllers\B2CController;
use App\Http\Controllers\C2BController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\ReversalController;
use App\Http\Controllers\STKPushController;
use App\Http\Controllers\TaxRemittanceController;
use App\Http\Controllers\TransactionStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/access-token', AccessTokenController::class);

// STK Push Routes
Route::get('/stk-push', [STKPushController::class, 'index']);
Route::get('/stk-push-query', [STKPushController::class, 'query']);

// C2B Routes
Route::get('/c2b-register', [C2BController::class, 'register']);
Route::get('/c2b', [C2BController::class, 'index']);

// B2C Routes,
Route::get('/b2c', [B2CController::class, 'b2c']);
Route::get('/security-credential', [B2CController::class, 'securityCredential']);

// Transaction Status Routes
Route::get('/transaction-status', [TransactionStatusController::class, 'index']);

// Account Balance Routes
Route::get('/account-balance', [AccountBalanceController::class, 'index']);

// Reversal Routes
Route::get('/reversal', [ReversalController::class, 'index']);

// Tax Remittance Routes
Route::get('/tax-remittance', [TaxRemittanceController::class, 'index']);

// QR Code Routes
Route::get('/qr-code', [QRCodeController::class, 'index']);

// B2B Routes
Route::get('/b2b-pay-bill', [B2BController::class, 'b2bPayBill']);
Route::get('/b2b-buy-goods', [B2BController::class, 'b2bBuyGoods']);

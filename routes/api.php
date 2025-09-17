<?php

use App\Http\Controllers\AccountBalanceController;
use App\Http\Controllers\B2CController;
use App\Http\Controllers\C2BController;
use App\Http\Controllers\ReversalController;
use App\Http\Controllers\STKPushController;
use App\Http\Controllers\TransactionStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// STK
Route::post('/confirm', [STKPushController::class, 'confirm'])->name('mpesa.stk.confirm');

// C2B
Route::post('/c2b/validation', [C2BController::class, 'validation'])->name('c2b.validate');
Route::post('/c2b/confirmation', [C2BController::class, 'confirmation'])->name('c2b.confirm');

// B2C
Route::post('/b2c/result', [B2CController::class, 'result'])->name('b2c.result');
Route::post('/b2c/timeout', [B2CController::class, 'timeout'])->name('b2c.timeout');

// Transaction Status
Route::post('/transaction/result', [TransactionStatusController::class, 'result'])->name('transaction.status.result');
Route::post('/transaction/timeout', [TransactionStatusController::class, 'timeout'])->name('transaction.status.timeout');

// Account Balance
Route::post('/balance/result', [AccountBalanceController::class, 'result'])->name('account.balance.result');
Route::post('/balance/timeout', [AccountBalanceController::class, 'timeout'])->name('account.balance.timeout');

// Reversal
Route::post('/reversal/result', [ReversalController::class, 'result'])->name('reversal.result');
Route::post('/reversal/timeout', [ReversalController::class, 'timeout'])->name('reversal.timeout');

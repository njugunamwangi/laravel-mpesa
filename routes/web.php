<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\AccountBalanceController;
use App\Http\Controllers\B2CController;
use App\Http\Controllers\C2BController;
use App\Http\Controllers\ReversalController;
use App\Http\Controllers\STKPushController;
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

// B2C Routes
Route::get('/b2c', [B2CController::class, 'b2c']);
Route::get('/security-credential', [B2CController::class, 'securityCredential']);

// Transaction Status Routes
Route::get('/transaction-status', [TransactionStatusController::class, 'index']);

// Account Balance Routes
Route::get('/account-balance', [AccountBalanceController::class, 'index']);

// Reversal Routes
Route::get('/reversal', [ReversalController::class, 'index']);

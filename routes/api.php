<?php

use App\Http\Controllers\B2CController;
use App\Http\Controllers\C2BController;
use App\Http\Controllers\STKPushController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// STK
Route::post('/v1/confirm', [STKPushController::class, 'confirm'])->name('mpesa.stk.confirm');

// C2B
Route::post('validation', [C2BController::class, 'validation'])->name('c2b.validate');
Route::post('confirmation', [C2BController::class, 'confirmation'])->name('c2b.confirm');

// B2C
Route::post('/b2c-result', [B2CController::class, 'result'])->name('b2c.result');
Route::post('/b2c-timeout', [B2CController::class, 'timeout'])->name('b2c.timeout');

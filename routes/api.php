<?php

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

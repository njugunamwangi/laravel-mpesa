<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\C2BController;
use App\Http\Controllers\STKPushController;
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

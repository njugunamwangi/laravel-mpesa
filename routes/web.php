<?php

use App\Http\Controllers\AccessTokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/access-token', AccessTokenController::class);

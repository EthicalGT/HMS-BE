<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;
use App\Http\Controllers\VendorAuthController;
use App\Http\Controllers\VendorController;

Route::post('/hawker/register', [HawkerController::class, 'registerHawker']);

Route::post('/hawker/verify', [OTPController::class, 'validateOTP']);

Route::post('/hawker/login', [HawkerController::class, 'loginHawker']);

//vendor's api
Route::post('/vendor/register', [VendorAuthController::class, 'register']);
Route::post('/vendor/verify-otp', [VendorAuthController::class, 'verifyOtp']);
Route::post('/vendor/login', [VendorAuthController::class, 'login']);

//get all vendors
Route::get('/vendors', [VendorController::class, 'index']);



<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;
use App\Http\Controllers\VendorAuthController;
use App\Http\Controllers\VendorController;

Route::post('/register_hawker', [HawkerController::class, 'registerHawker']);

Route::post('/verify_hawker', [OTPController::class, 'validateOTP']);

Route::post('/hawker_login', [HawkerController::class, 'loginHawker']);

//vendor's api
Route::post('/vendor/register', [VendorAuthController::class, 'register']);
Route::post('/vendor/verify-otp', [VendorAuthController::class, 'verifyOtp']);
Route::post('/vendor/login', [VendorAuthController::class, 'login']);

// Get all vendors
Route::get('/vendors', [VendorController::class, 'index']);



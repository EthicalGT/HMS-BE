<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\OTPController;


//hawker's api
Route::post('/hawker/register', [HawkerController::class, 'registerHawker']);
Route::post('/hawker/verify', [OTPController::class, 'validateOTP']);
Route::post('/hawker/login', [HawkerController::class, 'loginHawker']);
Route::get('/current_otp_user', [HawkerController::class, 'getCurrentUser']);

//vendor's api
Route::post('/vendor/register', [VendorController::class, 'register']);
Route::post('/vendor/verify-otp', [VendorController::class, 'verifyOtp']);
Route::post('/vendor/login', [VendorController::class, 'login']);
Route::get('/vendors', [VendorController::class, 'fetchAllVendors']);



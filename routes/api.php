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
Route::get('/current_otp_user', [HawkerController::class, 'getCurrentVerifyingUser']);

//vendor's api
Route::post('/vendor/register', [VendorController::class, 'registerVendor']);
Route::post('/vendor/verify', [VendorController::class, 'validateOTP']);
Route::post('/vendor/login', [VendorController::class, 'loginVendor']);
Route::get('/vendor/current_otp_user', [VendorController::class, 'getCurrentUser']);
Route::get('/vendors', [VendorController::class, 'fetchAllVendors']);



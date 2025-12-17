<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;
use App\Http\Controllers\VendorAuthController;

<<<<<<< HEAD

Route::post('/register_hawker', [HawkerController::class, 'registerHawker']);


Route::post('/vendor/register', [VendorAuthController::class, 'register']);
Route::post('/vendor/verify-otp', [VendorAuthController::class, 'verifyOtp']);
=======
Route::post('/register_hawker', [HawkerController::class, 'registerHawker']);

Route::post('/verify_hawker', [OTPController::class, 'validateOTP']);
>>>>>>> 865a02b (updated the route for otp verification - GT.)

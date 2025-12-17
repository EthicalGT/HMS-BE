<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;
use App\Http\Controllers\VendorAuthController;

Route::post('/register_hawker', [HawkerController::class, 'registerHawker']);

Route::post('/verify_hawker', [OTPController::class, 'validateOTP']);

Route::post('/hawker_login', [HawkerController::class, 'loginHawker']);


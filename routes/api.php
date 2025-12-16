<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HawkerController;

Route::post('/register_hawker', [HawkerController::class, 'registerHawker']);
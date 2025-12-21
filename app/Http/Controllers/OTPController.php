<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Hawkers;

class OTPController extends Controller {
    public function __construct()
    {
        $this->otpModel = new Otp();
        $this->hawkerModel = new Hawkers();
    }
    public function validateOTP(Request $request)
{

    $email = Cache::get('cvuser');

    if (!$email) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Session expired. Please try again later.'
        ], 400);
    }

    $request->validate([
        'otp' => 'required|string',
    ]);

    $phoneno = $this->hawkerModel->retrieveHawkerMobileNo($email);

    if (!$this->otpModel->checkOTP($phoneno, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid OTP or OTP expired.'
        ], 400);
    }

    if (!$this->otpModel->updateOTPStatus($phoneno, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'OTP status updation failed. Please try again.'
        ], 400);
    }

    //Cache::forget('cvuser');

    return response()->json([
        'status' => 'success',
        'message' => 'OTP verified successfully. You can proceed for login.',
        'redirectTo' => '/hawker_dashboard',
    ], 200);
}
}
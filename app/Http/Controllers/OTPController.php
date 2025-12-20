<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OTPController extends Controller {
    public function __construct()
    {
        $this->otpModel = new Otp();
    }
    public function validateOTP(Request $request)
{
    $email = session('cu_email');
    echo "Retrieved session email: " . $email;

    if (!$email) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Session expired. Please try again.'
        ], 400);
    }

    $request->validate([
        'otp' => 'required|string',
    ]);

    if (!$this->otpModel->checkOTP($email, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid OTP or OTP expired.'
        ], 400);
    }

    if (!$this->otpModel->updateOTPStatus($email, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'OTP status updation failed. Please try again.'
        ], 400);
    }

    Session::forget('cu_email');

    return response()->json([
        'status' => 'success',
        'message' => 'OTP verified successfully. You can proceed for login.',
        'redirectTo' => '/hawker_dashboard',
    ], 200);
}
}
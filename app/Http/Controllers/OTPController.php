<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

class OTPController extends Controller {
    public function validateOTP(Request $request)
{
    $email = session('cu_email');

    if (!$email) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Session expired. Please try again.'
        ], 400);
    }

    $request->validate([
        'otp' => 'required|string',
    ]);

    $otpModel = new Otp();

    if (!$otpModel->checkOTP($email, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid OTP or OTP expired.'
        ], 400);
    }

    if (!$otpModel->updateOTPStatus($email, $request->otp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'OTP status updation failed. Please try again.'
        ], 400);
    }

    Session::forget('cu_email');

    return response()->json([
        'status' => 'success',
        'message' => 'OTP verified successfully. You can proceed for login.'
    ], 200);
}
}
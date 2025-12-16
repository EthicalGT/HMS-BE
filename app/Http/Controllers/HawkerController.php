<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Hawkers;
use App\Models\Otp;
use App\Services\EmailService;

class HawkerController extends Controller
{
    public function registerHawker(Request $req)
{
    $email = $req->email;

    if (session()->has('cu_email')) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An OTP has already been sent. Please check your email.',
        ], 400);
    }

    $hawkersModel = new Hawkers();
    $otpModel = new Otp();

    $data = [
        'full_name'         => $req->full_name,
        'phone_number'      => $req->phone_number,
        'email'             => $email,
        'password_hash'     => Hash::make($req->password),
        'aadhaar_number'    => $req->aadhaar_number,
        'address'           => $req->address,
        'city'              => $req->city,
        'state'             => $req->state,
        'pincode'           => $req->pincode,
        'profile_photo_url' => '/static/img/profile_picture/default.png',
    ];

    $result = $hawkersModel->registerHawkers($data);

    if (!$result['success']) {
        return response()->json([
            'status'  => 'failed',
            'message' => $result['error'],
        ], 400);
    }

    $otp = generate_otp(6);

    $otpData = [
        'otp'   => $otp,
        'email' => $email,
    ];

    $result2 = $otpModel->registerOTP($otpData);

    if (!$result2['success']) {
        return response()->json([
            'status'  => 'failed',
            'message' => $result2['error'],
        ], 400);
    }

    $emailSender = new EmailService();
    $subject = "HMS Account Verification Code";
    $body = "
        <h2>HMS Account Verification</h2>
        <p>Hello,</p>
        <p>Use the following One-Time Password (OTP) to verify your account:</p>
        <h1 style='color:#2d89ef;'>{$otp}</h1>
        <p>This OTP is valid for <strong>10 minutes</strong>.</p>
        <p>Do not share this OTP.</p>
        <p>Thank you,<br>HMS Team</p>
    ";

    try {
        $emailSender->send($email, $subject, $body);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Email sending failed: ' . $e->getMessage(),
        ], 500);
    }

    session(['cu_email' => $email]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Registration successful, kindly check your email for OTP verification.',
    ]);
    }
}

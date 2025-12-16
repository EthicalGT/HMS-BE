<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Hawkers;
use App\Models\Otp;
use App\Services\EmailService;

Route::get('/getdata', function () {
    return response()->json(['msg' => 'Hello this is GT.']);
});

Route::post('/register_hawker', function(Request $req) {

    $hawkersModel = new Hawkers(); 
    $otpModel = new Otp();

    $data = [
        'full_name' => $req->full_name,
        'phone_number' => $req->phone_number,
        'email' => $req->email,
        'password_hash' => Hash::make($req->password),
        'aadhaar_number' => $req->aadhaar_number,
        'address' => $req->address,
        'city' => $req->city,
        'state' => $req->state,
        'pincode' => $req->pincode,
        'profile_photo_url' => "/static/img/profile_picture/default.png",
    ];

    $result = $hawkersModel->registerHawkers($data);

    if (!$result['success']) {
        return response()->json([
            'status' => 'failed',
            'message' => $result['error'],
        ], 400);
    }

    $otp = generate_otp(6);
    $otpData = [
        'otp' => $otp,
        'email' => $req->email,
    ];

    $result2 = $otpModel->registerOTP($otpData);

    if (!$result2['success']) {
        return response()->json([
            'status' => 'failed',
            'message' => $result2['error'],
        ], 400);
    }

    $emailSender = new EmailService();
    $to = $req->email;
    $subject = "HMS Account Verification Code";
    $body = "
    <h2>HMS Account Verification</h2>
    <p>Hello,</p>
    <p>Use the following One-Time Password (OTP) to verify your account:</p>
    <h1 style='color:#2d89ef;'>{$otp}</h1>
    <p>This OTP is valid for <strong>10 minutes</strong>. Do not share it.</p>
    <p>If you did not request this, please ignore this email.</p>
    <p>Thank you,<br>HMS Team</p>
    ";

    $emailSender = new EmailService();

    try {
        $emailSender->send($to, $subject, $body);
        echo "Email sent successfully!";
    } catch (\Exception $e) {
        echo "Email sending failed: " . $e->getMessage();
    }

    return response()->json([
        'status' => 'success',
        'message' => "Registration successful, kindly check your email for OTP verification.",
    ]);
});
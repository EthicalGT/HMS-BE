<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Hawkers;
use App\Models\Otp;
use App\Services\EmailService;

class HawkerController extends Controller
{
    private $hawkersModel;

    public function __construct()
    {
        $this->hawkersModel = new Hawkers();
        $this->otpModel = new Otp();
    }

    public function registerHawker(Request $req)
{
    $email = $req->email;

    if (session()->has('cu_email')) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An OTP has already been sent. Please check your email.',
        ], 400);
    }

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
        'profile_photo_url' => '/assets/img/profile_pictures/user.png',
    ];

    $result = $this->hawkersModel->registerHawkers($data);

    if (!$result['success']) {
        return response()->json([
            'status'  => 'failed',
            'message' => $result['error'],
        ], 400);
    }

    $otp = generate_otp(6);

    $otpData = [
        'hawker_mobile' => $data['phone_number'],
        'otp'         => $otp,
        'user_type'   => 'hawker',
    ];

    $result2 = $this->otpModel->registerOTP($otpData);

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
        <p>Dear {$data['full_name']},</p>
        <p>Use the following One-Time Password (OTP) to verify your account:</p>
        <h1 style='color:#2d89ef;'>{$otp}</h1>
        <p>This OTP is valid for <strong>10 minutes</strong>.</p>
        <p>Do not share this OTP with anyone.</p>
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
    echo "Session set for cu_email: " . session('cu_email');

    return response()->json([
        'status'  => 'success',
        'message' => 'Registration successful, kindly check your email for OTP verification.',
    ]);
    }

    public function loginHawker(Request $req)
    {
        $data = [
            'email' => $req->email,
            'password' => $req->password,
        ];

        if($this->hawkersModel->checkHawkerPresent($data['email']) === false) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Hawker account does not exist.',
            ], 404);
        }

        $stored_hash = $this->hawkersModel->retrieveHawkerPWD($data['email']);
        if (!$stored_hash || !Hash::check($data['password'], $stored_hash)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = create_jwt(['cuser' => $data['email'], 'role' => 'hawker'], 10);
        if(is_jwt_valid($token)) {
            echo "JWT Token is valid.";
        }else{
            echo "JWT Token is invalid.";
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
        ]);
    }

    public function getCurrentUser(){
        $cuser = session('cu_email');

        if (!$cuser) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No currently active user found for account otp verification.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cuser,
        ]);
    }
}

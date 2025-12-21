<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\EmailService;
use Illuminate\Support\Facades\Session;


class VendorController extends Controller
{
    private $vendorsModel;
    private $otpModel;

    public function __construct()
    {
        $this->vendorsModel = new Vendor();
        $this->otpModel = new Otp();
    }

    public function registerVendor(Request $req)
    {
        $email = $req->email;

        if (session()->has('cu_email')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'OTP already sent. Please check email.'
            ], 400);
        }

        $data = [
            'email'            => $req->email,
            'fullname'         => $req->full_name,
            'contact_no'       => $req->phone_number,
            'aadhaar_number'   => $req->aadhar_number,
            'firm_name'        => $req->firm_name,
            'product_category' => $req->product_category,
            'firm_addr'        => $req->address,
            'password'         => Hash::make($req->password),
            'city'             => $req->city,
            'gstin_no'         => $req->gst_number,
            'state'            => $req->state,
            'pincode'          => $req->pincode,
            'status'           => 'active',
            'role'             => 'vendor',
            'aadhaar_verified' => 'unverified'
        ];

        $result = $this->vendorsModel->registerVendor($data);

        if (!$result['success']) {
            return response()->json([
                'status' => 'failed',
                'message' => $result['error']
            ], 400);
        }

        $otp = generate_otp(6);

        $this->otpModel->registerOTP([
            'vendor_email' => $email,
            'otp' => $otp,
            'user_type' => 'vendor'
        ]);

        $emailSender = new EmailService();
        $emailSender->send(
            $email,
            'HMS Account Verification Code',
            "<h3>Your OTP: <b>{$otp}</b></h3>"
        );

        session(['cu_email' => $email]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful. Check email for OTP.',
            'redirectTo' => '/verify_otp'
        ]);
    }

    public function loginVendor(Request $req)
    {
        if (!$this->vendorsModel->checkVendorPresent($req->email)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Vendor account not found'
            ], 404);
        }

        $storedHash = $this->vendorsModel->retrieveVendorPWD($req->email);

        if (!Hash::check($req->password, $storedHash)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = create_jwt([
            'cuser' => $req->email,
            'role' => 'vendor'
        ], 10);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'redirectTo' => '/dashboard_vendor',
            'token' => $token
        ]);
    }

    public function getCurrentUser()
    {
        $cuser = session('cu_email');

        if (!$cuser) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No active OTP session found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cuser
        ]);
    }
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
            'redirectTo' => '/vendor_dashboard',
        ], 200);
    }

}

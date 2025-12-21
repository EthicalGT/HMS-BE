<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Hawkers;
use App\Models\Otp;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class HawkerController extends Controller
{
    private $hawkersModel;
    private $otpModel;

    public function __construct()
    {
        $this->hawkersModel = new Hawkers();
        $this->otpModel = new Otp();
    }

    public function registerHawker(Request $req)
    {
        $email = $req->email;

        if (Cache::has('cvuser')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'OTP already sent. Please check your email.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $hawkerData = [
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

            $result = $this->hawkersModel->registerHawkers($hawkerData);

            if (!$result['success']) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => $result['error'],
                ], 400);
            }

            $otp = generate_otp(6);

            $otpData = [
                'hawker_mobile' => $req->phone_number,
                'otp'           => $otp,
                'user_type'     => 'hawker',
            ];

            $otpResult = $this->otpModel->registerOTP($otpData);

            if (!$otpResult['success']) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => $otpResult['error'],
                ], 400);
            }

            $emailService = new EmailService();
            $emailService->send(
                $email,
                'HMS Account Verification Code',
                "
                <h2>HMS Account Verification</h2>
                <p>Dear {$req->full_name},</p>
                <p>Your OTP is:</p>
                <h1 style='color:#2d89ef;'>{$otp}</h1>
                <p>Valid for 10 minutes.</p>
                <p>Do not share this OTP.</p>
                <br>
                <p>— HMS Team</p>
                "
            );

            Cache::put('cvuser', $email, now()->addMinutes(10));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. Check email for OTP.',
                'redirectTo' => '/verify_otp',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function loginHawker(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$this->hawkersModel->checkHawkerPresent($req->email)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Hawker account does not exist.',
            ], 404);
        }

        $storedHash = $this->hawkersModel->retrieveHawkerPWD($req->email);

        if (!$storedHash || !Hash::check($req->password, $storedHash)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = create_jwt([
            'cuser' => $req->email,
            'role'  => 'hawker'
        ], 60 * 24);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'redirectTo' => '/dashboard_hawker',
            'token' => $token,
        ]);
    }

 public function getCurrentVerifyingUser()
{
    $cacheKey = 'cvuser';

    if (!Cache::has($cacheKey)) {
        Log::info('OTP cache not found', [
            'cache_key' => $cacheKey,
        ]);

        return response()->json([
            'status' => 'failed',
            'data' => null,
            'message' => 'No active OTP verification found for this user.',
        ], 404);
    }

    $cachedValue = Cache::get($cacheKey);

    Log::info('OTP cache found', [
        'cache_key' => $cacheKey,
        'cached_value' => $cachedValue,
    ]);

    return response()->json([
        'status' => 'success',
        'data' => $cachedValue,
    ]);
}
}
<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Services\EmailService;
use Exception;

class VendorAuthController extends Controller
{
    /**
     * Vendor Registration + Send OTP
     */
    public function register(Request $request)
    {
       
        try {
            // 2️⃣ Create Vendor
            $vendor = Vendor::create([
                'email'            => $request->email,
                'fullname'         => $request->full_name,
                'contact_no'       => $request->phone_number,
                'aadhaar_number'   => $request->aadhar_number,
                'firm_name'        => $request->firm_name,
                'product_category' => $request->product_category,
                'firm_addr'        => $request->address,
                'password'         => Hash::make($request->password),
                'city'             => $request->city,
                'gstin_no'         => $request->gst_number,
                'state'            => $request->state,
                'pincode'          => $request->pincode,
                'status'           => 'active',
                'role'             => 'vendor',
                'aadhaar_verified' => 'unverified'
            ]);

            // 3️⃣ Generate OTP (helper)
            $otp = generate_otp(6);

            // 4️⃣ Save OTP in SAME otp table
            Otp::create([
                'otp'          => $otp,
                'user_type'    => 'vendor',
                'vendor_email' => $vendor->email,
                'status'       => 'unverified',
                'expires_at'   => now()->addMinutes(10),
            ]);

            // 5️⃣ Send OTP email
            $emailService = new EmailService();
            $subject = "Vendor Account Verification Code";
            $body = "
                <h2>Vendor Account Verification</h2>
                <p>Hello {$vendor->fullname},</p>
                <p>Your OTP is:</p>
                <h1 style='color:#2d89ef;'>{$otp}</h1>
                <p>This OTP is valid for <b>10 minutes</b>.</p>
                <p>Do not share this OTP.</p>
                <br>
                <p>Regards,<br>HMS Team</p>
            ";

            $emailService->send($vendor->email, $subject, $body);

            return response()->json([
                'success' => true,
                'message' => 'Vendor registered successfully. OTP sent to email.',
                'data' => [
                    'email' => $vendor->email,
                    'role'  => 'vendor'
                ]
            ], 201);

        } catch (QueryException $e) {

            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate entry',
                    'error'   => 'Email / Contact / Aadhaar already exists'
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error'   => $e->getMessage()
            ], 500);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vendor OTP Verification
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Find valid OTP
        $otpRow = Otp::where('user_type', 'vendor')
            ->where('vendor_email', $request->email)
            ->where('status', 'unverified')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRow || $request->otp !== $otpRow->otp)
            {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Mark OTP verified
        $otpRow->update(['status' => 'verified']);

        // Update vendor Aadhaar verification
        Vendor::where('email', $request->email)
              ->update(['aadhaar_verified' => 'verified']);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. Vendor account activated.'
        ], 200);
    }
}

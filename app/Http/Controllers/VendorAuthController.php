<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorOtp;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VendorAuthController extends Controller
{
    /**
     * Register vendor
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'            => 'required|string|max:150',
                'contact_person'  => 'required|string|max:150',
                'phone'           => 'required|string|max:15|unique:vendors,phone',
                'email'           => 'required|email|max:150|unique:vendors,email',
                'password'        => 'required|min:6|confirmed',
                'aadhaar_no'      => 'required|string|size:12|unique:vendors,aadhaar_no',
                'address_line1'   => 'required|string|max:255',
                'address_line2'   => 'nullable|string|max:255',
                'city'            => 'required|string|max:100',
                'state'           => 'required|string|max:100',
                'pincode'         => 'required|string|max:10',
                'country'         => 'nullable|string|max:100',
                'gst_no'          =>    'nullable|string|max:20',

            ]);

            DB::transaction(function () use ($data) {

                $vendor = Vendor::create([
                    'name' => $data['name'],
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'aadhaar_no' => $data['aadhaar_no'],
                    'address_line1' => $data['address_line1'],
                    'address_line2' => $data['address_line2'] ?? null,
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'country' => $data['country'] ?? null,
                    'gst_no'=>$data['gst_no'],
                    'status' => 'inactive',
                ]);

                // ✅ OTP GENERATED FROM HELPER
                $otp = generate_otp(6);

                VendorOtp::create([
                    'vendor_id'  => $vendor->id,
                    'otp'        => $otp,
                    'status'     => 'unverified',
                    'expires_at' => now()->addMinutes(10),
                ]);

                // ✅ EMAIL SENDING (YOUR STYLE)
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

                $emailSender = new EmailService();
                $emailSender->send($vendor->email, $subject, $body);
            });

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. OTP sent to your email.',
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|string',
            ]);

            $vendor = Vendor::where('email', $data['email'])->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found',
                ], 404);
            }

            $otp = VendorOtp::where('vendor_id', $vendor->id)
                ->where('otp', $data['otp'])
                ->where('status', 'unverified')
                ->where('expires_at', '>', now())
                ->first();

            if (!$otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 401);
            }

            $otp->update(['status' => 'verified']);
            $vendor->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login vendor
     */
    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $vendor = Vendor::where('email', $data['email'])->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found',
                ], 404);
            }

            if (!Hash::check($data['password'], $vendor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if ($vendor->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not verified',
                ], 403);
            }

            // ✅ Generate JWT token using helper
            $token = create_jwt([
                'vendor_id' => $vendor->id,
                'email'     => $vendor->email,
            ], 120); // valid for 120 minutes

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token'   => $token,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
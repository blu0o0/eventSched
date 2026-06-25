<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * Send OTP for email verification during registration
     */
    public function sendVerificationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP
        \DB::table('otp_codes')->insert([
            'email' => $request->email,
            'otp' => bcrypt($otp),
            'type' => 'email_verification',
            'expires_at' => Carbon::now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send OTP via email
        try {
            Mail::send('emails.otp-verification', ['otp' => $otp, 'email' => $request->email], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Email Verification - OTP Code');
            });
        } catch (\Exception $e) {
            \Log::error('OTP email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    /**
     * Verify OTP during registration
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $otpRecord = \DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('type', 'email_verification')
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'No OTP code found. Please request a new code.'], 400);
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'OTP code has expired. Please request a new code.'], 400);
        }

        if (!password_verify($request->otp, $otpRecord->otp)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        // Mark OTP as used
        \DB::table('otp_codes')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => true]);

        return response()->json([
            'message' => 'Email verified successfully.',
            'email_verified' => true,
        ]);
    }

    /**
     * Send OTP for password reset
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'This email is not registered.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP
        \DB::table('otp_codes')->insert([
            'email' => $request->email,
            'otp' => bcrypt($otp),
            'type' => 'password_reset',
            'expires_at' => Carbon::now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send OTP via email
        try {
            Mail::send('emails.otp-password-reset', ['otp' => $otp, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset - OTP Code');
            });
        } catch (\Exception $e) {
            \Log::error('OTP email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Reset code sent to your email.',
        ]);
    }

    /**
     * Verify OTP only (for forgot password flow)
     */
    public function verifyResetOtpOnly(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        $otpRecord = \DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'No OTP code found. Please request a new code.'], 400);
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'OTP code has expired. Please request a new code.'], 400);
        }

        if (!password_verify($request->otp, $otpRecord->otp)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        return response()->json([
            'message' => 'OTP verified successfully.',
            'otp_valid' => true,
        ]);
    }

    /**
     * Verify OTP and reset password
     */
    public function verifyResetOtpAndReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => ['required', 'string', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]{8,}$/', 'confirmed'],
        ]);

        $otpRecord = \DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'No OTP code found. Please request a new code.'], 400);
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'OTP code has expired. Please request a new code.'], 400);
        }

        if (!password_verify($request->otp, $otpRecord->otp)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        // Mark OTP as used
        \DB::table('otp_codes')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => true]);

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        // Revoke all existing tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
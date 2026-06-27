<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Rules\Recaptcha;

class AuthController extends Controller
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 3;
    
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]{8,}$/', 'confirmed'],
            'role' => 'nullable|in:ADMINISTRATOR,SBO BSIT WMAD,SBO BSIT NETSEC,SBO BSA,SBL BSLEA,SSC OFFICER,FACULTY/STAFF,STUDENT',
            'email_verified' => 'required|boolean|accepted',
        ], [
            'password.regex' => 'Password must contain: 8+ characters, at least one letter (a-z, A-Z), at least one number (0-9), and at least one symbol (@$!%*?&.,)',
            'email_verified.accepted' => 'Email must be verified before registering.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'] ?? 'STUDENT',
            'email_verified' => true, // Already verified via OTP
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'recaptcha_token' => ['required', 'string', new Recaptcha],
        ]);

        $email = $validated['email'];
        $ipAddress = $request->ip();

        // Check if account is locked
        $loginAttempt = LoginAttempt::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($loginAttempt && $loginAttempt->locked_until && $loginAttempt->locked_until->isFuture()) {
            $remainingSeconds = now()->diffInSeconds($loginAttempt->locked_until);
            $remainingMinutes = ceil($remainingSeconds / 60);
            
            return response()->json([
                'message' => "Too many failed attempts. Please try again in {$remainingMinutes} minute(s).",
                'locked' => true,
                'locked_until' => $loginAttempt->locked_until->toIso8601String(),
            ], 429);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            // Failed login - increment attempts
            $this->incrementLoginAttempts($email, $ipAddress);
            
            // Get updated attempt count
            $updatedAttempt = LoginAttempt::where('email', $email)
                ->where('ip_address', $ipAddress)
                ->first();
            
            $attemptsRemaining = self::MAX_ATTEMPTS;
            if ($updatedAttempt) {
                $attemptsRemaining = max(0, self::MAX_ATTEMPTS - $updatedAttempt->attempts);
            }
            
            // Check if user exists to provide specific error message
            $userExists = User::where('email', $email)->exists();
            
            if (!$userExists) {
                return response()->json([
                    'message' => 'No registered email account.',
                    'error_type' => 'email_not_found',
                    'attempts_remaining' => $attemptsRemaining,
                ], 401);
            }

            return response()->json([
                'message' => 'Incorrect password.',
                'error_type' => 'incorrect_password',
                'attempts_remaining' => $attemptsRemaining,
            ], 401);
        }

        // Successful login - clear attempts
        if ($loginAttempt) {
            $loginAttempt->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'attempts_remaining' => self::MAX_ATTEMPTS,
        ]);
    }

    /**
     * Increment login attempts and lock account if needed
     */
    private function incrementLoginAttempts(string $email, string $ipAddress): void
    {
        $loginAttempt = LoginAttempt::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->first();

        if (!$loginAttempt) {
            $loginAttempt = new LoginAttempt([
                'email' => $email,
                'ip_address' => $ipAddress,
                'attempts' => 0,
            ]);
        }

        $loginAttempt->attempts += 1;
        $loginAttempt->last_attempt_at = now();

        if ($loginAttempt->attempts >= self::MAX_ATTEMPTS) {
            $loginAttempt->locked_until = now()->addMinutes(self::LOCKOUT_MINUTES);
        }

        $loginAttempt->save();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
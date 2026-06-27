<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 3;
    
    /**
     * Show the login form
     */
    public function showLoginForm(Request $request)
    {
        $email = $request->input('email');
        $ipAddress = $request->ip();
        
        $attemptsRemaining = self::MAX_ATTEMPTS;
        $isLocked = false;
        $lockedUntil = null;
        
        if ($email) {
            $loginAttempt = LoginAttempt::where('email', $email)
                ->where('ip_address', $ipAddress)
                ->first();
            
            if ($loginAttempt) {
                $attemptsRemaining = max(0, self::MAX_ATTEMPTS - $loginAttempt->attempts);
                
                if ($loginAttempt->locked_until && $loginAttempt->locked_until->isFuture()) {
                    $isLocked = true;
                    $lockedUntil = $loginAttempt->locked_until;
                }
            }
        }
        
        // Always show login form (user can logout from dashboard if already authenticated)
        return view('admin.auth.login', compact('attemptsRemaining', 'isLocked', 'lockedUntil', 'email'));
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha_token' => 'required',
        ]);

        $email = $request->input('email');
        $ipAddress = $request->ip();

        // Check if account is locked
        $loginAttempt = LoginAttempt::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($loginAttempt && $loginAttempt->locked_until && $loginAttempt->locked_until->isFuture()) {
            $remainingSeconds = now()->diffInSeconds($loginAttempt->locked_until);
            $remainingMinutes = ceil($remainingSeconds / 60);
            
            throw ValidationException::withMessages([
                'email' => ["Too many failed attempts. Please try again in {$remainingMinutes} minute(s)."],
            ]);
        }

        // Verify reCAPTCHA v3
        $recaptchaResponse = $request->input('recaptcha_token');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');
        
        $recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}&remoteip={$request->ip()}");
        $recaptcha = json_decode($recaptcha, true);
        
        // Check if reCAPTCHA was successful and score is above threshold (0.5)
        if (!$recaptcha['success'] || ($recaptcha['score'] ?? 0) < 0.5) {
            throw ValidationException::withMessages([
                'email' => ['reCAPTCHA verification failed. Please try again.'],
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear login attempts on successful login
            if ($loginAttempt) {
                $loginAttempt->delete();
            }
            
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isOsas()) {
                return redirect()->intended(route('admin.reservations.index'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

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
        $userExists = \App\Models\User::where('email', $email)->exists();
        
        if (!$userExists) {
            throw ValidationException::withMessages([
                'email' => ['No registered email account.'],
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Incorrect password.'],
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

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

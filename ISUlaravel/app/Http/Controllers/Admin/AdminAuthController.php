<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Always show login form (user can logout from dashboard if already authenticated)
        return view('admin.auth.login');
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
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isOsas()) {
                return redirect()->intended(route('admin.reservations.index'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
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

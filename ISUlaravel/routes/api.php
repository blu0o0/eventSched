<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\VenueController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\EmergencyController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\OtpController;

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// OTP routes
Route::post('/auth/send-verification-otp', [OtpController::class, 'sendVerificationOtp']);
Route::post('/auth/verify-email-otp', [OtpController::class, 'verifyEmailOtp']);
Route::post('/forgot-password/send-otp', [OtpController::class, 'sendResetOtp']);
Route::post('/forgot-password/verify-otp', [OtpController::class, 'verifyResetOtpOnly']);
Route::post('/forgot-password/reset-password', [OtpController::class, 'verifyResetOtpAndReset']);

// Public viewing routes (anyone can view)
Route::get('/venues', [VenueController::class, 'index']);
Route::get('/venues/{venue}', [VenueController::class, 'show']);
Route::get('/venues/map/data', [VenueController::class, 'mapData']);
Route::get('/areas', [AreaController::class, 'index']);
Route::get('/areas/{area}', [AreaController::class, 'show']);
Route::get('/calendar/events', [CalendarController::class, 'events']);
Route::get('/emergency/list', [EmergencyController::class, 'list']);
Route::get('/emergency/{emergency}', [EmergencyController::class, 'show']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Reservations - View/Create/Update/Delete
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::post('/reservations/{reservation}/reschedule', [ReservationController::class, 'reschedule']);

    // Profile management
    Route::put('/profile/name', [ProfileController::class, 'updateName']);

    // Emergency Reports - Create
    Route::post('/emergency', [EmergencyController::class, 'store']);
});

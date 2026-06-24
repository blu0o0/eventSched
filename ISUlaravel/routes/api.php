<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\VenueController;
use App\Http\Controllers\Api\EmergencyController;
use App\Http\Controllers\Api\CalendarController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Reservations
    Route::apiResource('reservations', ReservationController::class);
    Route::post('/reservations/{reservation}/reschedule', [ReservationController::class, 'reschedule']);

    // Venues
    Route::get('/venues', [VenueController::class, 'index']);
    Route::get('/venues/{venue}', [VenueController::class, 'show']);

    // Calendar
    Route::get('/calendar/events', [CalendarController::class, 'events']);

    // Emergency Reports
    Route::post('/emergency', [EmergencyController::class, 'store']);
    Route::get('/emergency/list', [EmergencyController::class, 'list']);
    Route::get('/emergency/{emergency}', [EmergencyController::class, 'show']);
});

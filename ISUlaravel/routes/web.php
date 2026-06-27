<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReservationController;
use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Controllers\Admin\AdminEmergencyController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAreaController;
use App\Http\Controllers\Api\CalendarController;

// Public routes
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin Authentication
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Admin Protected Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Reservations
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
    Route::post('/reservations/{reservation}/approve', [AdminReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/bulk-action', [AdminReservationController::class, 'bulkAction'])->name('reservations.bulk-action');
    Route::delete('/reservations/{reservation}', [AdminReservationController::class, 'destroy'])->name('reservations.destroy');

    // Calendar View
    Route::get('/calendar', [\App\Http\Controllers\Admin\AdminCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [\App\Http\Controllers\Admin\AdminCalendarController::class, 'events'])->name('calendar.events');

    // Venues
    Route::resource('venues', AdminVenueController::class);
    Route::get('/venues-map', [AdminVenueController::class, 'map'])->name('venues.map');

    // Areas
    Route::resource('areas', AdminAreaController::class);

    // Emergency Reports
    Route::get('/emergency/create', [AdminEmergencyController::class, 'create'])->name('emergency.create');
    Route::post('/emergency', [AdminEmergencyController::class, 'store'])->name('emergency.store');
    Route::get('/emergency', [AdminEmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/{emergency}', [AdminEmergencyController::class, 'show'])->name('emergency.show');
    Route::post('/emergency/{emergency}/resolve', [AdminEmergencyController::class, 'resolve'])->name('emergency.resolve');
    Route::delete('/emergency/{emergency}', [AdminEmergencyController::class, 'destroy'])->name('emergency.destroy');

    // Users
    Route::resource('users', AdminUserController::class);
});

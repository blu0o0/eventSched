<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Venue;
use App\Models\EmergencyReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Ensure user is administrator
        if (!auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
        // Filter reservations to only Santiago Campus venues
        $santiagoReservationsQuery = Reservation::whereHas('venue', function ($q) {
            $q->where('location', 'Santiago Campus');
        });

        $stats = [
            'total_reservations' => $santiagoReservationsQuery->count(),
            'pending_reservations' => (clone $santiagoReservationsQuery)->where('status', 'pending')->count(),
            'approved_reservations' => (clone $santiagoReservationsQuery)->where('status', 'approved')->count(),
            'rejected_reservations' => (clone $santiagoReservationsQuery)->where('status', 'rejected')->count(),
            'total_venues' => Venue::where('location', 'Santiago Campus')->count(),
            'open_emergencies' => EmergencyReport::where('status', 'open')->count(),
            'total_users' => User::count(),
        ];

        $recent_reservations = Reservation::with(['venue', 'user'])
            ->whereHas('venue', function ($q) {
                $q->where('location', 'Santiago Campus');
            })
            ->latest()
            ->limit(10)
            ->get();

        $recent_emergencies = EmergencyReport::with('reporter')
            ->where('status', 'open')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_reservations', 'recent_emergencies'));
    }
}

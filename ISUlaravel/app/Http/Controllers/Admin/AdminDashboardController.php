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
        // Show all reservations (matching the admin venues page and Create Reservation form)
        $reservationsQuery = Reservation::query();

        $stats = [
            'total_reservations' => $reservationsQuery->count(),
            'pending_reservations' => (clone $reservationsQuery)->where('status', 'pending')->count(),
            'approved_reservations' => (clone $reservationsQuery)->where('status', 'approved')->count(),
            'rejected_reservations' => (clone $reservationsQuery)->where('status', 'rejected')->count(),
            'total_venues' => Venue::count(),
            'open_emergencies' => EmergencyReport::where('status', 'open')->count(),
            'total_users' => User::count(),
        ];

        $recent_reservations = Reservation::with(['venue', 'user', 'area'])
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

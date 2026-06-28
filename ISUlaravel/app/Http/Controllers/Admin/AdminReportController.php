<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use App\Models\EmergencyReport;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $startDateStr = $start->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $users = User::whereBetween('created_at', [$start, $end])->get();

        // Filter reservations by event date (date field), not created_at
        // Include reservations where:
        // 1. The start date is within the range, OR
        // 2. The end date is within the range, OR
        // 3. The reservation spans across the entire range (starts before and ends after)
        $reservations = Reservation::with(['user', 'venue'])
            ->where(function ($query) use ($startDateStr, $endDateStr) {
                // Reservation starts within the date range
                $query->whereBetween('date', [$startDateStr, $endDateStr])
                    // OR reservation ends within the date range (for multi-day events)
                    ->orWhereBetween('end_date', [$startDateStr, $endDateStr])
                    // OR reservation spans across the entire range (starts before and ends after)
                    ->orWhere(function ($q) use ($startDateStr, $endDateStr) {
                        $q->where('date', '<', $startDateStr)
                          ->where('end_date', '>', $endDateStr);
                    });
            })
            ->get();

        $emergencies = EmergencyReport::with(['reporter'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return view('admin.reports.index', compact(
            'start',
            'end',
            'users',
            'reservations',
            'emergencies'
        ));
    }

    public function print(Request $request)
    {
        $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
        $startDateStr = $start->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $users = User::whereBetween('created_at', [$start, $end])->get();

        // Filter reservations by event date (date field), not created_at
        $reservations = Reservation::with(['user', 'venue'])
            ->where(function ($query) use ($startDateStr, $endDateStr) {
                // Reservation starts within the date range
                $query->whereBetween('date', [$startDateStr, $endDateStr])
                    // OR reservation ends within the date range (for multi-day events)
                    ->orWhereBetween('end_date', [$startDateStr, $endDateStr])
                    // OR reservation spans across the entire range (starts before and ends after)
                    ->orWhere(function ($q) use ($startDateStr, $endDateStr) {
                        $q->where('date', '<', $startDateStr)
                          ->where('end_date', '>', $endDateStr);
                    });
            })
            ->get();

        $emergencies = EmergencyReport::with(['reporter'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return view('admin.reports.print', compact(
            'start',
            'end',
            'users',
            'reservations',
            'emergencies'
        ));
    }
}
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

        $users = User::whereBetween('created_at', [$start, $end])->get();

        $reservations = Reservation::with(['user', 'venue'])
            ->whereBetween('created_at', [$start, $end])
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

    $users = User::whereBetween('created_at', [$start, $end])->get();

    $reservations = Reservation::with(['user', 'venue'])
        ->whereBetween('created_at', [$start, $end])
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


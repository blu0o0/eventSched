<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminCalendarController extends Controller
{
    /**
     * Check if user is administrator
     */
    protected function ensureAdmin()
    {
        if (!auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Check if user is administrator or SSC Officer (for calendar access)
     */
    protected function ensureAdminOrSscOfficer()
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->isSscOfficer()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Display the calendar view
     */
    public function index()
    {
        $this->ensureAdminOrSscOfficer();
        return view('admin.calendar.index');
    }

    /**
     * Get calendar events for FullCalendar.js (web version)
     */
    public function events(Request $request)
    {
        $this->ensureAdminOrSscOfficer();
        $query = Reservation::with(['venue', 'user'])
            ->whereHas('venue', function ($q) {
                $q->where('location', 'Santiago Campus');
            });

        // Filter by date range if provided
        if ($request->has('start')) {
            $query->where('date', '>=', $request->start);
        }
        if ($request->has('end')) {
            $query->where('date', '<=', $request->end);
        }

        $reservations = $query->get();

        $events = $reservations->map(function ($reservation) {
            // Color coding: pending = yellow, approved = green, rejected = red
            $color = match($reservation->status) {
                'pending' => '#ffc107',
                'approved' => '#28a745',
                'rejected' => '#dc3545',
                default => '#6c757d',
            };

            return [
                'id' => $reservation->id,
                'title' => $reservation->title,
                'start' => $reservation->date->format('Y-m-d') . 'T' . $reservation->start_time,
                'end' => $reservation->date->format('Y-m-d') . 'T' . $reservation->end_time,
                'color' => $color,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'venue' => $reservation->venue->name,
                    'status' => $reservation->status,
                    'description' => $reservation->description,
                    'capacity' => $reservation->capacity,
                    'user' => $reservation->user->name,
                ],
            ];
        });

        return response()->json($events);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{

    /**
     * Get calendar events for FullCalendar.js
     */
    public function events(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Reservation::with(['venue', 'user']);

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

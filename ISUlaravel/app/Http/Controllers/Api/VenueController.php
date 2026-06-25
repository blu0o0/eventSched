<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Reservation;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Only return Santiago Campus venues for mobile app users
        $venues = Venue::where('location', 'Santiago Campus')
            ->latest()
            ->get();

        return response()->json([
            'data' => VenueResource::collection($venues),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue): JsonResponse
    {
        return response()->json([
            'data' => new VenueResource($venue),
        ]);
    }

    /**
     * Get venue map data with availability information.
     */
    public function mapData(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        
        // Get all Santiago Campus venues
        $venues = Venue::where('location', 'Santiago Campus')->get();
        
        // Get reservations for the selected date
        $reservations = Reservation::where('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->get()
            ->groupBy('venue_id');
        
        // Calculate venue availability
        $venueAvailability = [];
        foreach ($venues as $venue) {
            $venueReservations = $reservations->get($venue->id, collect());
            $reservationCount = $venueReservations->count();
            $isAvailable = $reservationCount === 0;
            $isCurrentlyOccupied = false;
            
            // Check if currently occupied (current time falls within any reservation)
            $currentTime = now()->format('H:i');
            foreach ($venueReservations as $reservation) {
                if ($currentTime >= $reservation->start_time && $currentTime <= $reservation->end_time) {
                    $isCurrentlyOccupied = true;
                    break;
                }
            }
            
            $venueAvailability[$venue->id] = [
                'is_available' => $isAvailable,
                'is_currently_occupied' => $isCurrentlyOccupied,
                'reservation_count' => $reservationCount,
                'reservations' => $venueReservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'title' => $reservation->title,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                    ];
                })->values()->toArray(),
            ];
        }
        
        return response()->json([
            'venues' => VenueResource::collection($venues),
            'venue_availability' => $venueAvailability,
            'selected_date' => $date,
        ]);
    }
}

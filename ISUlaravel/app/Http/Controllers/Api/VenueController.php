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
        // Return all venues (admin venues page shows all venues)
        $venues = Venue::latest()->get();

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
        
        // Get all venues with areas
        $venues = Venue::with('areas')->get();
        
        // Get reservations for the selected date
        $reservations = Reservation::where('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->get()
            ->groupBy('venue_id');
        
        // Calculate venue availability
        $venueAvailability = [];
        
        // Get total reservations per venue (all time, all statuses)
        $totalReservations = Reservation::select('venue_id')
            ->selectRaw('count(*) as total_count')
            ->groupBy('venue_id')
            ->pluck('total_count', 'venue_id');
        
        // Get all reservations per venue (all time, all statuses) with relationships
        $allReservationsByVenue = Reservation::with(['user', 'area'])
            ->get()
            ->groupBy('venue_id')
            ->map(function ($reservations) {
                return $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'title' => $reservation->title,
                        'status' => $reservation->status,
                        'date' => $reservation->date,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                        'user' => $reservation->user ? [
                            'name' => $reservation->user->name,
                        ] : null,
                    'area' => $reservation->area ? [
                        'id' => $reservation->area->id,
                        'name' => $reservation->area->name,
                        'photo_url' => $reservation->area->photo_url,
                    ] : null,
                    ];
                })->values()->toArray();
            });
        
        foreach ($venues as $venue) {
            $venueReservations = $reservations->get($venue->id, collect());
            $reservationCount = $venueReservations->count();
            $totalCount = $totalReservations->get($venue->id, 0);
            $allVenueReservations = $allReservationsByVenue->get($venue->id, []);
            
            // A venue is only available if:
            // 1. Its status is 'available' (not damaged or under_construction)
            // 2. It has no approved/pending reservations for the selected date
            $isAvailable = $venue->isAvailable() && $reservationCount === 0;
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
                'total_reservations' => $totalCount,
                'reservations' => $venueReservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'title' => $reservation->title,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                    ];
                })->values()->toArray(),
                'all_reservations' => $allVenueReservations,
            ];
        }
        
        // Get all areas with coordinates
        $areas = \App\Models\Area::whereNotNull('map_coordinates')
            ->where('map_coordinates', '!=', '')
            ->with('venue')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'status' => $area->status,
                    'map_coordinates' => $area->map_coordinates,
                    'photo_url' => $area->photo_url,
                    'venue' => $area->venue ? [
                        'id' => $area->venue->id,
                        'name' => $area->venue->name,
                    ] : null,
                ];
            })->values()->toArray();
        
        // Get all reservations for the selected date with relationships
        $reservationsData = Reservation::where('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->with(['user', 'venue', 'area'])
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'title' => $reservation->title,
                    'date' => $reservation->date,
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time,
                    'status' => $reservation->status,
                    'user' => $reservation->user ? [
                        'name' => $reservation->user->name,
                    ] : null,
                    'venue' => $reservation->venue ? [
                        'id' => $reservation->venue->id,
                        'name' => $reservation->venue->name,
                    ] : null,
                    'area' => $reservation->area ? [
                        'id' => $reservation->area->id,
                        'name' => $reservation->area->name,
                        'map_coordinates' => $reservation->area->map_coordinates,
                        'photo_url' => $reservation->area->photo_url,
                    ] : null,
                ];
            })->values()->toArray();
        
        return response()->json([
            'venues' => VenueResource::collection($venues),
            'venue_availability' => $venueAvailability,
            'selected_date' => $date,
            'areas' => $areas,
            'reservations' => $reservationsData,
        ]);
    }
}

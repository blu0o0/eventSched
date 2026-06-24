<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    public function __construct(ReservationService $reservationService) {
        $this->reservationService = $reservationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Reservation::with(['venue', 'user']);

        // Non-admins can only see their own reservations
        if (!$user->isAdministrator()) {
            $query->where('user_id', $user->id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(15);

        // Check and postpone any approved reservations with unavailable venues
        foreach ($reservations as $reservation) {
            if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
                $this->checkAndPostponeReservation($reservation);
            }
        }

        // Refresh reservations to get updated statuses
        $reservations->load(['venue', 'user']);

        return response()->json([
            'data' => ReservationResource::collection($reservations),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $this->authorize('create', Reservation::class);

        try {
            $reservation = $this->reservationService->create(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'message' => 'Reservation created successfully',
                'data' => new ReservationResource($reservation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create reservation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $this->authorize('view', $reservation);

        // Check if venue is unavailable and automatically postpone if needed
        $reservation->load('venue');
        if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
            $this->checkAndPostponeReservation($reservation);
            $reservation->refresh();
        }

        return response()->json([
            'data' => new ReservationResource($reservation->load(['venue', 'user', 'approver'])),
        ]);
    }

    /**
     * Check and postpone a reservation if venue is unavailable
     */
    protected function checkAndPostponeReservation(Reservation $reservation): void
    {
        $venue = $reservation->venue;
        
        if (!$venue || !$venue->isUnavailable()) {
            return;
        }

        // Only postpone if reservation date is on or before unavailable_until date
        // Or if unavailable_until is not set, postpone all upcoming
        $shouldPostpone = false;
        
        if (!$venue->unavailable_until) {
            // If no unavailable_until date, postpone if reservation is in the future
            $shouldPostpone = $reservation->date >= now()->toDateString();
        } else {
            // Postpone if reservation date is on or before unavailable_until
            $shouldPostpone = $reservation->date <= $venue->unavailable_until;
        }

        if ($shouldPostpone && $reservation->isApproved()) {
            $reservation->status = 'postponed';
            $reservation->postponement_reason = sprintf(
                'Venue is %s. %s',
                $venue->status === 'damaged' ? 'damaged' : 'under construction',
                $venue->unavailable_until 
                    ? "Expected to be available again on " . \Carbon\Carbon::parse($venue->unavailable_until)->format('F d, Y') . "."
                    : 'Please contact administrator for availability updates.'
            );
            $reservation->save();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        try {
            $reservation = $this->reservationService->update(
                $reservation,
                $request->validated()
            );

            return response()->json([
                'message' => 'Reservation updated successfully',
                'data' => new ReservationResource($reservation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update reservation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        $this->authorize('delete', $reservation);

        $reservation->delete();

        return response()->json([
            'message' => 'Reservation deleted successfully',
        ]);
    }

    /**
     * Reschedule or change venue for a postponed reservation
     */
    public function reschedule(Request $request, Reservation $reservation): JsonResponse
    {
        // Only allow rescheduling if reservation is postponed
        if (!$reservation->isPostponed()) {
            return response()->json([
                'message' => 'Only postponed reservations can be rescheduled',
            ], 422);
        }

        // Only the owner can reschedule
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Clean up empty strings to null
        $data = $request->all();
        foreach (['venue_id', 'date', 'start_time', 'end_time'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        $request->merge($data);
        
        $validated = $request->validate([
            'venue_id' => 'nullable|integer|exists:venues,id',
            'date' => [
                'nullable',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) use ($request, $reservation) {
                    if ($value) {
                        // Get the venue (either new one or current one)
                        $newVenueId = $request->input('venue_id');
                        $isVenueChanged = $newVenueId && $newVenueId != $reservation->venue_id;
                        
                        if ($isVenueChanged) {
                            // If venue is changed, check if new venue is available and is Santiago Campus
                            $newVenue = \App\Models\Venue::find($newVenueId);
                            if (!$newVenue || !$newVenue->isAvailable()) {
                                $fail('The selected venue is not available. Please choose an available venue.');
                            }
                            if ($newVenue->location !== 'Santiago Campus') {
                                $fail('Reservations can only be made for Santiago Campus venues.');
                            }
                            // If venue is changed to an available one, any date is fine (already validated as after_or_equal:today)
                        } else {
                            // If same venue (or venue not changed), check that date is after unavailable_until
                            $venue = $reservation->venue;
                            if ($venue && $venue->isUnavailable() && $venue->unavailable_until) {
                                $selectedDate = \Carbon\Carbon::parse($value);
                                $unavailableUntil = \Carbon\Carbon::parse($venue->unavailable_until);
                                
                                if ($selectedDate <= $unavailableUntil) {
                                    $fail('The date must be after ' . $unavailableUntil->format('F d, Y') . ' when the venue becomes available.');
                                }
                            }
                        }
                    }
                },
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $reservation) {
                    // Get end_time from request or use current reservation end_time
                    $endTimeValue = $request->input('end_time') ?? $reservation->end_time;
                    
                    // Only validate if both start_time and end_time exist
                    if ($value && $endTimeValue) {
                        try {
                            $startTime = \Carbon\Carbon::parse($value);
                            $endTime = \Carbon\Carbon::parse($endTimeValue);
                            if ($startTime >= $endTime) {
                                $fail('The start time must be before the end time.');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid time format.');
                        }
                    }
                },
            ],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $reservation) {
                    // Get start_time from request or use current reservation start_time
                    $startTimeValue = $request->input('start_time') ?? $reservation->start_time;
                    
                    // Only validate if both start_time and end_time exist
                    if ($value && $startTimeValue) {
                        try {
                            $startTime = \Carbon\Carbon::parse($startTimeValue);
                            $endTime = \Carbon\Carbon::parse($value);
                            if ($endTime <= $startTime) {
                                $fail('The end time must be after the start time.');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid time format.');
                        }
                    }
                },
            ],
        ]);

        try {
            // If venue is changed, check if new venue is available (already validated in date validation, but double-check)
            if (isset($validated['venue_id']) && $validated['venue_id'] != $reservation->venue_id) {
                $newVenue = \App\Models\Venue::find($validated['venue_id']);
                if (!$newVenue || !$newVenue->isAvailable()) {
                    return response()->json([
                        'message' => 'Selected venue is not available',
                    ], 422);
                }
            }

            // Build update data
            $updateData = [];
            if (isset($validated['venue_id'])) {
                $updateData['venue_id'] = $validated['venue_id'];
            }
            if (isset($validated['date'])) {
                $updateData['date'] = $validated['date'];
            }
            if (isset($validated['start_time'])) {
                $updateData['start_time'] = $validated['start_time'];
            }
            if (isset($validated['end_time'])) {
                $updateData['end_time'] = $validated['end_time'];
            }

            // Check for overlaps with approved reservations before updating
            $tempReservation = $reservation->replicate();
            $tempReservation->fill($updateData);
            $conflicts = $this->reservationService->getConflictingReservations($tempReservation);
            
            if (!empty($conflicts)) {
                $conflictMessages = [];
                foreach ($conflicts as $conflict) {
                    $conflictMessages[] = sprintf(
                        '"%s" by %s (%s - %s)',
                        $conflict['title'],
                        $conflict['user_name'],
                        \Carbon\Carbon::parse($conflict['start_time'])->format('g:i A'),
                        \Carbon\Carbon::parse($conflict['end_time'])->format('g:i A')
                    );
                }
                return response()->json([
                    'message' => 'This reservation overlaps with existing approved reservation(s): ' . implode(', ', $conflictMessages),
                ], 422);
            }

            // Change status back to pending for admin approval
            $updateData['status'] = 'pending';
            $updateData['postponement_reason'] = null;

            $reservation->update($updateData);

            return response()->json([
                'message' => 'Reservation rescheduled successfully. Status changed to pending for admin approval.',
                'data' => new ReservationResource($reservation->load(['venue', 'user'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reschedule reservation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}

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
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService) {
        $this->reservationService = $reservationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Reservation::with(['venue', 'user', 'area']);
        
        // If user is authenticated, filter by mine parameter
        if ($user && $request->has('mine') && $request->mine === 'true') {
            $query->where('user_id', $user->id);
        } elseif (!$user && $request->has('mine') && $request->mine === 'true') {
            // If not authenticated and mine=true, return empty result
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ],
            ]);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->get();

        // Check and postpone any approved reservations with unavailable venues
        foreach ($reservations as $reservation) {
            if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
                $this->checkAndPostponeReservation($reservation);
            }
        }

        // Refresh reservations to get updated statuses
        $reservations->load(['venue', 'user', 'area']);

        return response()->json([
            'data' => ReservationResource::collection($reservations),
            'meta' => [
                'total' => $reservations->count(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $this->authorize('create', Reservation::class);

        $force = $request->boolean('force', false);

        try {
            $validated = $request->validated();
            
            // Handle event approval file upload
            if ($request->hasFile('event_approval_file')) {
                $file = $request->file('event_approval_file');
                $path = $file->store('event-approvals', 'public');
                $validated['event_approval_file'] = $path;
            }

            $reservation = $this->reservationService->create(
                $validated,
                $request->user()->id,
                $force
            );

            return response()->json([
                'message' => 'Reservation created successfully',
                'data' => new ReservationResource($reservation),
            ], 201);
        } catch (\Exception $e) {
            // If the error contains overlap/conflict info, return structured conflict data
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'overlaps') || str_contains($errorMessage, 'conflict')) {
                // Build a temp reservation to get conflict details
                $tempReservation = new \App\Models\Reservation($request->validated());
                $tempReservation->user_id = $request->user()->id;
                $conflicts = $this->reservationService->getConflictingReservations($tempReservation);

                return response()->json([
                    'message' => 'This reservation overlaps with existing reservation(s).',
                    'error' => $errorMessage,
                    'conflicts' => $conflicts,
                ], 409);
            }

            return response()->json([
                'message' => 'Failed to create reservation',
                'error' => $errorMessage,
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation): JsonResponse
    {
        // Allow public viewing of reservations
        // Check if venue is unavailable and automatically postpone if needed
        $reservation->load('venue');
        if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
            $this->checkAndPostponeReservation($reservation);
            $reservation->refresh();
        }

        return response()->json([
            'data' => new ReservationResource($reservation->load(['venue', 'user', 'approver', 'area'])),
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
    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        try {
            // Handle both JSON and FormData
            $updateData = [];
            
            // If it's a file upload (FormData), use validation
            if ($request->hasFile('event_approval_file')) {
                $validator = \Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'venue_id' => 'required|exists:venues,id',
                    'area_id' => 'nullable|integer|exists:areas,id',
                    'area_name' => 'nullable|string|max:255',
                    'date' => 'required|date|after_or_equal:today',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
                    'capacity' => 'required|integer|min:1',
                    'end_date' => 'nullable|date|after_or_equal:date',
                    'event_approval_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                    'existing_event_approval_file' => 'nullable|string',
                    'force' => 'nullable|boolean',
                ]);
                
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                $updateData = $validator->validated();
            } else {
                // JSON request - get all data directly
                $updateData = $request->all();
            }
            
            $force = $request->boolean('force', false);
            
            // Handle event approval file upload
            if ($request->hasFile('event_approval_file')) {
                // Delete old file if exists
                if ($reservation->event_approval_file && Storage::disk('public')->exists($reservation->event_approval_file)) {
                    Storage::disk('public')->delete($reservation->event_approval_file);
                }
                // Upload new file
                $file = $request->file('event_approval_file');
                $path = $file->store('event-approvals', 'public');
                $updateData['event_approval_file'] = $path;
            } elseif ($request->has('existing_event_approval_file')) {
                // Keep existing file if no new file is uploaded
                $updateData['event_approval_file'] = $request->input('existing_event_approval_file');
            }
            
            // Check for overlaps with approved reservations before updating
            // Always check for conflicts with approved reservations (can't override approved)
            // Only skip check for pending conflicts if force is true and reservation is pending
            $tempReservation = $reservation->replicate();
            $tempReservation->id = $reservation->id; // Preserve ID so conflict check excludes this reservation
            $tempReservation->fill($updateData);
            $allConflicts = $this->reservationService->getConflictingReservations($tempReservation);
            
            if (!empty($allConflicts)) {
                // Filter conflicts to only show approved reservations (pending conflicts can be overridden with force)
                $approvedConflicts = array_filter($allConflicts, function($conflict) {
                    return $conflict['status'] === 'approved';
                });
                
                // If there are approved conflicts, always block (can't override approved reservations)
                if (!empty($approvedConflicts)) {
                    return response()->json([
                        'message' => 'This reservation overlaps with existing approved reservation(s).',
                        'conflicts' => array_values($approvedConflicts),
                    ], 409);
                }
                
                // If only pending conflicts and not forcing, show conflict modal
                if (!$force) {
                    return response()->json([
                        'message' => 'This reservation overlaps with existing reservation(s).',
                        'conflicts' => $allConflicts,
                    ], 409);
                }
                
                // If forcing and only pending conflicts, allow the update (pending can override pending)
            }
            
            $reservation = $this->reservationService->update(
                $reservation,
                $updateData,
                $force
            );

            return response()->json([
                'message' => 'Reservation updated successfully',
                'data' => new ReservationResource($reservation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update reservation: ' . $e->getMessage(),
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
                // Return conflicts in the same format as create() so the frontend can show the conflict modal
                return response()->json([
                    'message' => 'This reservation overlaps with existing approved reservation(s).',
                    'conflicts' => $conflicts,
                ], 409);
            }

            // Change status back to pending for admin approval
            $updateData['status'] = 'pending';
            $updateData['postponement_reason'] = null;

            $reservation->update($updateData);

            return response()->json([
                'message' => 'Reservation rescheduled successfully. Status changed to pending for admin approval.',
                'data' => new ReservationResource($reservation->load(['venue', 'user', 'area'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reschedule reservation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {
    }

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
     * Check if user is administrator or OSAS (for reservation approval/rejection)
     */
    protected function ensureAdminOrOsas()
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->isOsas()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Display a listing of reservations
     */
    public function index(Request $request)
    {
        $this->ensureAdminOrOsas();
        $query = Reservation::with(['venue', 'user'])
            ->whereHas('venue', function ($q) {
                $q->where('location', 'Santiago Campus');
            });

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reservations = $query->latest()->paginate(15);

        // Check and postpone any approved reservations with unavailable venues
        foreach ($reservations as $reservation) {
            if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
                $this->checkAndPostponeReservation($reservation);
            }
        }

        // Refresh to get updated statuses
        $reservations->load(['venue', 'user']);

        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Display the specified reservation
     */
    public function show(Request $request, Reservation $reservation)
    {
        $this->ensureAdminOrOsas();
        $reservation->load(['venue', 'user', 'approver']);

        // Check if venue is unavailable and automatically postpone if needed
        if ($reservation->isApproved() && $reservation->venue && $reservation->venue->isUnavailable()) {
            $this->checkAndPostponeReservation($reservation);
            $reservation->refresh()->load(['venue', 'user', 'approver']);
        }

        // Return JSON if requested via AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'data' => [
                    'id' => $reservation->id,
                    'title' => $reservation->title,
                    'description' => $reservation->description,
                    'venue' => [
                        'id' => $reservation->venue->id,
                        'name' => $reservation->venue->name,
                    ],
                    'date' => $reservation->date->format('Y-m-d'),
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time,
                    'capacity' => $reservation->capacity,
                    'status' => $reservation->status,
                    'user' => [
                        'id' => $reservation->user->id,
                        'name' => $reservation->user->name,
                    ],
                ],
            ]);
        }

        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Approve a reservation
     */
    public function approve(Request $request, Reservation $reservation)
    {
        $this->ensureAdminOrOsas();
        try {
            $this->reservationService->approve($reservation, $request->user()->id);

            return redirect()->route('admin.reservations.index')
                ->with('success', 'Reservation approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a reservation
     */
    public function reject(Request $request, Reservation $reservation)
    {
        $this->ensureAdminOrOsas();
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservationService->reject(
                $reservation,
                $request->user()->id,
                $request->rejection_reason
            );

            return redirect()->route('admin.reservations.index')
                ->with('success', 'Reservation rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a reservation
     */
    public function destroy(Reservation $reservation)
    {
        $this->ensureAdmin();
        $reservation->delete();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    /**
      * Show the form for editing the specified reservation.
      */
    public function edit(Reservation $reservation)
    {
        $this->ensureAdmin();
        $reservation->load(['venue', 'user']);
        $venues = \App\Models\Venue::where('location', 'Santiago Campus')->get();

        return view('admin.reservations.edit', compact('reservation', 'venues'));
    }

    /**
      * Update the specified reservation in storage.
      */
    public function update(Request $request, Reservation $reservation)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'venue_id' => ['required', 'exists:venues,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $reservation->update($validated);
        
        // Set edited_at timestamp to indicate this reservation was edited after approval
        $reservation->edited_at = now();
        $reservation->save();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation updated successfully.');
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
}
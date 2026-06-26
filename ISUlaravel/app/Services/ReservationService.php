<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Check if a reservation conflicts with existing pending or approved reservations
     */
    public function hasConflict(Reservation $reservation): bool
    {
        $conflictingReservations = Reservation::where('venue_id', $reservation->venue_id)
            ->where('date', $reservation->date)
            ->whereIn('status', ['pending', 'approved'])
            ->where('id', '!=', $reservation->id ?? 0)
            ->get();

        foreach ($conflictingReservations as $existing) {
            if ($reservation->overlapsWith($existing)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get conflicting reservations with details
     */
    public function getConflictingReservations(Reservation $reservation): array
    {
        $conflictingReservations = Reservation::where('venue_id', $reservation->venue_id)
            ->where('date', $reservation->date)
            ->whereIn('status', ['pending', 'approved'])
            ->where('id', '!=', $reservation->id ?? 0)
            ->with(['user', 'venue'])
            ->get();

        $conflicts = [];
        foreach ($conflictingReservations as $existing) {
            if ($reservation->overlapsWith($existing)) {
                $conflicts[] = [
                    'id' => $existing->id,
                    'title' => $existing->title,
                    'user_name' => $existing->user->name ?? 'Unknown',
                    'venue_name' => $existing->venue->name ?? 'Unknown',
                    'date' => $existing->date instanceof Carbon ? $existing->date->format('Y-m-d') : (string) $existing->date,
                    'start_time' => $existing->start_time,
                    'end_time' => $existing->end_time,
                    'capacity' => $existing->capacity,
                    'description' => $existing->description,
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Create a new reservation
     */
    public function create(array $data, int $userId, bool $force = false): Reservation
    {
        // Check if venue is available
        $venue = Venue::find($data['venue_id']);
        if (!$venue || !$venue->isAvailable()) {
            throw new \Exception('The selected venue is currently unavailable. Please select another venue.');
        }

        // Only allow reservations for Santiago Campus venues
        if ($venue->location !== 'Santiago Campus') {
            throw new \Exception('Reservations can only be made for Santiago Campus venues.');
        }

        // Ensure date is in correct format (Y-m-d) without time component
        if (isset($data['date'])) {
            $data['date'] = is_string($data['date']) ? $data['date'] : Carbon::parse($data['date'])->format('Y-m-d');
        }
        
        $reservation = new Reservation($data);
        $reservation->user_id = $userId;
        $reservation->status = 'pending';
        
        // Check for overlaps with approved reservations (skip if force=true)
        if (!$force) {
            $conflicts = $this->getConflictingReservations($reservation);
            if (!empty($conflicts)) {
                $conflictMessages = [];
                foreach ($conflicts as $conflict) {
                    $conflictMessages[] = sprintf(
                        '"%s" by %s (%s - %s)',
                        $conflict['title'],
                        $conflict['user_name'],
                        Carbon::parse($conflict['start_time'])->format('g:i A'),
                        Carbon::parse($conflict['end_time'])->format('g:i A')
                    );
                }
                throw new \Exception('This reservation overlaps with existing approved reservation(s): ' . implode(', ', $conflictMessages));
            }
        }
        
        $reservation->save();

        return $reservation->load(['venue', 'user']);
    }

    /**
     * Update a reservation
     */
    public function update(Reservation $reservation, array $data): Reservation
    {
        // Temporarily update to check for conflicts
        $tempReservation = $reservation->replicate();
        $tempReservation->fill($data);
        
        // Check for overlaps with approved reservations (if date, time, or venue changed)
        if (isset($data['date']) || isset($data['start_time']) || isset($data['end_time']) || isset($data['venue_id'])) {
            $conflicts = $this->getConflictingReservations($tempReservation);
            if (!empty($conflicts)) {
                $conflictMessages = [];
                foreach ($conflicts as $conflict) {
                    $conflictMessages[] = sprintf(
                        '"%s" by %s (%s - %s)',
                        $conflict['title'],
                        $conflict['user_name'],
                        Carbon::parse($conflict['start_time'])->format('g:i A'),
                        Carbon::parse($conflict['end_time'])->format('g:i A')
                    );
                }
                throw new \Exception('This reservation overlaps with existing approved reservation(s): ' . implode(', ', $conflictMessages));
            }
        }
        
        $reservation->fill($data);
        $reservation->save();

        return $reservation->load(['venue', 'user']);
    }

    /**
     * Approve a reservation
     */
    public function approve(Reservation $reservation, int $adminId): Reservation
    {
        // Check if venue is available
        $venue = $reservation->venue;
        if (!$venue || !$venue->isAvailable()) {
            throw new \Exception('Cannot approve reservation. The venue is currently unavailable.');
        }

        // Check for conflicts before approving
        if ($this->hasConflict($reservation)) {
            throw new \Exception('This reservation conflicts with an existing approved reservation.');
        }

        DB::transaction(function () use ($reservation, $adminId) {
            $reservation->status = 'approved';
            $reservation->approved_by = $adminId;
            $reservation->approved_at = now();
            $reservation->rejection_reason = null;
            $reservation->save();
        });

        return $reservation->load(['venue', 'user', 'approver']);
    }

    /**
     * Reject a reservation
     */
    public function reject(Reservation $reservation, int $adminId, ?string $reason = null): Reservation
    {
        DB::transaction(function () use ($reservation, $adminId, $reason) {
            $reservation->status = 'rejected';
            $reservation->approved_by = $adminId;
            $reservation->rejection_reason = $reason;
            $reservation->save();
        });

        return $reservation->load(['venue', 'user', 'approver']);
    }

    /**
     * Get available time slots for a venue on a specific date
     */
    public function getAvailableTimeSlots(int $venueId, string $date): array
    {
        $approvedReservations = Reservation::where('venue_id', $venueId)
            ->where('date', $date)
            ->where('status', 'approved')
            ->orderBy('start_time')
            ->get();

        $slots = [];
        $start = Carbon::parse($date . ' 08:00:00');
        $end = Carbon::parse($date . ' 20:00:00');

        $current = $start->copy();
        while ($current < $end) {
            $slotEnd = $current->copy()->addHours(1);
            $isAvailable = true;

            foreach ($approvedReservations as $reservation) {
                $resStart = Carbon::parse($date . ' ' . $reservation->start_time);
                $resEnd = Carbon::parse($date . ' ' . $reservation->end_time);

                if ($current < $resEnd && $slotEnd > $resStart) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $current->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            $current->addHours(1);
        }

        return $slots;
    }
}
<?php

namespace App\Services;

use App\Models\Venue;
use App\Models\Reservation;
use Carbon\Carbon;

class VenueService
{
    /**
     * Update venue status and postpone upcoming reservations if needed
     */
    public function updateStatus(Venue $venue, string $status, ?string $unavailableUntil = null): array
    {
        $wasUnavailable = $venue->isUnavailable();
        $oldStatus = $venue->status;
        
        $postponedCount = 0;

        // If venue is being marked as unavailable (damaged or under construction)
        if (!$wasUnavailable && in_array($status, ['damaged', 'under_construction'])) {
            $venue->status = $status;
            $venue->unavailable_until = $unavailableUntil;
            $venue->save();
            $postponedCount = $this->postponeUpcomingReservations($venue, $unavailableUntil);
        }
        // If venue is being marked as available again
        elseif ($wasUnavailable && $status === 'available') {
            $venue->status = $status;
            $venue->unavailable_until = null;
            $venue->save();
        }
        // If status is being changed but both are unavailable or both are available
        else {
            $venue->status = $status;
            if ($status === 'available') {
                $venue->unavailable_until = null;
            } else {
                $venue->unavailable_until = $unavailableUntil;
                // If venue is already unavailable but unavailable_until date is being updated,
                // re-check and postpone reservations
                if (in_array($status, ['damaged', 'under_construction'])) {
                    $postponedCount = $this->postponeUpcomingReservations($venue, $unavailableUntil);
                }
            }
            $venue->save();
        }

        return [
            'venue' => $venue,
            'postponed_count' => $postponedCount,
        ];
    }

    /**
     * Postpone all upcoming approved reservations for a venue
     */
    protected function postponeUpcomingReservations(Venue $venue, ?string $unavailableUntil = null): int
    {
        $today = now()->toDateString();
        
        // Get all approved reservations that are on or after today
        $upcomingReservations = Reservation::where('venue_id', $venue->id)
            ->where('status', 'approved')
            ->where('date', '>=', $today)
            ->get();

        $postponedCount = 0;
        $unavailableUntilDate = $unavailableUntil ? Carbon::parse($unavailableUntil) : null;

        foreach ($upcomingReservations as $reservation) {
            // Only postpone if reservation date is before or on the unavailable_until date
            // Or if unavailable_until is not set, postpone all upcoming
            if (!$unavailableUntilDate || $reservation->date <= $unavailableUntilDate) {
                $reservation->status = 'postponed';
                $reservation->postponement_reason = sprintf(
                    'Venue is %s. %s',
                    $venue->status === 'damaged' ? 'damaged' : 'under construction',
                    $unavailableUntilDate 
                        ? "Expected to be available again on {$unavailableUntilDate->format('F d, Y')}."
                        : 'Please contact administrator for availability updates.'
                );
                $reservation->save();
                $postponedCount++;
            }
        }

        return $postponedCount;
    }
}

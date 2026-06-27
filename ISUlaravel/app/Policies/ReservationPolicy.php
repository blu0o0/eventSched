<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view reservations
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // Users can view their own reservations, admins and OSAS can view all
        return $user->isAdministrator() || $user->isOsas() || $reservation->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users (main_proponent, general_user, and administrators) can create reservations
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Admins can update any reservation
        if ($user->isAdministrator()) {
            return true;
        }
        // Users can only update their own pending reservations
        return $reservation->user_id === $user->id && $reservation->isPending();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Users can delete their own pending reservations, admins can delete any
        if ($user->isAdministrator()) {
            return true;
        }
        return $reservation->user_id === $user->id && $reservation->isPending();
    }

    /**
     * Determine whether the user can approve the reservation.
     */
    public function approve(User $user, Reservation $reservation): bool
    {
        return ($user->isAdministrator() || $user->isOsas()) && ($reservation->isPending() || $reservation->isPostponed());
    }

    /**
     * Determine whether the user can reject the reservation.
     */
    public function reject(User $user, Reservation $reservation): bool
    {
        return ($user->isAdministrator() || $user->isOsas()) && ($reservation->isPending() || $reservation->isPostponed());
    }
}

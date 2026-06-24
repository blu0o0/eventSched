<?php

namespace App\Policies;

use App\Models\EmergencyReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmergencyReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All users can view emergency reports
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmergencyReport $emergencyReport): bool
    {
        // Users can view their own reports, admins can view all
        return $user->isAdministrator() || $emergencyReport->reporter_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create emergency reports
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmergencyReport $emergencyReport): bool
    {
        // Only admins can update emergency reports
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmergencyReport $emergencyReport): bool
    {
        // Only admins can delete emergency reports
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmergencyReport $emergencyReport): bool
    {
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmergencyReport $emergencyReport): bool
    {
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can resolve the emergency report.
     */
    public function resolve(User $user, EmergencyReport $emergencyReport): bool
    {
        return $user->isAdministrator() && $emergencyReport->isOpen();
    }
}

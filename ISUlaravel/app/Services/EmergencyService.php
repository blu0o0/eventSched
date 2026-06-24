<?php

namespace App\Services;

use App\Models\EmergencyReport;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmergencyReportNotification;

class EmergencyService
{
    /**
     * Create a new emergency report
     */
    public function create(array $data, int $reporterId): EmergencyReport
    {
        $report = EmergencyReport::create([
            'type' => $data['type'],
            'description' => $data['description'],
            'reporter_id' => $reporterId,
            'status' => 'open',
        ]);

        // Notify administrators
        $this->notifyAdministrators($report);

        return $report->load('reporter');
    }

    /**
     * Resolve an emergency report
     */
    public function resolve(EmergencyReport $report, int $adminId): EmergencyReport
    {
        $report->status = 'closed';
        $report->resolved_by = $adminId;
        $report->resolved_at = now();
        $report->save();

        return $report->load(['reporter', 'resolver']);
    }

    /**
     * Notify all administrators about a new emergency report
     */
    protected function notifyAdministrators(EmergencyReport $report): void
    {
        $administrators = User::where('role', 'administrator')->get();

        foreach ($administrators as $admin) {
            // Send database notification
            $admin->notify(new EmergencyReportNotification($report));

            // Send email notification (if configured)
            // Mail::to($admin->email)->send(new EmergencyReportMail($report));
        }
    }
}


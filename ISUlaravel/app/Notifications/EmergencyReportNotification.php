<?php

namespace App\Notifications;

use App\Models\EmergencyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyReportNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public EmergencyReport $emergencyReport
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Emergency Report: ' . $this->emergencyReport->type)
            ->line('A new emergency report has been submitted.')
            ->line('Type: ' . $this->emergencyReport->type)
            ->line('Description: ' . $this->emergencyReport->description)
            ->line('Reported by: ' . $this->emergencyReport->reporter->name)
            ->action('View Report', url('/admin/emergency/' . $this->emergencyReport->id))
            ->line('Please review and take appropriate action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'emergency_report',
            'emergency_report_id' => $this->emergencyReport->id,
            'emergency_type' => $this->emergencyReport->type,
            'description' => $this->emergencyReport->description,
            'reporter_name' => $this->emergencyReport->reporter->name,
            'reporter_id' => $this->emergencyReport->reporter_id,
            'created_at' => $this->emergencyReport->created_at->toDateTimeString(),
        ];
    }
}

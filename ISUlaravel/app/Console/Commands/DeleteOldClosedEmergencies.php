<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmergencyReport;
use Illuminate\Support\Facades\Log;

class DeleteOldClosedEmergencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emergencies:delete-old-closed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete closed emergencies older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $cutoffDate = now()->subDays(30);
        
        $oldClosedEmergencies = EmergencyReport::where('status', 'closed')
            ->where('resolved_at', '<', $cutoffDate)
            ->get();

        $count = $oldClosedEmergencies->count();

        if ($count > 0) {
            foreach ($oldClosedEmergencies as $emergency) {
                $emergency->delete();
                $this->info("Deleted emergency #{$emergency->id} - {$emergency->type} (resolved on {$emergency->resolved_at->format('Y-m-d')})");
            }

            Log::info("Auto-deleted {$count} closed emergency report(s) older than 30 days.");
            $this->info("Successfully deleted {$count} closed emergency report(s) older than 30 days.");
        } else {
            $this->info('No closed emergencies older than 30 days found.');
        }
    }
}
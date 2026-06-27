<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAttempt;

class ClearLoginAttempts extends Command
{
    protected $signature = 'login-attempts:clear {email? : The email address to clear attempts for}';
    protected $description = 'Clear login attempts from the database';

    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $deleted = LoginAttempt::where('email', $email)->delete();
            $this->info("Cleared login attempts for email: {$email}");
            $this->info("Deleted {$deleted} records.");
        } else {
            $deleted = LoginAttempt::truncate();
            $this->info('Cleared all login attempts from the database.');
            $this->info("Deleted all records.");
        }
        
        return Command::SUCCESS;
    }
}
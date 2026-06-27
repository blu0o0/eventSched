<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email? : The email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a user an administrator by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? 'paltaobobby30@gmail.com';
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        $user->update(['role' => 'ADMINISTRATOR']);
        
        $this->info("Successfully made '{$user->name}' ({$email}) an ADMINISTRATOR.");
        return 0;
    }
}
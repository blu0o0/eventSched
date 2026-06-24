<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Administrator
        User::create([
            'name' => 'Admin',
            'email' => 'admin@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'administrator',
        ]);

        // Create OSAS
        User::create([
            'name' => 'OSAS',
            'email' => 'osas@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'osas',
        ]);

        // Create Main Proponent
        User::create([
            'name' => 'Main Proponent',
            'email' => 'proponent@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'main_proponent',
        ]);

        // Create General User
        User::create([
            'name' => 'General User',
            'email' => 'user@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'general_user',
        ]);
    }
}

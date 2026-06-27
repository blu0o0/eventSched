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
            'role' => 'ADMINISTRATOR',
        ]);

        // Create SSC Officer
        User::create([
            'name' => 'SSC Officer',
            'email' => 'ssc@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'SSC OFFICER',
        ]);

        // Create Student
        User::create([
            'name' => 'Student',
            'email' => 'student@isu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'STUDENT',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venues = [
            [
                'name' => 'Admin Building',
                'location' => 'Santiago Campus',
                'capacity' => 50,
                'description' => 'Admin Building',
                'map_coordinates' => '16.722248008284478, 121.537140965431',
                'status' => 'available',
                'unavailable_until' => null,
                'created_at' => '2025-12-12 15:11:09',
                'updated_at' => '2025-12-12 15:11:09',
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}


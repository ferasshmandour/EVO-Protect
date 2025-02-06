<?php

namespace Database\Seeders;

use App\Enums\FacilitySystemStatus;
use App\Models\FacilitySystem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 1,
            'status' => FacilitySystemStatus::off,
        ]);

        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 2,
            'status' => FacilitySystemStatus::off,
        ]);

        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 3,
            'status' => FacilitySystemStatus::off,
        ]);
    }
}

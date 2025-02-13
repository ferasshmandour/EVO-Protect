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
        // Fire system
        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 1,
            'status' => FacilitySystemStatus::off,
            'notification_status' => FacilitySystemStatus::off,
            'mac_address' => 'A8-5E-45-C3-F8-00',
        ]);

        // Energy Saving system
        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 2,
            'status' => FacilitySystemStatus::off,
            'notification_status' => FacilitySystemStatus::off,
            'mac_address' => 'A8-5E-45-C3-F8-00',
        ]);

        // Protection system
        FacilitySystem::create([
            'facility_id' => 1,
            'system_id' => 3,
            'status' => FacilitySystemStatus::off,
            'notification_status' => FacilitySystemStatus::off,
            'mac_address' => 'A8-5E-45-C3-F8-00',
        ]);
    }
}

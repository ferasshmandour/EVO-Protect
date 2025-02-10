<?php

namespace Database\Seeders;

use App\Enums\FaceStatus;
use App\Enums\FacilitySystemStatus;
use App\Models\SystemValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemValue::create([
            'facility_id' => 1,
            'system_id' => 1,
            'temperature' => '40',
            'smoke' => 'YES',
            'horn' => 'YES',
            'movement' => null,
            'face_status' => null,
        ]);

        SystemValue::create([
            'facility_id' => 1,
            'system_id' => 2,
            'temperature' => null,
            'smoke' => null,
            'horn' => null,
            'movement' => 'YES',
            'face_status' => null,
        ]);

        SystemValue::create([
            'facility_id' => 1,
            'system_id' => 3,
            'temperature' => null,
            'smoke' => null,
            'horn' => null,
            'movement' => null,
            'face_status' => FaceStatus::unknownFace,
        ]);
    }
}

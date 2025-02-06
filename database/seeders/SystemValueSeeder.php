<?php

namespace Database\Seeders;

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
            'spray' => 'NO',
            'status' => null,
            'movement' => 'YES',
        ]);
    }
}

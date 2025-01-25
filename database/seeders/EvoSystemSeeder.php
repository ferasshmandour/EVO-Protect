<?php

namespace Database\Seeders;

use App\Models\EvoSystem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvoSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EvoSystem::create([
            'name' => 'First system',
            'devices' => 'test test',
            'description' => 'test test',
        ]);

        EvoSystem::create([
            'name' => 'Second system',
            'devices' => 'test test',
            'description' => 'test test',
        ]);

        EvoSystem::create([
            'name' => 'Third system',
            'devices' => 'test test',
            'description' => 'test test',
        ]);
    }
}

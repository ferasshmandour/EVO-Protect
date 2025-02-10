<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facility::create([
            'name' => 'First Facility',
            'user_id' => 2,
            'area_id' => 1,
            'location_url' => 'https://www.google.com/maps/place/%D8%A7%D9%84%D8%AC%D8%B3%D8%B1+%D8%A7%D9%84%D8%A3%D8%A8%D9%8A%D8%B6%D8%8C+%D8%AF%D9%85%D8%B4%D9%82%D8%8C+%D8%B3%D9%88%D8%B1%D9%8A%D8%A7%E2%80%AD/@33.5243198,36.2924445,861m/data=!3m2!1e3!4b1!4m6!3m5!1s0x1518e7396153ea33:0x148403af67b050ee!8m2!3d33.524113!4d36.2895191!16s%2Fg%2F1tcx9kcr?entry=ttu&g_ep=EgoyMDI1MDIwMy4wIKXMDSoASAFQAw%3D%3D',
            'code' => Str::random(5),
        ]);
    }
}

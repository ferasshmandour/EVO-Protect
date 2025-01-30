<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => UserRole::superAdmin,
        ]);

        Role::create([
            'name' => USerRole::admin,
        ]);

        Role::create([
            'name' => UserRole::user,
        ]);
    }
}

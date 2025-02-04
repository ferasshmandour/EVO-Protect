<?php

namespace Database\Seeders;

use App\Enums\AddedBy;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'phone' => '0123456789',
            'password' => bcrypt('123456'),
            'role_id' => 1,
            'status' => UserStatus::active,
            'added_by' => AddedBy::dashboard,
            'is_client' => false,
        ]);
    }
}

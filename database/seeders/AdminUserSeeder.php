<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@tarraya.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        $coordinator = User::updateOrCreate(
            ['email' => 'coordinator@tarraya.test'],
            [
                'name' => 'Academic Coordinator',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $coordinator->assignRole('academic_coordinator');
    }
}

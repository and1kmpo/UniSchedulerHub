<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@sas.com',
            'password' => bcrypt('password'),
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
    }
}

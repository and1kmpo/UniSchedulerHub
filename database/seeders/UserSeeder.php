<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@sas.com',
            'password' => Hash::make('admin')
        ]);
        $admin->assignRole('admin');

        $student = User::create([
            'name' => 'student',
            'email' => 'student@sas.com',
            'password' => Hash::make('student')
        ]);
        $student->assignRole('student');

        $professor = User::create([
            'name' => 'professor',
            'email' => 'professor@sas.com',
            'password' => Hash::make('professor')
        ]);
        $professor->assignRole('professor');
    }
}

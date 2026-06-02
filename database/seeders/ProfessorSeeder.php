<?php

namespace Database\Seeders;

use App\Models\Professor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfessorSeeder extends Seeder
{
    public static $professors;

    public function run(): void
    {
        self::$professors = collect();

        // Creamos 5 profesores
        for ($i = 0; $i < 5; $i++) {
            $professorData = Professor::factory()->make(); // aún no se guarda

            $user = User::updateOrCreate(
                ['email' => $professorData->document . '@sas.com'],
                [
                    'name' => fake()->name(),
                    'password' => Hash::make('password'),
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('professor');

            // Creamos el profesor con el user_id ya correcto
            $professor = Professor::updateOrCreate(
                ['document' => $professorData->document],
                [
                    'user_id' => $user->id,
                    'phone' => $professorData->phone,
                    'address' => $professorData->address,
                    'city' => $professorData->city,
                ]
            );

            self::$professors->push($professor);
        }
    }
}

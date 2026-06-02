<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public static $student;

    public function run(): void
    {
        self::$student = collect();

        // Creamos 5 estudiantes
        for ($i = 0; $i < 5; $i++) {
            $studentData = Student::factory()->make(); // aún no se guarda

            $user = User::updateOrCreate(
                ['email' => $studentData->document . '@sas.com'],
                [
                    'name' => fake()->name(),
                    'password' => Hash::make('password'),
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('student');

            // Creamos el estudiante con el user_id ya correcto
            $student = Student::updateOrCreate(
                ['document' => $studentData->document],
                [
                    'user_id' => $user->id,
                    'phone' => $studentData->phone,
                    'address' => $studentData->address,
                    'city' => $studentData->city,
                    'semester' => $studentData->semester,
                    'program_id' => $studentData->program_id,
                    'curriculum_id' => $studentData->curriculum_id,
                    'academic_status' => $studentData->academic_status,
                ]
            );

            self::$student->push($student);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public static $student;

    public function run(): void
    {
        self::$student = collect();

        // Creamos 5 estudiantes
        for ($i = 0; $i < 5; $i++) {
            $studentData = Student::factory()->make(); // aún no se guarda

            $user = User::create([
                'name' => fake()->name(),
                'email' => $studentData->document . '@sas.com',
                'password' => bcrypt('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('student');

            // Creamos el estudiante con el user_id ya correcto
            $student = Student::create([
                'user_id' => $user->id,
                'document' => $studentData->document,
                'phone' => $studentData->phone,
                'address' => $studentData->address,
                'city' => $studentData->city,
                'semester' => $studentData->semester,
                'program_id' => $studentData->program_id,
            ]);

            self::$student->push($student);
        }
    }
}

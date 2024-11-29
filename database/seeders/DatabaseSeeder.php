<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Professor;
use App\Models\Subject;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RolSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Ejecutar RolSeeder para roles y permisos
        $this->call(RolSeeder::class);

        // Crear un usuario de tipo admin y asignarle el rol
        $adminUser = User::factory()->create(['email' => 'admin@sas.com']);
        $adminUser->assignRole('admin');

        // Crear programas
        $programs = Program::factory(5)->create();

        // Crear estudiantes y sus usuarios
        $students = Student::factory(10)->make()->each(function ($student) use ($programs) {
            // Crear el usuario correspondiente para el estudiante
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => $student->document . '@sas.com',
            ]);
            $user->assignRole('student');

            // Asocia el user_id al estudiante y luego guarda
            $student->user_id = $user->id;
            $student->program_id = $programs->random()->id;
            $student->save();
        });

        // Crear profesores y sus usuarios
        $professors = Professor::factory(5)->make()->each(function ($professor) {
            // Crear el usuario correspondiente para el profesor
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => $professor->document . '@sas.com',
            ]);
            $user->assignRole('professor');

            // Asocia el user_id al profesor y luego guarda
            $professor->user_id = $user->id;
            $professor->save();
        });

        // Crear asignaturas
        $subjects = Subject::factory(20)->create();

        // Asignar asignaturas aleatoriamente a estudiantes y profesores
        foreach ($subjects as $subject) {
            $randomProfessors = $professors->random(rand(1, $professors->count()));
            foreach ($randomProfessors as $professor) {
                $professor->subjects()->syncWithoutDetaching([$subject->id]);
            }

            $randomStudents = $students->random(rand(1, $students->count()));
            foreach ($randomStudents as $student) {
                $professor = $randomProfessors->random();
                $student->subjects()->syncWithoutDetaching([$subject->id => ['professor_id' => $professor->id]]);
            }
        }
    }
}

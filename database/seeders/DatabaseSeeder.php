<?php

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Professor;
use App\Models\Subject;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Crear programas
        $programs = Program::factory(5)->create();

        // Crear estudiantes y asignar programas
        $students = Student::factory(10)->create(['program_id' => $programs->random()]);

        // Crear profesores
        $professors = Professor::factory(5)->create();

        // Crear asignaturas
        $subjects = Subject::factory(20)->create();

        // Asignar aleatoriamente asignaturas a estudiantes y profesores
        foreach ($students as $student) {
            if ($professors->count() > 0) {
                $subject = $subjects->random();
                $professor = $professors->random();

                // Asignar asignatura a profesor
                $professor->subjects()->attach($subject);

                // Asignar asignatura a estudiante
                $student->subjects()->attach($subject, ['professor_id' => $professor->id]);
            }
        }
        $this->call([RolSeeder::class, UserSeeder::class]);
    }
}

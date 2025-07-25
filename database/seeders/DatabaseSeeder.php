<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Professor;
use App\Models\Subject;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Database\Seeders\GradeStatusSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolSeeder::class,
            GradeStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class
        ]);

        // Crear un usuario de tipo admin y asignarle el rol
        $adminUser = User::factory()->create(['email' => 'admin@sas.com']);
        $adminUser->assignRole('admin');

        // Array de programas predefinidos
        $programs = [
            ['name' => 'Computer Science', 'description' => 'Study of computers and computational systems.'],
            ['name' => 'Mechanical Engineering', 'description' => 'Design and manufacture of machines and systems.'],
            ['name' => 'Business Administration', 'description' => 'Management of business operations.'],
            ['name' => 'Psychology', 'description' => 'Study of the human mind and behavior.'],
            ['name' => 'Biology', 'description' => 'Study of living organisms and their interactions.'],
            ['name' => 'Mathematics', 'description' => 'Abstract science of numbers, quantities, and shapes.'],
            ['name' => 'Civil Engineering', 'description' => 'Design and construction of infrastructure projects.'],
            ['name' => 'Physics', 'description' => 'Study of matter, energy, and the laws of nature.'],
            ['name' => 'Law', 'description' => 'Study of legal systems and the law.'],
            ['name' => 'Economics', 'description' => 'Study of production, consumption, and transfer of wealth.'],
        ];

        // Insertar los programas directamente en la base de datos
        foreach ($programs as $program) {
            Program::create($program);
        }

        // Crear estudiantes y sus usuarios
        $students = Student::factory(10)->create()->each(function ($student) {
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => $student->document . '@sas.com',
            ]);
            $user->assignRole('student');

            $student->user_id = $user->id;
            $student->program_id = Program::InRandomOrder()->first()->id; // Asignar un programa aleatorio de los ya creados
            $student->save();
        });

        // Crear profesores y sus usuarios
        $professors = Professor::factory(5)->create()->each(function ($professor) {
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => $professor->document . '@sas.com',
            ]);
            $user->assignRole('professor');

            $professor->user_id = $user->id;
            $professor->save();
        });

        // Crear asignaturas y asignarlas a programas
        $subjects = Subject::factory(20)->create();
        foreach ($subjects as $subject) {
            // Asignar subject a 1 o 2 programas con semestre aleatorio
            $randomPrograms = Program::inRandomOrder()->take(rand(1, 2))->get();
            foreach ($randomPrograms as $program) {
                $subject->programs()->attach($program->id, ['semester' => rand(1, 10)]);
            }
        }

        // Asignar asignaturas a profesores y estudiantes con restricciones
        foreach ($subjects as $subject) {
            // Asignar a un máximo de 10 profesores
            $randomProfessors = $professors->random(rand(1, min(10, $professors->count())));
            foreach ($randomProfessors as $professor) {
                if ($professor->subjects()->count() < 10) {
                    $professor->subjects()->syncWithoutDetaching([$subject->id]);
                }
            }

            // Asignar a estudiantes con un máximo de 20 créditos
            $randomStudents = $students->random(rand(1, $students->count()));
            foreach ($randomStudents as $student) {
                $currentCredits = $student->subjects()->sum('credits');
                if ($currentCredits + $subject->credits <= 20) {
                    $professor = $randomProfessors->random();
                    $student->subjects()->syncWithoutDetaching([
                        $subject->id => ['professor_id' => $professor->id],
                    ]);
                }
            }
        }

        // Crear 10 grupos de clase
        $classGroups = ClassGroup::factory(10)->create();

        // Para cada grupo, crear entre 1 y 3 horarios (sin traslapes automáticos)
        $classGroups->each(function ($group) {
            ClassSchedule::factory(rand(1, 3))->create([
                'class_group_id' => $group->id
            ]);
        });
    }
}

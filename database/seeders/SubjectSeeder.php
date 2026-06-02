<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Program;
use App\Models\Professor;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::factory(20)->create();

        foreach ($subjects as $subject) {
            $randomPrograms = Program::inRandomOrder()->take(rand(1, 2))->get();
            foreach ($randomPrograms as $program) {
                $subject->programs()->syncWithoutDetaching([
                    $program->id => ['semester' => rand(1, 10)],
                ]);
            }

            $professors = Professor::inRandomOrder()->take(rand(1, 3))->get();
            foreach ($professors as $professor) {
                $professor->subjects()->syncWithoutDetaching([$subject->id]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Subject;

class SubjectAssignmentSeeder extends Seeder
{
    public function run()
    {
        $subjects = Subject::all();
        $professors = Professor::all();

        if ($subjects->isEmpty() || $professors->isEmpty()) {
            return;
        }

        foreach ($subjects as $subject) {
            $randomProfessors = $professors->random(rand(1, min(10, $professors->count())));

            foreach ($randomProfessors as $professor) {
                if ($professor->subjects()->count() < 10) {
                    $professor->subjects()->syncWithoutDetaching([$subject->id]);
                }
            }
        }
    }
}

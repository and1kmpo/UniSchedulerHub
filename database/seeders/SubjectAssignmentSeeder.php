<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Student;
use App\Models\Subject;

class SubjectAssignmentSeeder extends Seeder
{
    public function run()
    {
        $subjects = Subject::all();
        $professors = Professor::all();
        $students = Student::all();

        foreach ($subjects as $subject) {
            $randomProfessors = $professors->random(rand(1, min(10, $professors->count())));
            foreach ($randomProfessors as $professor) {
                if ($professor->subjects()->count() < 10) {
                    $professor->subjects()->syncWithoutDetaching([$subject->id]);
                }
            }

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
    }
}

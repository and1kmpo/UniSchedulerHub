<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Professor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSubjectGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $period = AcademicPeriod::where('is_active', true)->first();
        if (!$period) return;

        $program = Program::where('name', 'Civil Engineering')->first();
        if (!$program) return;

        $professor = Professor::first(); // Usa el primero que exista
        if (!$professor) return;

        foreach ($program->subjects as $subject) {
            // Crea un grupo por asignatura
            $group = ClassGroup::create([
                'subject_id' => $subject->id,
                'professor_id' => $professor->id,
                'academic_period_id' => $period->id,
                'code' => 'A' . strtoupper(Str::random(3)),
                'name' => 'Grupo A',
                'capacity' => 30,
            ]);

            // Añade 2 horarios aleatorios
            foreach ($this->generateSchedules() as $sched) {
                ClassSchedule::create([
                    'class_group_id' => $group->id,
                    'day' => $sched['day'],
                    'start_time' => $sched['start_time'],
                    'end_time' => $sched['end_time'],
                ]);
            }
        }
    }

    private function generateSchedules(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        shuffle($days);
        $startHours = range(7, 15); // Entre 7am y 3pm
        shuffle($startHours);

        return [
            [
                'day' => $days[0],
                'start_time' => sprintf('%02d:00:00', $startHours[0]),
                'end_time' => sprintf('%02d:00:00', $startHours[0] + 2),
            ],
            [
                'day' => $days[1],
                'start_time' => sprintf('%02d:00:00', $startHours[1]),
                'end_time' => sprintf('%02d:00:00', $startHours[1] + 2),
            ],
        ];
    }
}

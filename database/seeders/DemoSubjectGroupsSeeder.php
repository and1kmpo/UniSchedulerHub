<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Professor;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
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

        $professor = Professor::first(); // Usa el primero disponible
        if (!$professor) return;

        $student = Student::first(); // Para simular inscripción
        $statusId = SubjectEnrollmentStatus::where('code', 'enrolled')->value('id');

        foreach ($program->subjects as $subject) {
            // Evita duplicados en el mismo período
            if ($subject->classGroups()->where('academic_period_id', $period->id)->exists()) {
                continue;
            }

            // Intenta obtener el semestre desde el pivot (si lo tienes)
            $semester = $subject->pivot->semester ?? rand(1, 8);

            $group = ClassGroup::create([
                'subject_id' => $subject->id,
                'professor_id' => $professor->id,
                'academic_period_id' => $period->id,
                'code' => 'A' . strtoupper(Str::random(3)),
                'group_code' => 'A',
                'name' => 'Grupo A',
                'semester' => $semester,
                'modality' => 'Presential',
                'shift' => 'Day',
                'capacity' => 30,
            ]);

            // Agrega horarios al grupo
            foreach ($this->generateSchedules() as $sched) {
                ClassSchedule::create([
                    'class_group_id' => $group->id,
                    'day' => $sched['day'],
                    'start_time' => $sched['start_time'],
                    'end_time' => $sched['end_time'],
                ]);
            }

            // Opcional: crear una inscripción ficticia del primer estudiante
            if ($student && $statusId) {
                SubjectEnrollment::firstOrCreate([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_period_id' => $period->id,
                ], [
                    'class_group_id' => $group->id,
                    'status_id' => $statusId,
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

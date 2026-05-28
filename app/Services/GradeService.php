<?php

namespace App\Services;

use App\Models\SubjectEnrollment;
use App\Models\Grade;
use App\Models\GradeStatus;
use App\Domain\Grades\GradeEvaluator;
use App\Domain\Academic\AcademicPeriodGuard;
use DomainException;


class GradeService
{
    public function update(
        SubjectEnrollment $enrollment,
        array $gradeData,
        int $professorId
    ): Grade {

        $enrollment->loadMissing(['academicPeriod', 'grade']);

        $period = $enrollment->academicPeriod;

        //Bloqueo calificaciones desde el guard
        // 1️⃣ Freeze check (PRIMERO)
        AcademicPeriodGuard::ensurePeriodNotFrozen($period);

        // 2️⃣ Regla académica
        AcademicPeriodGuard::ensureGradesEditable($period);

        // Evaluación académica
        $evaluation = GradeEvaluator::evaluate($gradeData);

        $state = $evaluation->statusCode
            ? GradeStatus::where('code', $evaluation->statusCode)->first()
            : null;

        $grade = Grade::updateOrCreate(
            ['subject_enrollment_id' => $enrollment->id],
            [
                'professor_id' => $professorId,
                'partial_1' => $gradeData['first_exam'] ?? null,
                'partial_2' => $gradeData['second_exam'] ?? null,
                'partial_3' => $gradeData['third_exam'] ?? null,
                'activities' => $gradeData['activities'] ?? null,
                'attendance' => $gradeData['attendance'] ?? null,
                'final_grade' => $evaluation->finalGrade,
                'grade_status_id' => optional($state)->id,
            ]
        );

        return $grade->load('state');
    }

    public function delete(SubjectEnrollment $enrollment): void
    {
        $enrollment->loadMissing(['academicPeriod', 'grade']);

        $period = $enrollment->academicPeriod;

        // 1️⃣ Freeze
        AcademicPeriodGuard::ensurePeriodNotFrozen($period);

        // 2️⃣ Regla académica
        AcademicPeriodGuard::ensureGradesEditable($period);

        if (! $enrollment->grade) {
            throw new DomainException('BLOCK_GRADE_NOT_FOUND');
        }

        $enrollment->grade->delete();
    }
}

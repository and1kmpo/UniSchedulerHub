<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Domain\Grades\GradeEvaluator;
use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\GradeStatus;
use App\Models\SubjectEnrollment;
use DomainException;

class GradeService
{
    public function update(
        SubjectEnrollment $enrollment,
        array $gradeData,
        ?int $professorId
    ): Grade {
        $enrollment->loadMissing(['academicPeriod', 'grade', 'classGroup']);

        $period = $enrollment->academicPeriod;

        AcademicPeriodGuard::ensurePeriodNotFrozen($period);
        AcademicPeriodGuard::ensureGradesEditable($period);

        if (! $enrollment->canEditGrades()) {
            throw new DomainException('BLOCK_ENROLLMENT_DOES_NOT_ALLOW_GRADES');
        }

        if (in_array($enrollment->classGroup?->status, [
            ClassGroup::STATUS_CANCELLED,
            ClassGroup::STATUS_CLOSED,
        ], true)) {
            throw new DomainException('BLOCK_GROUP_DOES_NOT_ALLOW_GRADES');
        }

        if (! $professorId) {
            throw new DomainException('BLOCK_NO_PROFESSOR_ASSIGNED');
        }

        $evaluation = GradeEvaluator::evaluate($gradeData);

        $state = $evaluation->statusCode
            ? GradeStatus::where('code', $evaluation->statusCode)->first()
            : null;

        $grade = Grade::firstOrNew([
            'subject_enrollment_id' => $enrollment->id,
        ]);

        $before = $grade->exists
            ? $grade->only(['partial_1', 'partial_2', 'partial_3', 'activities', 'attendance', 'final_grade', 'grade_status_id'])
            : null;

        if (! $grade->exists) {
            $grade->created_by = auth()->id();
        }

        $grade->fill([
            'professor_id' => $professorId,
            'partial_1' => $gradeData['first_exam'] ?? null,
            'partial_2' => $gradeData['second_exam'] ?? null,
            'partial_3' => $gradeData['third_exam'] ?? null,
            'activities' => $gradeData['activities'] ?? null,
            'attendance' => $gradeData['attendance'] ?? null,
            'final_grade' => $evaluation->finalGrade,
            'grade_status_id' => optional($state)->id,
            'updated_by' => auth()->id(),
        ]);

        $grade->save();

        app(AcademicAuditService::class)->record(
            $before ? 'grade.updated' : 'grade.created',
            $grade,
            [
                'subject_enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'subject_id' => $enrollment->subject_id,
                'class_group_id' => $enrollment->class_group_id,
                'academic_period_id' => $enrollment->academic_period_id,
                'before' => $before,
                'after' => $grade->only(['partial_1', 'partial_2', 'partial_3', 'activities', 'attendance', 'final_grade', 'grade_status_id']),
            ],
            $before ? 'Grade updated' : 'Grade created'
        );

        return $grade->load('state');
    }

    public function delete(SubjectEnrollment $enrollment): void
    {
        $enrollment->loadMissing(['academicPeriod', 'grade']);

        $period = $enrollment->academicPeriod;

        AcademicPeriodGuard::ensurePeriodNotFrozen($period);
        AcademicPeriodGuard::ensureGradesEditable($period);

        if (! $enrollment->grade) {
            throw new DomainException('BLOCK_GRADE_NOT_FOUND');
        }

        $enrollment->grade->delete();
    }
}

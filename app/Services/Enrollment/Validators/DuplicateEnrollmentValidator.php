<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class DuplicateEnrollmentValidator
{
    public function __construct(
        protected ?int $ignoreEnrollmentId = null,
    ) {}

    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $exists = $student->subjectEnrollments()
            ->where('subject_id', $group->subject_id)
            ->where('academic_period_id', $group->academic_period_id)
            ->when(
                $this->ignoreEnrollmentId,
                fn($query) => $query->where('id', '!=', $this->ignoreEnrollmentId)
            )
            ->whereHas(
                'status',
                fn($query) => $query->whereIn(
                    'code',
                    config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled'])
                )
            )
            ->exists();

        if ($exists) {

            $result->addError(
                'Student is already enrolled in this subject for the selected academic period.'
            );
        }
    }
}

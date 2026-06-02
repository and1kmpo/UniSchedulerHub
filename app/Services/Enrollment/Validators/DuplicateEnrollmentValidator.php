<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class DuplicateEnrollmentValidator
{
    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $exists = $student->subjectEnrollments()
            ->where('subject_id', $group->subject_id)
            ->where('academic_period_id', $group->academic_period_id)
            ->exists();

        if ($exists) {

            $result->addError(
                'Student is already enrolled in this subject for the selected academic period.'
            );
        }
    }
}

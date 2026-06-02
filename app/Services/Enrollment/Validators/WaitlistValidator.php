<?php

namespace App\Services\Enrollment\Validators;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class WaitlistValidator
{
    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {
        if (($result->meta['available_slots'] ?? 0) > 0) {
            $result->meta['waitlist'] = false;

            return;
        }

        $result->meta['waitlist'] = true;

        if ($result->allowed) {
            $result->addWarning('This group is full. The student can only be considered for waitlist workflows.');
        }
    }
}

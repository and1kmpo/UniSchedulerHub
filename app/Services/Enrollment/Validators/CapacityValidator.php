<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class CapacityValidator
{
    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $current = $group->subjectEnrollments()
            ->whereHas(
                'status',
                fn($query) => $query->whereIn(
                    'code',
                    config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled'])
                )
            )
            ->count();

        $result->meta['available_slots'] = max(0, (int) $group->capacity - $current);

        if ($current >= $group->capacity) {

            $result->addError(
                'This class group has reached maximum capacity.'
            );
        }
    }
}

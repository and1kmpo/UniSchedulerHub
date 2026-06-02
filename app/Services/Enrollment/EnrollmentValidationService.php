<?php

namespace App\Services\Enrollment;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;
use App\Services\Enrollment\Validators\CapacityValidator;
use App\Services\Enrollment\Validators\DuplicateEnrollmentValidator;
use App\Services\Enrollment\Validators\ScheduleConflictValidator;
use App\Services\Enrollment\Validators\AcademicLoadValidator;
use App\Services\Enrollment\Validators\WaitlistValidator;
use App\Services\Enrollment\Recommendations\EnrollmentRecommendationService;

class EnrollmentValidationService
{
    public function __construct(
        protected EnrollmentRecommendationService $recommendations,
    ) {}

    public function validate(
        Student $student,
        ClassGroup $group,
        ?SubjectEnrollment $ignoreEnrollment = null
    ): EnrollmentValidationResult {

        $group->loadMissing([
            'subject',
            'schedules',
            'subjectEnrollments.status',
        ]);

        $result = new EnrollmentValidationResult();

        if (! $group->isPublished()) {
            $result->addError('This class group is not published and cannot receive enrollments.');
        }

        $validators = [
            new CapacityValidator(),
            new DuplicateEnrollmentValidator($ignoreEnrollment?->id),
            new ScheduleConflictValidator($ignoreEnrollment?->class_group_id),
            new AcademicLoadValidator($ignoreEnrollment?->id),
            new WaitlistValidator(),
        ];

        foreach ($validators as $validator) {

            $validator->validate(
                $student,
                $group,
                $result
            );
        }

        foreach ($this->recommendations->generate($student, $group, $result) as $recommendation) {
            $result->addRecommendation($recommendation);
        }

        return $result;
    }
}

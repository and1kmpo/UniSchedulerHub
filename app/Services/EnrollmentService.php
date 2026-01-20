<?php

namespace App\Services;

use App\Models\SubjectEnrollment;
use App\Models\ClassGroup;
use App\Models\Student;

class EnrollmentService
{
    public function canEnroll(Student $student, ClassGroup $group): array
    {
        $group->loadMissing(['schedules', 'subject', 'academicPeriod']);

        /*
    |--------------------------------------------------------------------------
    | 1. Academic period validation (HIGHEST PRIORITY)
    |--------------------------------------------------------------------------
    */
        if (!$group->academicPeriod?->is_active) {
            return $this->block(
                'BLOCK_PERIOD_CLOSED',
                'Enrollment period is closed.',
                'error'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | 2. Curriculum  validation
    |--------------------------------------------------------------------------
    */
        $curriculum = $student->curriculum;

        if (!$curriculum) {
            return $this->block(
                'BLOCK_NO_CURRICULUM',
                'Student does not have an assigned curriculum.',
                'error'
            );
        }

        $subjectInCurriculum = $curriculum->subjects()
            ->where('subjects.id', $group->subject_id)
            ->exists();

        if (!$subjectInCurriculum) {
            return $this->block(
                'BLOCK_OUT_OF_CURRICULUM',
                'This subject does not belong to the student curriculum.',
                'error'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | 3. Student academic status (hard blocks)
    |--------------------------------------------------------------------------
    */
        switch ($student->academic_status) {
            case 'suspended':
                return $this->block(
                    'BLOCK_STATUS',
                    'Student is suspended and cannot enroll.',
                    'error'
                );

            case 'graduated':
                return $this->block(
                    'BLOCK_STATUS',
                    'Graduated students cannot enroll in new subjects.',
                    'error'
                );

            case 'withdrawn':
                return $this->block(
                    'BLOCK_STATUS',
                    'Withdrawn students cannot enroll.',
                    'error'
                );

            case 'active':
            case 'probation':
                break;
            default:
                return $this->block(
                    'BLOCK_INVALID_STATUS',
                    'Invalid academic status.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | 4. Already enrolled in subject
    |--------------------------------------------------------------------------
    */
        $alreadyEnrolled = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $group->subject_id)
            ->where('academic_period_id', $group->academic_period_id)
            ->exists();

        if ($alreadyEnrolled) {
            return $this->block(
                'BLOCK_ALREADY_ENROLLED',
                'Student is already enrolled in this subject for the current academic period.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | 5. Group capacity
    |--------------------------------------------------------------------------
    */
        $currentEnrollments = SubjectEnrollment::where('class_group_id', $group->id)->count();

        if ($group->capacity !== null && $currentEnrollments >= $group->capacity) {
            return $this->block(
                'BLOCK_CAPACITY',
                'This class group has reached its maximum capacity.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | 6. Schedule conflicts
    |--------------------------------------------------------------------------
    */
        $studentEnrollments = SubjectEnrollment::with('classGroup.schedules')
            ->where('student_id', $student->id)
            ->where('academic_period_id', $group->academic_period_id)
            ->get();

        foreach ($studentEnrollments as $enrollment) {
            foreach ($enrollment->classGroup->schedules as $existing) {
                foreach ($group->schedules as $incoming) {
                    if (
                        $existing->day === $incoming->day &&
                        $this->schedulesOverlap($existing, $incoming)
                    ) {
                        return $this->block(
                            'BLOCK_SCHEDULE_CONFLICT',
                            'Schedule conflict with another enrolled class group.'
                        );
                    }
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 7. Probation credit limit (SOFT academic rule)
    |--------------------------------------------------------------------------
    */
        if ($student->academic_status === 'probation') {

            $maxCredits = config('enrollment.probation_max_credits');

            $currentCredits = SubjectEnrollment::with('subject')
                ->where('student_id', $student->id)
                ->where('academic_period_id', $group->academic_period_id)
                ->get()
                ->sum(fn($e) => $e->subject->credits ?? 0);

            $newCredits = $group->subject->credits ?? 0;

            if (($currentCredits + $newCredits) > $maxCredits) {
                return $this->block(
                    'BLOCK_PROBATION_CREDITS',
                    "Students on academic probation can enroll in a maximum of {$maxCredits} credits.",
                    'warning',
                    [
                        'current_credits' => $currentCredits,
                        'attempted_credits' => $newCredits,
                        'max_credits' => $maxCredits,
                    ]
                );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | ✅ Allowed
    |--------------------------------------------------------------------------
    */
        return $this->allow();
    }



    private function schedulesOverlap($a, $b): bool
    {
        return $a->start_time < $b->end_time
            && $a->end_time > $b->start_time;
    }

    private function block(string $code, string $message, string $severity = 'error', array $meta = []): array
    {
        return [
            'allowed' => false,
            'code' => $code,
            'severity' => $severity,
            'message' => $message,
            'meta' => $meta,
        ];
    }

    private function allow(): array
    {
        return [
            'allowed' => true,
            'code' => 'ALLOW_ENROLLMENT',
            'severity' => 'success',
            'message' => 'Enrollment allowed.',
        ];
    }
}

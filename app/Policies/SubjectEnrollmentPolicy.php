<?php

namespace App\Policies;

use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Models\User;
use App\Services\EnrollmentService;

class SubjectEnrollmentPolicy
{
    public function enroll(User $user, ClassGroup $group): bool
    {
        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            return true;
        }

        if ($user->hasRole('student')) {
            return (bool) $user->student;
        }

        return false;
    }

    public function unenroll(User $user, SubjectEnrollment $enrollment): bool
    {
        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $user->student?->id === $enrollment->student_id;
        }

        return false;
    }

    public function view(User $user, SubjectEnrollment $enrollment): bool
    {
        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $user->student?->id === $enrollment->student_id;
        }

        if ($user->hasRole('professor')) {
            return $enrollment->classGroup?->professor_id === $user->id;
        }

        return false;
    }

    public function grade(User $user, SubjectEnrollment $enrollment): bool
    {
        return $user->hasRole('professor')
            && $enrollment->classGroup?->professor_id === $user->id;
    }

    public function finalize(User $user, SubjectEnrollment $enrollment): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator'])
            || (
                $user->hasRole('professor')
                && $enrollment->classGroup?->professor_id === $user->id
            );
    }

    public function changeStatus(User $user, SubjectEnrollment $enrollment, string $toStatus): bool
    {
        return match ($toStatus) {
            'enrolled', 'cancelled', 'withdrawn' =>
                $user->hasAnyRole(['admin', 'academic_coordinator']),

            'approved', 'failed' =>
                $user->hasRole('professor')
                && $enrollment->classGroup?->professor_id === $user->id,

            default => false,
        };
    }

    public function canAttemptEnroll(User $user, ClassGroup $group, EnrollmentService $service): bool
    {
        if (! $user->hasRole('student') || ! $user->student) {
            return false;
        }

        $result = $service->canEnroll($user->student, $group);

        return $result['can_enroll'];
    }
}

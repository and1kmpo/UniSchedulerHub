<?php

namespace App\Policies;

use App\Models\AcademicPeriod;
use App\Models\User;

class AcademicPeriodPolicy
{
    public function openEnrollment(User $user, AcademicPeriod $period): bool
    {
        return $user->hasAnyRole([
            'admin',
            'academic_coordinator',
        ]);
    }

    public function closeEnrollment(User $user, AcademicPeriod $period): bool
    {
        return $user->hasAnyRole([
            'admin',
            'academic_coordinator',
        ]);
    }

    public function startPeriod(User $user, AcademicPeriod $period): bool
    {
        return $user->hasAnyRole([
            'admin',
            'academic_coordinator',
        ]);
    }

    public function closeAcademically(User $user, AcademicPeriod $period): bool
    {
        return $user->hasAnyRole([
            'admin',
            'academic_coordinator',
        ]);
    }

    public function archive(User $user, AcademicPeriod $period): bool
    {
        return $user->hasRole('admin');
    }
}

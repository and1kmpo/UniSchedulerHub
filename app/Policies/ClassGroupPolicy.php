<?php

namespace App\Policies;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassGroupPolicy
{

    public function manageGrades(User $user, ClassGroup $group): bool
    {
        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            return true;
        }

        return $user->professor && $group->professor_id === $user->id;
    }

    public function editGrades(User $user, ClassGroup $group): bool
    {
        if (! $group->academicPeriod?->canEditGrades()) {
            return false;
        }

        if (in_array($group->status, [
            ClassGroup::STATUS_CANCELLED,
            ClassGroup::STATUS_CLOSED,
        ], true)) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $group->professor_id;
    }
}

<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function update(User $user, Grade $grade): bool
    {
        if (! $user->hasRole('professor')) {
            return false;
        }

        $group = $grade->subjectEnrollment?->classGroup;

        if (! $group) {
            return false;
        }

        return $group->professor_id === $user->professor?->id;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }
}

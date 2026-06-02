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

        $group = $grade->enrollment?->classGroup;

        if (! $group) {
            return false;
        }

        return $group->professor_id === $user->id;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }
}

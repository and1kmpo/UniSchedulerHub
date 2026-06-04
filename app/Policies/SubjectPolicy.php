<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    /**
     * View any subjects
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * View single subject
     */
    public function view(User $user, Subject $subject): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * Create subjects
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * Update subjects
     */
    public function update(User $user, Subject $subject): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * Delete subjects
     */
    public function delete(User $user, Subject $subject): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * Restore subjects
     */
    public function restore(User $user, Subject $subject): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }

    /**
     * Force delete
     */
    public function forceDelete(User $user, Subject $subject): bool
    {
        return $user->hasAnyRole(['admin', 'academic_coordinator']);
    }
}

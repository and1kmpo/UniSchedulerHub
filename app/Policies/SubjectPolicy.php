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
        return $user->hasAnyRole(['admin', 'professor']);
    }

    /**
     * View single subject
     */
    public function view(User $user, Subject $subject): bool
    {
        // Admin puede todo
        if ($user->hasRole('admin')) {
            return true;
        }

        // Profesor solo materias asignadas
        if ($user->hasRole('professor')) {
            $professor = $user->professor;

            return $professor
                && $professor->subjects()
                ->where('subjects.id', $subject->id)
                ->exists();
        }

        return false;
    }

    /**
     * Create subjects
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Update subjects
     */
    public function update(User $user, Subject $subject): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Delete subjects
     */
    public function delete(User $user, Subject $subject): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Restore subjects
     */
    public function restore(User $user, Subject $subject): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Force delete
     */
    public function forceDelete(User $user, Subject $subject): bool
    {
        return $user->hasRole('admin');
    }
}

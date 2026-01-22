<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Grade $grade): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Grade $grade): bool
    {
        $enrollment = $grade->enrollment;
        $period = $enrollment->academicPeriod;

        // 1️⃣ Admin siempre puede
        if ($user->hasRole('admin')) {
            return true;
        }

        // 2️⃣ Periodo debe permitir edición
        if (!$period->allowsGradeEdition()) {
            return false;
        }

        // 3️⃣ Profesor: solo su materia
        if ($user->hasRole('professor')) {
            return $user->professor
                && $user->professor->id === $grade->professor_id;
        }

        return false;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Grade $grade): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Grade $grade): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Grade $grade): bool
    {
        //
    }
}

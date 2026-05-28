<?php

namespace App\Policies;

use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Models\User;
use App\Services\EnrollmentService;

class SubjectEnrollmentPolicy
{
    /**
     * 🎯 Inscripción
     * - Estudiante: solo puede intentar (validación real en service)
     * - Admin/Registrar: override permitido
     */
    public function enroll(User $user, ClassGroup $group): bool
    {
        if ($user->hasAnyRole(['admin', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('student')) {
            return (bool) $user->student;
        }

        return false;
    }

    /**
     * 🎯 Retiro
     */
    public function unenroll(User $user, SubjectEnrollment $enrollment): bool
    {
        if ($user->hasAnyRole(['admin', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $user->student?->id === $enrollment->student_id;
        }

        return false;
    }

    /**
     * 🎯 Ver inscripción
     */
    public function view(User $user, SubjectEnrollment $enrollment): bool
    {
        if ($user->hasAnyRole(['admin', 'registrar'])) {
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

    /**
     * 🎯 Calificar
     */
    public function grade(User $user, SubjectEnrollment $enrollment): bool
    {
        return $user->hasRole('professor')
            && $enrollment->classGroup?->professor_id === $user->id;
    }

    /**
     * 🎯 Finalizar inscripción (estado académico)
     */
    public function finalize(User $user, SubjectEnrollment $enrollment): bool
    {
        return $user->hasAnyRole([
            'admin',
            'academic_coordinator',
        ]);
    }

    /**
     * 🎯 Cambiar estado
     */
    public function changeStatus(User $user, SubjectEnrollment $enrollment, string $toStatus): bool
    {
        return match ($toStatus) {

            // administrativos
            'enrolled', 'cancelled' =>
            $user->hasAnyRole(['admin', 'registrar']),

            // académicos (docente)
            'approved', 'failed' =>
            $user->hasRole('professor')
                && $enrollment->classGroup?->professor_id === $user->id,

            default => false,
        };
    }

    /**
     * 🎯 Pre-check opcional (UI)
     * ⚠️ No bloquea backend real, solo ayuda al frontend
     */
    public function canAttemptEnroll(User $user, ClassGroup $group, EnrollmentService $service): bool
    {
        if (! $user->hasRole('student')) {
            return false;
        }

        $result = $service->canEnroll($user->student, $group);

        return $result['can_enroll'];
    }
}

<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SubjectEnrollment;
use App\Models\AcademicPeriod;
use App\Models\Subject;
use Illuminate\Support\Collection;

class DegreeAuditService
{
    protected Student $student;

    protected Collection $approvedSubjectIds;
    protected Collection $failedSubjectIds;
    protected Collection $enrolledSubjectIds;
    protected $failedOrCancelledSubjectIds;
    public readonly int $maxCreditsPerPeriod;
    protected int $minCreditsPerPeriod = 9;

    protected bool $hasCurriculum = true;

    public function __construct(Student $student)
    {
        $this->student = $student;

        // 🔒 LÍMITE ACADÉMICO (por ahora fijo)
        $this->maxCreditsPerPeriod = 27;

        // 🛡️ Si no tiene currículo, inicializamos colecciones vacías
        if (!$student->curriculum) {
            $this->hasCurriculum = false;
            $this->approvedSubjectIds = collect();
            $this->failedSubjectIds = collect();
            $this->enrolledSubjectIds = collect();
            return;
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        $this->approvedSubjectIds = SubjectEnrollment::where('student_id', $student->id)
            ->whereHas('status', fn($q) => $q->where('code', 'approved'))
            ->pluck('subject_id');

        $this->failedOrCancelledSubjectIds = SubjectEnrollment::where('student_id', $student->id)
            ->whereHas(
                'status',
                fn($q) =>
                $q->whereIn('code', ['failed', 'cancelled'])
            )
            ->pluck('subject_id');

        $this->enrolledSubjectIds = $activePeriod
            ? SubjectEnrollment::where('student_id', $student->id)
            ->where('academic_period_id', $activePeriod->id)
            ->whereHas(
                'status',
                fn($q) => $q->whereIn('code', config('enrollment.active_status_codes'))
            )
            ->pluck('subject_id')
            : collect();
    }

    public function evaluateSubject($subject): array
    {
        $approved = $this->approvedSubjectIds->contains($subject->id);
        $enrolled = $this->enrolledSubjectIds->contains($subject->id);

        $hasAllPrerequisites = $subject->prerequisites->every(
            fn($pr) => $this->approvedSubjectIds->contains($pr->id)
        );

        if ($approved) {
            $status = 'approved';
        } elseif ($enrolled) {
            $status = 'in_progress';
        } elseif (!$hasAllPrerequisites) {
            $status = 'blocked';
        } else {
            // ya la cursó pero no la aprobó → repetición
            $hasHistory = SubjectEnrollment::where('student_id', $this->student->id)
                ->where('subject_id', $subject->id)
                ->exists();

            $status = $hasHistory
                ? 'available_but_repeating'
                : 'available';
        }

        return [
            'audit' => [
                'status' => $status,
                'blockedBy' => $hasAllPrerequisites
                    ? []
                    : $subject->prerequisites
                    ->whereNotIn('id', $this->approvedSubjectIds)
                    ->pluck('name')
                    ->values(),
            ],
            'canEnroll' => in_array($status, [
                'available',
                'available_but_repeating',
            ]),
        ];
    }


    public function progress(): float
    {
        if (!$this->student->curriculum) {
            return 0;
        }

        $totalSubjects = $this->student->curriculum->subjects()->count();

        return $totalSubjects > 0
            ? round(($this->approvedSubjectIds->count() / $totalSubjects) * 100, 1)
            : 0;
    }

    public function currentPeriodCredits(AcademicPeriod $period): int
    {
        return SubjectEnrollment::where('student_id', $this->student->id)
            ->where('academic_period_id', $period->id)
            ->whereHas(
                'status',
                fn($q) => $q->whereIn('code', config('enrollment.active_status_codes'))
            )
            ->with('subject')
            ->get()
            ->sum(
                fn($enrollment) => $enrollment->subject?->credits ?? 0
            );
    }

    public function canAddSubject(Subject $subject, AcademicPeriod $period): array
    {
        /**
         * Créditos ya inscritos en el período
         */
        $currentCredits = $this->currentPeriodCredits($period);

        /**
         * Créditos de la asignatura según currículo
         */
        $subjectCredits = (int) ($subject->pivot->credits ?? 0);

        /**
         * Validación dura: límite máximo
         */
        if (($currentCredits + $subjectCredits) > $this->maxCreditsPerPeriod) {
            return [
                'allowed' => false,
                'reason' => 'max_credits_exceeded',
                'message' => 'Maximum credit limit exceeded for this academic period.',
                'current' => $currentCredits,
                'subject' => $subjectCredits,
                'after' => $currentCredits + $subjectCredits,
                'max' => $this->maxCreditsPerPeriod,
            ];
        }

        /**
         * OK → puede inscribir
         */
        return [
            'allowed' => true,
            'current' => $currentCredits,
            'subject' => $subjectCredits,
            'after' => $currentCredits + $subjectCredits,
            'max' => $this->maxCreditsPerPeriod,
        ];
    }

    public function authorizeEnrollment(
        Subject $subject,
        AcademicPeriod $period
    ): array {
        $evaluation = $this->evaluateSubject($subject);

        if (!$evaluation['canEnroll']) {
            return [
                'allowed' => false,
                'reason' => 'academic_block',
                'status' => $evaluation['audit']['status'],
                'blocked_by' => $evaluation['audit']['blockedBy'] ?? [],
            ];
        }

        // Validación de créditos
        $creditCheck = $this->canAddSubject($subject, $period);

        if (!$creditCheck['allowed']) {
            return [
                'allowed' => false,
                'reason' => 'credit_limit',
                'details' => $creditCheck,
            ];
        }

        return [
            'allowed' => true,
            'audit_status' => $evaluation['audit']['status'],
        ];
    }

    public function authorizeUnenrollment(
        SubjectEnrollment $enrollment,
        AcademicPeriod $period
    ): array {
        /**
         * 1️⃣ Validar período activo
         */
        if (!$period->is_active) {
            return [
                'allowed' => false,
                'reason' => 'Academic period is not active.',
                'status' => 'period_closed',
            ];
        }

        $statusCode = $enrollment->status?->code;

        /**
         * 2️⃣ No permitir retiro si ya está aprobada
         */
        if (in_array($statusCode, ['approved', 'completed'])) {
            return [
                'allowed' => false,
                'reason' => 'You cannot unenroll from a completed subject.',
                'status' => 'subject_completed',
                'details' => [
                    'subject' => $enrollment->subject->code,
                    'current_status' => $statusCode,
                ],
            ];
        }

        /**
         * 3️⃣ (Opcional pero recomendado)
         * No permitir retiro si ya fue reprobada
         */
        if ($statusCode === 'failed') {
            return [
                'allowed' => false,
                'reason' => 'You cannot unenroll from a failed subject.',
                'status' => 'subject_failed',
                'details' => [
                    'subject' => $enrollment->subject->code,
                ],
            ];
        }

        /**
         * ✅ 4️⃣ Retiro permitido
         */
        return [
            'allowed' => true,
        ];
    }
}

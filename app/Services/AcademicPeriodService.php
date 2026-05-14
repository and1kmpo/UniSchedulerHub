<?php

namespace App\Services;

use App\Enums\AcademicPeriodState;
use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\SubjectEnrollment;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    public function openEnrollment(AcademicPeriod $period): void
    {
        $this->transition(
            $period,
            AcademicPeriodState::DRAFT,
            AcademicPeriodState::ENROLLMENT_OPEN
        );
    }

    public function closeEnrollment(AcademicPeriod $period): void
    {
        $this->transition(
            $period,
            AcademicPeriodState::ENROLLMENT_OPEN,
            AcademicPeriodState::ENROLLMENT_CLOSED
        );

        $enrollments = SubjectEnrollment::where('academic_period_id', $period->id)
            ->whereHas('status', fn($q) => $q->where('code', 'pre_enrolled'))
            ->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->transitionTo('enrolled');
        }
    }

    public function startPeriod(AcademicPeriod $period): void
    {
        $this->transition(
            $period,
            AcademicPeriodState::ENROLLMENT_CLOSED,
            AcademicPeriodState::IN_PROGRESS
        );
    }

    public function closeAcademicPeriod(AcademicPeriod $period): void
    {
        $this->transition(
            $period,
            AcademicPeriodState::IN_PROGRESS,
            AcademicPeriodState::ACADEMICALLY_CLOSED
        );
    }

    public function archive(AcademicPeriod $period): void
    {
        $this->transition(
            $period,
            AcademicPeriodState::ACADEMICALLY_CLOSED,
            AcademicPeriodState::ARCHIVED
        );
    }

    protected function transition(
        AcademicPeriod $period,
        AcademicPeriodState $from,
        AcademicPeriodState $to
    ): void {

        if ($period->isFinal()) {
            throw new \DomainException('BLOCK_PERIOD_ALREADY_FINAL');
        }

        // ✅ Validar estado actual correctamente (Enum)
        if ($period->state() !== $from) {
            throw new \DomainException(
                "INVALID_TRANSITION_FROM_{$period->state()->value}_TO_{$to->value}"
            );
        }

        $statusId = AcademicPeriodStatus::where('code', $to->value)->value('id');

        DB::transaction(function () use ($period, $statusId) {
            $period->update([
                'academic_period_status_id' => $statusId,
                'status_changed_at' => now(),
            ]);
        });
    }
}

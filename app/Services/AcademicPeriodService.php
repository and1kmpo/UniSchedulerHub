<?php

namespace App\Services;

use App\Enums\AcademicPeriodState;
use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\SubjectEnrollment;
use DomainException;
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
            ->whereHas('status', fn($query) => $query->where('code', 'pre_enrolled'))
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

        $period->update(['is_active' => false]);
    }

    public function closePeriod(AcademicPeriod $period): void
    {
        $this->closeEnrollment($period);
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
        $period->loadMissing('status');

        if ($period->isFinal() && $from !== AcademicPeriodState::ACADEMICALLY_CLOSED) {
            throw new DomainException('BLOCK_PERIOD_ALREADY_FINAL');
        }

        if (! $period->state()) {
            throw new DomainException('BLOCK_PERIOD_HAS_NO_STATUS');
        }

        if ($period->state() !== $from) {
            throw new DomainException(
                "INVALID_TRANSITION_FROM_{$period->state()->value}_TO_{$to->value}"
            );
        }

        $statusId = AcademicPeriodStatus::where('code', $to->value)->value('id');

        if (! $statusId) {
            throw new DomainException("BLOCK_TARGET_STATUS_NOT_FOUND_{$to->value}");
        }

        DB::transaction(function () use ($period, $from, $to, $statusId) {
            $period->update([
                'academic_period_status_id' => $statusId,
                'status_changed_at' => now(),
            ]);

            app(AcademicAuditService::class)->record(
                'academic_period.transitioned',
                $period,
                [
                    'from' => $from->value,
                    'to' => $to->value,
                ],
                "Academic period transitioned from {$from->value} to {$to->value}"
            );
        });
    }
}

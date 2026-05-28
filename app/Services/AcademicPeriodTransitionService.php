<?php

namespace App\Services\AcademicPeriod;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Enums\AcademicPeriodState;
use DomainException;

class AcademicPeriodTransitionService
{
    private const TRANSITIONS = [
        AcademicPeriodState::DRAFT->value => [
            AcademicPeriodState::ENROLLMENT_OPEN,
        ],

        AcademicPeriodState::ENROLLMENT_OPEN->value => [
            AcademicPeriodState::ENROLLMENT_CLOSED,
        ],

        AcademicPeriodState::ENROLLMENT_CLOSED->value => [
            AcademicPeriodState::IN_PROGRESS,
        ],

        AcademicPeriodState::IN_PROGRESS->value => [
            AcademicPeriodState::ACADEMICALLY_CLOSED,
        ],

        AcademicPeriodState::ACADEMICALLY_CLOSED->value => [
            AcademicPeriodState::ARCHIVED,
        ],

        AcademicPeriodState::ARCHIVED->value => [],
    ];

    public function openEnrollment(AcademicPeriod $period): void
    {
        $this->transition($period, AcademicPeriodState::ENROLLMENT_OPEN);
    }

    public function closeEnrollment(AcademicPeriod $period): void
    {
        $this->transition($period, AcademicPeriodState::ENROLLMENT_CLOSED);
    }

    public function startPeriod(AcademicPeriod $period): void
    {
        $this->transition($period, AcademicPeriodState::IN_PROGRESS);
    }

    public function closeAcademically(AcademicPeriod $period): void
    {
        $this->transition($period, AcademicPeriodState::ACADEMICALLY_CLOSED);
    }

    public function archive(AcademicPeriod $period): void
    {
        $this->transition($period, AcademicPeriodState::ARCHIVED);
    }

    private function transition(
        AcademicPeriod $period,
        AcademicPeriodState $target
    ): void {
        $this->ensureTransitionIsAllowed($period, $target);
        $this->transitionTo($period, $target);
    }

    private function ensureTransitionIsAllowed(
        AcademicPeriod $period,
        AcademicPeriodState $target
    ): void {
        $current = $period->state();

        if (! $current) {
            throw new DomainException('Invalid current state.');
        }

        $allowedTransitions = self::TRANSITIONS[$current->value] ?? [];

        $isAllowed = collect($allowedTransitions)
            ->contains(fn($state) => $state === $target);

        if (! $isAllowed) {
            throw new DomainException(
                "Transition from {$current->value} to {$target->value} is not allowed."
            );
        }
    }

    private function transitionTo(
        AcademicPeriod $period,
        AcademicPeriodState $state
    ): void {
        $status = AcademicPeriodStatus::where('code', $state->value)->firstOrFail();

        $period->academic_period_status_id = $status->id;
        $period->save();
    }
}

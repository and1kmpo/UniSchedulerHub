<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use DomainException;

class ClassScheduleService
{
    public function create(ClassGroup $group, array $data)
    {
        $group->loadMissing('academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen($group->academicPeriod);
        $this->ensureGroupAllowsScheduleChanges($group);

        return $group->schedules()->create([
            ...$data,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    public function update(ClassSchedule $schedule, array $data)
    {
        $schedule->loadMissing('classGroup.academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen(
            $schedule->classGroup->academicPeriod
        );
        $this->ensureGroupAllowsScheduleChanges($schedule->classGroup);

        $schedule->update([
            ...$data,
            'updated_by' => auth()->id(),
        ]);
    }

    public function delete(ClassSchedule $schedule)
    {
        $schedule->loadMissing('classGroup.academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen(
            $schedule->classGroup->academicPeriod
        );
        $this->ensureGroupAllowsScheduleChanges($schedule->classGroup);

        if ($schedule->classGroup->subjectEnrollments()->exists()) {
            $schedule->update([
                'status' => ClassSchedule::STATUS_CANCELLED,
                'updated_by' => auth()->id(),
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
            ]);

            return;
        }

        $schedule->delete();
    }

    private function ensureGroupAllowsScheduleChanges(ClassGroup $group): void
    {
        if (! $group->canManageSchedules()) {
            throw new DomainException('BLOCK_GROUP_SCHEDULE_LOCKED');
        }
    }
}

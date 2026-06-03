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
        $group->loadMissing('academicPeriod', 'subject');

        AcademicPeriodGuard::ensurePeriodNotFrozen($group->academicPeriod);
        $this->ensureGroupAllowsScheduleChanges($group);
        $this->ensureNoConflicts($group, $data);

        $schedule = $group->schedules()->create([
            ...$data,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        app(AcademicAuditService::class)->record(
            'schedule.created',
            $schedule,
            [
                'class_group_id' => $group->id,
                'academic_period_id' => $group->academic_period_id,
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'classroom_id' => $schedule->classroom_id,
            ],
            'Class schedule created'
        );

        return $schedule;
    }

    public function update(ClassSchedule $schedule, array $data)
    {
        $schedule->loadMissing('classGroup.academicPeriod', 'classGroup.subject');

        AcademicPeriodGuard::ensurePeriodNotFrozen(
            $schedule->classGroup->academicPeriod
        );
        $this->ensureGroupAllowsScheduleChanges($schedule->classGroup);
        $this->ensureNoConflicts($schedule->classGroup, $data, $schedule->id);

        $before = $schedule->only(['day', 'start_time', 'end_time', 'classroom_id', 'status']);

        $schedule->update([
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        app(AcademicAuditService::class)->record(
            'schedule.updated',
            $schedule,
            [
                'class_group_id' => $schedule->class_group_id,
                'before' => $before,
                'after' => $schedule->fresh()->only(['day', 'start_time', 'end_time', 'classroom_id', 'status']),
            ],
            'Class schedule updated'
        );

        return $schedule->fresh(['classroom', 'classGroup.subject', 'classGroup.professor']);
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

            app(AcademicAuditService::class)->record(
                'schedule.cancelled',
                $schedule,
                [
                    'class_group_id' => $schedule->class_group_id,
                    'reason' => 'schedule_has_enrollments',
                ],
                'Class schedule cancelled'
            );

            return;
        }

        app(AcademicAuditService::class)->record(
            'schedule.deleted',
            $schedule,
            [
                'class_group_id' => $schedule->class_group_id,
            ],
            'Class schedule deleted'
        );

        $schedule->delete();
    }

    private function ensureGroupAllowsScheduleChanges(ClassGroup $group): void
    {
        if (! $group->canManageSchedules()) {
            throw new DomainException('BLOCK_GROUP_SCHEDULE_LOCKED');
        }
    }

    private function ensureNoConflicts(ClassGroup $group, array $data, ?int $ignoreScheduleId = null): void
    {
        if ($this->findGroupConflict($group, $data, $ignoreScheduleId)) {
            throw new DomainException('BLOCK_GROUP_SCHEDULE_CONFLICT');
        }

        if (! empty($data['classroom_id']) && $this->findClassroomConflict($data, $ignoreScheduleId)) {
            throw new DomainException('BLOCK_CLASSROOM_SCHEDULE_CONFLICT');
        }

        if ($this->findProfessorConflict($group, $data, $ignoreScheduleId)) {
            throw new DomainException('BLOCK_PROFESSOR_SCHEDULE_CONFLICT');
        }
    }

    private function findGroupConflict(ClassGroup $group, array $data, ?int $ignoreScheduleId = null): ?ClassSchedule
    {
        return $this->overlappingSchedules($data, $ignoreScheduleId)
            ->where('class_group_id', $group->id)
            ->first();
    }

    private function findClassroomConflict(array $data, ?int $ignoreScheduleId = null): ?ClassSchedule
    {
        return $this->overlappingSchedules($data, $ignoreScheduleId)
            ->where('classroom_id', $data['classroom_id'])
            ->first();
    }

    private function findProfessorConflict(ClassGroup $group, array $data, ?int $ignoreScheduleId = null): ?ClassSchedule
    {
        return $this->overlappingSchedules($data, $ignoreScheduleId)
            ->whereHas('classGroup', fn($query) => $query->where('professor_id', $group->professor_id))
            ->first();
    }

    private function overlappingSchedules(array $data, ?int $ignoreScheduleId = null)
    {
        return ClassSchedule::query()
            ->when($ignoreScheduleId, fn($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->where('status', '!=', ClassSchedule::STATUS_CANCELLED)
            ->where('day', $data['day'])
            ->whereTime('start_time', '<', $data['end_time'])
            ->whereTime('end_time', '>', $data['start_time'])
            ->with(['classGroup.subject', 'classroom']);
    }
}

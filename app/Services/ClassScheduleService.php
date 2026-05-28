<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;

class ClassScheduleService
{
    public function create(ClassGroup $group, array $data)
    {
        $group->loadMissing('academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen($group->academicPeriod);

        return $group->schedules()->create($data);
    }

    public function update(ClassSchedule $schedule, array $data)
    {
        $schedule->loadMissing('classGroup.academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen(
            $schedule->classGroup->academicPeriod
        );

        $schedule->update($data);
    }

    public function delete(ClassSchedule $schedule)
    {
        $schedule->loadMissing('classGroup.academicPeriod');

        AcademicPeriodGuard::ensurePeriodNotFrozen(
            $schedule->classGroup->academicPeriod
        );

        $schedule->delete();
    }
}

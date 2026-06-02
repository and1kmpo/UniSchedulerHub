<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Models\ClassGroup;

class ClassGroupService
{
    public function delete(ClassGroup $group): string
    {
        $group->loadMissing('academicPeriod');

        $period = $group->academicPeriod;

        AcademicPeriodGuard::ensurePeriodNotFrozen($period);

        if ($group->subjectEnrollments()->exists() || $group->schedules()->exists()) {
            $group->update([
                'status' => ClassGroup::STATUS_CANCELLED,
            ]);

            return 'cancelled';
        }

        $group->delete();

        return 'deleted';
    }
}

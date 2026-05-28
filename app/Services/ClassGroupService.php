<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Models\ClassGroup;

class ClassGroupService
{
    public function delete(ClassGroup $group): void
    {
        $group->loadMissing('academicPeriod');

        $period = $group->academicPeriod;

        AcademicPeriodGuard::ensurePeriodNotFrozen($period);

        $group->delete();
    }
}

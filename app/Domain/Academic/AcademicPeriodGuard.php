<?php

namespace App\Domain\Academic;

use App\Models\AcademicPeriod;
use DomainException;

class AcademicPeriodGuard
{

    private static function ensurePeriodExists(?AcademicPeriod $period): void
    {
        if (! $period) {
            throw new DomainException('BLOCK_NO_ACADEMIC_PERIOD');
        }
    }

    public static function ensureEnrollmentAllowed(?AcademicPeriod $period): void
    {
        self::ensurePeriodExists($period);

        if (! $period->allowsEnrollment()) {
            throw new DomainException('BLOCK_PERIOD_CLOSED');
        }
    }

    public static function ensureUnenrollmentAllowed(?AcademicPeriod $period): void
    {
        self::ensurePeriodExists($period);

        if (! $period->canUnenroll()) {
            throw new DomainException('BLOCK_UNENROLL_PERIOD_CLOSED');
        }
    }

    public static function ensureGradesEditable(?AcademicPeriod $period): void
    {
        self::ensurePeriodExists($period);

        if (! $period->canEditGrades()) {
            throw new DomainException('BLOCK_PERIOD_DOES_NOT_ALLOW_GRADES');
        }
    }

    public static function ensurePeriodNotFrozen(?AcademicPeriod $period): void
    {
        self::ensurePeriodExists($period);

        if ($period->isAcademicallyClosed()) {
            throw new DomainException('BLOCK_PERIOD_FROZEN');
        }
    }
}

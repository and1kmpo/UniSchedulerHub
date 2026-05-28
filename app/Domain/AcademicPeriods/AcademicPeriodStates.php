<?php

namespace App\Domain\AcademicPeriods;

final class AcademicPeriodStates
{
    public const DRAFT = 'draft';
    public const ENROLLMENT_OPEN = 'enrollment_open';
    public const ENROLLMENT_CLOSED = 'enrollment_closed';
    public const IN_PROGRESS = 'in_progress';
    public const ACADEMICALLY_CLOSED = 'academically_closed';
    public const ARCHIVED = 'archived';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::ENROLLMENT_OPEN,
            self::ENROLLMENT_CLOSED,
            self::IN_PROGRESS,
            self::ACADEMICALLY_CLOSED,
            self::ARCHIVED,
        ];
    }
}

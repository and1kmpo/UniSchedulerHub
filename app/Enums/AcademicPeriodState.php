<?php

namespace App\Enums;

enum AcademicPeriodState: string
{
    case DRAFT = 'draft';
    case ENROLLMENT_OPEN = 'enrollment_open';
    case ENROLLMENT_CLOSED = 'enrollment_closed';
    case IN_PROGRESS = 'in_progress';
    case ACADEMICALLY_CLOSED = 'academically_closed';
    case ARCHIVED = 'archived';

    /* =========================
     | State classification
     |=========================*/
    public function isFinal(): bool
    {
        return match ($this) {
            self::ACADEMICALLY_CLOSED,
            self::ARCHIVED => true,
            default => false,
        };
    }

    /* =========================
     | Enrollment rules
     |=========================*/
    public function allowsEnrollment(): bool
    {
        return match ($this) {
            self::ENROLLMENT_OPEN => true,
            default => false,
        };
    }

    public function allowsUnenrollment(): bool
    {
        return match ($this) {
            self::ENROLLMENT_OPEN,
            self::ENROLLMENT_CLOSED => true,
            default => false,
        };
    }

    /* =========================
     | Academic rules
     |=========================*/
    public function allowsGradeEdition(): bool
    {
        return match ($this) {
            self::IN_PROGRESS => true,
            default => false,
        };
    }
}

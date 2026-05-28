<?php

namespace App\Domain\Grades;

use App\Enums\GradeStatuses;

class GradeEvaluationResult
{
    public function __construct(
        public readonly ?float $finalGrade,
        public readonly ?string $statusCode
    ) {}

    public static function pending(): self
    {
        return new self(null, null);
    }

    public static function passed(float $final): self
    {
        return new self($final, GradeStatuses::PASSED->value);
    }

    public static function failed(float $final): self
    {
        return new self($final, GradeStatuses::FAILED->value);
    }

    public static function failedAttendance(float $final): self
    {
        return new self($final, GradeStatuses::FAILED_ATTENDANCE->value);
    }
}

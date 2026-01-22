<?php

namespace App\Domain\Grades;

use App\Enums\GradeStatuses;

class GradeEvaluator
{
    public static function evaluate(array $data): GradeEvaluationResult
    {
        if (!self::hasRequiredGrades($data)) {
            return GradeEvaluationResult::pending();
        }

        $final = self::calculateFinal($data);

        if (!isset($data['attendance']) || $data['attendance'] < 80) {
            return GradeEvaluationResult::failedAttendance($final);
        }

        return $final >= 3.0
            ? GradeEvaluationResult::passed($final)
            : GradeEvaluationResult::failed($final);
    }

    private static function hasRequiredGrades(array $data): bool
    {
        foreach (['first_exam', 'second_exam', 'third_exam', 'activities'] as $field) {
            if (!isset($data[$field]) || !is_numeric($data[$field])) {
                return false;
            }
        }
        return true;
    }

    private static function calculateFinal(array $data): float
    {
        return round(
            $data['first_exam'] * 0.25 +
                $data['second_exam'] * 0.25 +
                $data['third_exam'] * 0.30 +
                $data['activities'] * 0.20,
            2
        );
    }
}

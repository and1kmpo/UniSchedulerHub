<?php

namespace App\Services\Enrollment\Recommendations;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class EnrollmentRecommendationService
{
    public function generate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): array {
        $recommendations = [];

        if (! empty($result->conflicts)) {
            $recommendations[] = [
                'type' => 'schedule_alternative',
                'priority' => 'high',
                'message' => 'Review another group for the same subject or move one of the conflicting schedule blocks.',
            ];
        }

        if (($result->meta['available_slots'] ?? 0) <= 3 && ($result->meta['available_slots'] ?? 0) > 0) {
            $recommendations[] = [
                'type' => 'capacity_warning',
                'priority' => 'medium',
                'message' => 'Enroll soon or consider opening another group because this group is close to capacity.',
            ];
        }

        if (($result->meta['projected_credits'] ?? 0) > config('enrollment.max_credits', 21)) {
            $recommendations[] = [
                'type' => 'load_review',
                'priority' => 'medium',
                'message' => 'Review the student academic load before confirming this enrollment.',
            ];
        }

        if (empty($recommendations) && $result->allowed) {
            $recommendations[] = [
                'type' => 'ready',
                'priority' => 'low',
                'message' => 'The enrollment is ready to be confirmed.',
            ];
        }

        return $recommendations;
    }
}

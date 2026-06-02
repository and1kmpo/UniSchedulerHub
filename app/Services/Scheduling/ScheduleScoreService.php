<?php

namespace App\Services\Scheduling;

class ScheduleScoreService
{
    public function calculate(array $data): array
    {
        $score = 100;

        $penalties = [];

        /*
        |--------------------------------------------------------------------------
        | Conflicts
        |--------------------------------------------------------------------------
        */

        $conflictCount = count(
            $data['conflicts'] ?? []
        );

        if ($conflictCount > 0) {

            $deduction = $conflictCount * 15;

            $score -= $deduction;

            $penalties[] = [
                'type' => 'conflicts',
                'points' => -$deduction,
                'message' => 'Schedule overlaps detected',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Professor overload
        |--------------------------------------------------------------------------
        */

        foreach ($data['professors'] ?? [] as $professor) {

            if ($professor['rebalance_required']) {

                $score -= 10;

                $penalties[] = [
                    'type' => 'professor_overload',
                    'points' => -10,
                    'message' => "Professor overload detected",
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Classroom saturation
        |--------------------------------------------------------------------------
        */

        foreach ($data['classrooms'] ?? [] as $classroom) {

            if ($classroom['status'] === 'high') {

                $score -= 5;

                $penalties[] = [
                    'type' => 'classroom_saturation',
                    'points' => -5,
                    'message' => 'High classroom utilization',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Clamp score
        |--------------------------------------------------------------------------
        */

        $score = max(0, min(100, $score));

        return [
            'score' => $score,

            'grade' => $this->grade($score),

            'penalties' => $penalties,
        ];
    }

    protected function grade(int $score): string
    {
        return match (true) {

            $score >= 90 => 'Excellent',

            $score >= 75 => 'Good',

            $score >= 60 => 'Average',

            default => 'Poor',
        };
    }
}

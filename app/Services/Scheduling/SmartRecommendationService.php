<?php

namespace App\Services\Scheduling;

class SmartRecommendationService
{
    public function generate(array $data): array
    {
        $recommendations = [];

        /*
        |--------------------------------------------------------------------------
        | Conflict recommendations
        |--------------------------------------------------------------------------
        */

        if (!empty($data['conflicts'])) {

            $recommendations[] = [
                'type' => 'conflict_resolution',
                'priority' => 'high',
                'message' => 'Rearrange overlapping schedules.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Professor overload
        |--------------------------------------------------------------------------
        */

        foreach ($data['professors'] ?? [] as $professor) {

            if ($professor['rebalance_required']) {

                $recommendations[] = [
                    'type' => 'professor_rebalance',
                    'priority' => 'medium',
                    'message' =>
                    "Reduce workload for {$professor['professor']}.",
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Classroom optimization
        |--------------------------------------------------------------------------
        */

        foreach ($data['classrooms'] ?? [] as $room) {

            if ($room['status'] === 'high') {

                $recommendations[] = [
                    'type' => 'classroom_optimization',
                    'priority' => 'medium',
                    'message' =>
                    "Optimize classroom usage for {$room['classroom']}.",
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | High score positive feedback
        |--------------------------------------------------------------------------
        */

        if (($data['score']['score'] ?? 0) >= 90) {

            $recommendations[] = [
                'type' => 'positive',
                'priority' => 'low',
                'message' => 'Schedule efficiency is excellent.',
            ];
        }

        return $recommendations;
    }
}

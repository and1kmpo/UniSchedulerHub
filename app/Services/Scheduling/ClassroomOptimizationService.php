<?php

namespace App\Services\Scheduling;

class ClassroomOptimizationService
{
    public function optimize(array $schedules): array
    {
        $utilization = [];

        foreach ($schedules as $schedule) {

            $room = $schedule['classroom'] ?? 'Unassigned';

            if (!isset($utilization[$room])) {

                $utilization[$room] = [
                    'classroom' => $room,
                    'usage' => 0,
                    'schedules' => [],
                ];
            }

            $utilization[$room]['usage']++;

            $utilization[$room]['schedules'][] = [
                'subject' => $schedule['subject'] ?? null,
                'day' => $schedule['day'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Utilization percentage
        |--------------------------------------------------------------------------
        */

        foreach ($utilization as &$room) {

            $room['utilization_score'] =
                min(
                    100,
                    $room['usage'] * 10
                );

            $room['status'] =
                $room['utilization_score'] >= 85
                ? 'high'
                : (
                    $room['utilization_score'] >= 60
                    ? 'medium'
                    : 'low'
                );
        }

        return array_values($utilization);
    }
}

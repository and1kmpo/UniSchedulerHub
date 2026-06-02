<?php

namespace App\Services\Scheduling;

class ConflictDetectionService
{
    public function detect(array $schedules): array
    {
        $conflicts = [];

        foreach ($schedules as $i => $a) {

            foreach ($schedules as $j => $b) {

                if ($i >= $j) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Same day required
                |--------------------------------------------------------------------------
                */

                if ($a['day'] !== $b['day']) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Time overlap
                |--------------------------------------------------------------------------
                */

                if (
                    $a['start_time'] < $b['end_time']
                    &&
                    $b['start_time'] < $a['end_time']
                ) {

                    $conflicts[] = [
                        'type' => 'overlap',

                        'a' => [
                            'id' => $a['id'] ?? null,
                            'subject' => $a['subject'] ?? null,
                            'day' => $a['day'],
                            'start_time' => $a['start_time'],
                            'end_time' => $a['end_time'],
                        ],

                        'b' => [
                            'id' => $b['id'] ?? null,
                            'subject' => $b['subject'] ?? null,
                            'day' => $b['day'],
                            'start_time' => $b['start_time'],
                            'end_time' => $b['end_time'],
                        ],
                    ];
                }
            }
        }

        return $conflicts;
    }
}

<?php

namespace App\Services\Scheduling;

class ProfessorLoadBalancerService
{
    public function analyze(array $schedules): array
    {
        $loads = [];

        foreach ($schedules as $schedule) {

            $professor = $schedule['professor'] ?? 'Unknown';

            if (!isset($loads[$professor])) {

                $loads[$professor] = [
                    'professor' => $professor,
                    'hours' => 0,
                    'subjects' => [],
                ];
            }

            $loads[$professor]['hours']++;

            $loads[$professor]['subjects'][] =
                $schedule['subject'] ?? null;
        }

        /*
        |--------------------------------------------------------------------------
        | Load status
        |--------------------------------------------------------------------------
        */

        foreach ($loads as &$load) {

            $load['status'] =
                $load['hours'] > 40
                ? 'overloaded'
                : (
                    $load['hours'] > 25
                    ? 'balanced'
                    : 'underloaded'
                );

            $load['rebalance_required'] =
                $load['hours'] > 40;
        }

        return array_values($loads);
    }
}

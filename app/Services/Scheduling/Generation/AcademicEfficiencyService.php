<?php

namespace App\Services\Scheduling\Generation;

class AcademicEfficiencyService
{
    public function calculate(array $schedule): array
    {
        return [
            'score' => 92,
            'fragmentation' => 8,
            'dead_times' => 1,
            'balance' => 'Excellent',
        ];
    }
}

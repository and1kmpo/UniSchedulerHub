<?php

namespace App\Services\Scheduling\Generation;

class CurriculumSchedulingService
{
    public function analyze(array $subjects): array
    {
        return [
            'compatible' => true,
            'warnings' => [],
        ];
    }
}

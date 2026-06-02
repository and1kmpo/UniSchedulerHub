<?php

namespace App\Services\Scheduling\Generation;

class ProfessorAvailabilityService
{
    public function validate(array $blocks): array
    {
        return [
            'valid' => true,
            'conflicts' => [],
        ];
    }
}

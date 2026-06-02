<?php

namespace App\Services\Scheduling\Generation;

class FreeSlotFinderService
{
    public function find(array $constraints = []): array
    {
        return [
            [
                'day' => 'Monday',
                'start' => '07:00',
                'end' => '09:00',
            ],
            [
                'day' => 'Tuesday',
                'start' => '09:00',
                'end' => '11:00',
            ],
        ];
    }
}

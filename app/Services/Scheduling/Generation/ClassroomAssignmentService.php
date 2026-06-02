<?php

namespace App\Services\Scheduling\Generation;

use App\Models\Classroom;

class ClassroomAssignmentService
{
    public function assign(array $blocks): array
    {
        $classrooms = Classroom::all();

        return collect($blocks)
            ->map(function ($block, $index) use ($classrooms) {

                $room = $classrooms[$index % $classrooms->count()] ?? null;

                $block['classroom'] = $room;

                return $block;
            })
            ->toArray();
    }
}

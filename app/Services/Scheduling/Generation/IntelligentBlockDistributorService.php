<?php

namespace App\Services\Scheduling\Generation;

class IntelligentBlockDistributorService
{
    public function distribute(
        array $subjects,
        array $slots
    ): array {

        return collect($subjects)
            ->map(function ($subject, $index) use ($slots) {

                return [
                    'subject_id' => $subject['id'],
                    'slot' => $slots[$index % count($slots)],
                ];
            })
            ->toArray();
    }
}

<?php

namespace App\Filters;

class ClassroomFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'name',
        'floor',
        'capacity',
        'status',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {
            $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%")
                ->orWhereHas('building', function ($building) use ($value) {
                    $building
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                });
        });
    }

    public function building($value): void
    {
        $this->query->where('building_id', $value);
    }

    public function floor($value): void
    {
        $this->query->where('floor', $value);
    }

    public function status(string $value): void
    {
        $this->query->where('status', $value);
    }
}

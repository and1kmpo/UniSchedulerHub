<?php

namespace App\Filters;

class BuildingFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'name',
        'code',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('code', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%");
        });
    }
}

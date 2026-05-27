<?php

namespace App\Filters;

class ProgramFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'name',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%");
        });
    }
}

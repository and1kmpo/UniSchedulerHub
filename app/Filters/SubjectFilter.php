<?php

namespace App\Filters;

class SubjectFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'name',
        'credits',
        'knowledge_area',
        'created_at',
    ];

    protected string $defaultSort = 'created_at';

    protected string $defaultDirection = 'desc';

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%")
                ->orWhere('knowledge_area', 'like', "%{$value}%");
        });
    }

    public function elective($value): void
    {
        $this->query->where('elective', $value);
    }
}

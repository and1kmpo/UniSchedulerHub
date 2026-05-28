<?php

namespace App\Filters;

class StudentFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'id',
        'document',
        'phone',
        'semester',
        'academic_status',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('document', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")

                ->orWhereHas('user', function ($user) use ($value) {

                    $user
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                })

                ->orWhereHas('program', function ($program) use ($value) {

                    $program
                        ->where('name', 'like', "%{$value}%");
                });
        });
    }

    public function program($value): void
    {
        $this->query->where('program_id', $value);
    }

    public function academic_status($value): void
    {
        $this->query->where('academic_status', $value);
    }

    public function semester($value): void
    {
        $this->query->where('semester', $value);
    }
}

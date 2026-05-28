<?php

namespace App\Filters;

class ProfessorFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'id',
        'document',
        'phone',
        'city',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('document', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('city', 'like', "%{$value}%")

                ->orWhereHas('user', function ($user) use ($value) {

                    $user
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                });
        });
    }
}

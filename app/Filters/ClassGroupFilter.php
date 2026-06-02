<?php

namespace App\Filters;

class ClassGroupFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'code',
        'capacity',
        'shift',
        'modality',
        'status',
        'created_at',
    ];

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query
                ->where('code', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")

                ->orWhereHas('subject', function ($subject) use ($value) {

                    $subject
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                })

                ->orWhereHas('professor', function ($professor) use ($value) {

                    $professor->where(
                        'name',
                        'like',
                        "%{$value}%"
                    );
                });
        });
    }

    public function modality(string $value): void
    {
        $this->query->where('modality', $value);
    }

    public function shift(string $value): void
    {
        $this->query->where('shift', $value);
    }

    public function status(string $value): void
    {
        $this->query->where('status', $value);
    }

    public function academic_period($value): void
    {
        $this->query->where(
            'academic_period_id',
            $value
        );
    }

    public function professor($value): void
    {
        $this->query->where(
            'professor_id',
            $value
        );
    }

    public function subject($value): void
    {
        $this->query->where(
            'subject_id',
            $value
        );
    }
}

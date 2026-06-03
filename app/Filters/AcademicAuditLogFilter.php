<?php

namespace App\Filters;

class AcademicAuditLogFilter extends BaseFilter
{
    protected array $allowedSorts = [
        'action',
        'created_at',
    ];

    protected string $defaultSort = 'created_at';

    public function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {
            $query
                ->where('action', 'like', "%{$value}%")
                ->orWhere('summary', 'like', "%{$value}%")
                ->orWhere('auditable_type', 'like', "%{$value}%")
                ->orWhere('auditable_id', 'like', "%{$value}%")
                ->orWhereHas('user', function ($user) use ($value) {
                    $user
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                });
        });
    }

    public function action(string $value): void
    {
        $this->query->where('action', $value);
    }

    public function user_id($value): void
    {
        $this->query->where('user_id', $value);
    }

    public function auditable_type(string $value): void
    {
        $this->query->where('auditable_type', $value);
    }

    public function date_from(string $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    public function date_to(string $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }
}

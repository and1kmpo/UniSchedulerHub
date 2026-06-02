<?php

namespace App\Services\Enrollment\DTOs;

class EnrollmentValidationResult
{
    public function __construct(
        public bool $allowed = true,
        public array $errors = [],
        public array $warnings = [],
        public array $conflicts = [],
        public array $meta = [],
    ) {}

    public function addError(string $message): void
    {
        $this->allowed = false;

        $this->errors[] = $message;
    }

    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function addConflict(array $conflict): void
    {
        $this->conflicts[] = $conflict;
    }

    public function addRecommendation(string|array $recommendation): void
    {
        $this->meta['recommendations'][] = $recommendation;
    }

    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'valid' => $this->allowed,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'conflicts' => $this->conflicts,
            'recommendations' => $this->meta['recommendations'] ?? [],
            'load' => $this->meta['load'] ?? [
                'credits' => 0,
                'groups' => 0,
                'weekly_hours' => 0,
            ],
            'waitlist' => $this->meta['waitlist'] ?? false,
            'available_slots' => $this->meta['available_slots'] ?? 0,
            'meta' => $this->meta,
        ];
    }
}

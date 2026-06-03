<?php

namespace App\Services;

use App\Models\AcademicAuditLog;
use Illuminate\Database\Eloquent\Model;

class AcademicAuditService
{
    public function record(
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?string $summary = null
    ): AcademicAuditLog {
        return AcademicAuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'action' => $action,
            'summary' => $summary,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}

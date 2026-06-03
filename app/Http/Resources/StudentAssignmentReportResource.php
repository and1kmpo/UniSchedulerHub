<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAssignmentReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student' => [
                'id' => $this->id,
                'document' => $this->document,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'semester' => $this->semester,
                'program' => $this->program?->name,
            ],
            'summary' => [
                'assignments_count' => $this->enrollments->count(),
                'active_credits' => $this->enrollments
                    ->filter(fn($enrollment) => in_array($enrollment->status?->code, config('enrollment.active_status_codes'), true))
                    ->sum(fn($enrollment) => $enrollment->subject?->credits ?? 0),
                'minimum_credits' => config('enrollment.min_credits', 7),
            ],
            'assignments' => $this->enrollments->map(fn($enrollment) => [
                'enrollment_id' => $enrollment->id,
                'status' => $enrollment->status?->code,
                'period' => $enrollment->academicPeriod?->name,
                'subject' => [
                    'id' => $enrollment->subject?->id,
                    'code' => $enrollment->subject?->code,
                    'name' => $enrollment->subject?->name,
                    'credits' => $enrollment->subject?->credits,
                    'knowledge_area' => $enrollment->subject?->knowledge_area,
                    'elective' => $enrollment->subject?->elective,
                ],
                'professor' => [
                    'id' => $enrollment->classGroup?->professor?->id,
                    'name' => $enrollment->classGroup?->professor?->name,
                    'email' => $enrollment->classGroup?->professor?->email,
                ],
                'class_group' => [
                    'id' => $enrollment->classGroup?->id,
                    'code' => $enrollment->classGroup?->code,
                    'name' => $enrollment->classGroup?->name,
                    'status' => $enrollment->classGroup?->status,
                ],
            ])->values(),
        ];
    }
}

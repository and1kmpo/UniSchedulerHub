<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => $this->whenLoaded('student', fn() => [
                'id' => $this->student?->id,
                'document' => $this->student?->document,
                'name' => $this->student?->user?->name,
                'email' => $this->student?->user?->email,
            ]),
            'subject' => $this->whenLoaded('subject', fn() => [
                'id' => $this->subject?->id,
                'code' => $this->subject?->code,
                'name' => $this->subject?->name,
                'credits' => $this->subject?->credits,
                'knowledge_area' => $this->subject?->knowledge_area,
                'elective' => $this->subject?->elective,
            ]),
            'academic_period' => $this->whenLoaded('academicPeriod', fn() => [
                'id' => $this->academicPeriod?->id,
                'name' => $this->academicPeriod?->name,
                'state' => $this->academicPeriod?->state()?->value,
            ]),
            'class_group' => $this->whenLoaded('classGroup', fn() => [
                'id' => $this->classGroup?->id,
                'code' => $this->classGroup?->code,
                'name' => $this->classGroup?->name,
                'status' => $this->classGroup?->status,
                'capacity' => $this->classGroup?->capacity,
                'modality' => $this->classGroup?->modality,
                'shift' => $this->classGroup?->shift,
                'professor' => $this->classGroup?->professor ? [
                    'id' => $this->classGroup->professor->id,
                    'name' => $this->classGroup->professor->name,
                    'email' => $this->classGroup->professor->email,
                ] : null,
            ]),
            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status?->id,
                'code' => $this->status?->code,
                'label' => $this->status?->label,
                'color' => $this->status?->color,
            ]),
            'enrolled_at' => $this->enrolled_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'name' => $this->user?->name,
            'phone' => $this->phone,
            'email' => $this->user?->email,
            'address' => $this->address,
            'city' => $this->city,
            'semester' => $this->semester,
            'academic_status' => $this->academic_status,
            'program' => $this->whenLoaded('program', fn() => [
                'id' => $this->program?->id,
                'name' => $this->program?->name,
            ]),
            'curriculum' => $this->whenLoaded('curriculum', fn() => [
                'id' => $this->curriculum?->id,
                'name' => $this->curriculum?->name,
                'code' => $this->curriculum?->code,
            ]),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

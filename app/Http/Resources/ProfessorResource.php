<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'document' => $this->document,
            'name' => $this->user?->name,
            'phone' => $this->phone,
            'email' => $this->user?->email,
            'address' => $this->address,
            'city' => $this->city,
            'subjects_count' => $this->whenCounted('subjects'),
            'class_groups_count' => $this->whenCounted('classGroups'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'credits' => $this->credits,
            'knowledge_area' => $this->knowledge_area,
            'elective' => $this->elective,
            'professors_count' => $this->whenCounted('professors'),
            'class_groups_count' => $this->whenCounted('classGroups'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $buildingId = $this->route('building')?->id ?? $this->route('building');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('buildings', 'name')
                    ->ignore($buildingId),
            ],

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('buildings', 'code')
                    ->ignore($buildingId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'code' => is_string($this->code) ? strtoupper(trim($this->code)) : $this->code,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
        ]);
    }
}

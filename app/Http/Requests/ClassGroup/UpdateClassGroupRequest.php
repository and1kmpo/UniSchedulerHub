<?php

namespace App\Http\Requests\ClassGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],

            'professor_id' => [
                'required',
                'exists:users,id',
            ],

            'capacity' => [
                'required',
                'integer',
                'min:1',
            ],

            'modality' => [
                'required',
                'string',
                'max:50',
            ],

            'shift' => [
                'required',
                'string',
                'max:50',
            ],

            'status' => [
                'nullable',
                Rule::in(['draft', 'published', 'cancelled', 'closed']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', 'published'),
        ]);
    }
}

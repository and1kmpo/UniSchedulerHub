<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore(request('subject'))],
            'description' => 'required|string|max:300',
            'credits' => 'required|integer|max:50',
            'knowledge_area' => 'required|string|max:100',
            'elective' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'name.max' => 'The name field must not be more than :max characters.',
            'description.string' => 'The description field must be a string.',
            'credits.required' => 'The credits field is required.',
            'credits.integer' => 'The credits field must be an integer.',
            'credits.max' => 'The credits field must not be greater than :max.',
            'knowledge_area.required' => 'The knowledge area field is required.',
            'knowledge_area.string' => 'The knowledge area field must be a string.',
            'knowledge_area.max' => 'The knowledge area field must not be more than :max characters.',
            'elective.required' => 'The elective field is required.',
            'elective.boolean' => 'The elective field must be a boolean value.',
        ];
    }
}

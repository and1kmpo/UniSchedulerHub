<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
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
        $programId = $this->route('program')?->id ?? $this->route('program');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('programs', 'name')->ignore($programId),
            ],
            'description' => ['required', 'string', 'max:600'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
        ]);
    }

    public function messages()
    {
        return [
            'name.required' => __('The program name is required.'),
            'name.string' => __('The program name must be a string.'),
            'name.max' => __('The program name must not exceed 100 characters.'),
            'name.unique' => __('The program name is already taken.'),
            'description.required' => __('The program description is required.'),
            'description.string' => __('The program description must be a string.'),
            'description.max' => __('The program description must not exceed 600 characters.'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        if (! $student instanceof Student && $student) {
            $student = Student::with('user')->find($student);
        }

        $user = $student?->user;

        return [
            'document' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'document')->ignore($student?->id),
            ],
            'name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:6',
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'integer', 'min:1', 'max:20'],
            'program_id' => ['required', 'exists:programs,id'],
            'curriculum_id' => ['nullable', 'exists:curricula,id'],
            'academic_status' => [
                'nullable',
                Rule::in(array_merge(
                    Student::ENROLLABLE_STATUSES,
                    Student::BLOCKED_STATUSES
                )),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => is_string($this->document) ? trim($this->document) : $this->document,
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
            'city' => is_string($this->city) ? trim($this->city) : $this->city,
        ]);
    }

    public function messages(): array
    {
        return [
            'document.required' => __('The document number is required.'),
            'document.string' => __('The document number must be a string.'),
            'document.max' => __('The document number must not exceed 20 characters.'),
            'document.unique' => __('The document number is already in use.'),
            'name.required' => __('The name is required.'),
            'name.string' => __('The name must be a string.'),
            'name.max' => __('The name must not exceed 50 characters.'),
            'phone.required' => __('The phone number is required.'),
            'phone.string' => __('The phone number must be a string.'),
            'phone.max' => __('The phone number must not exceed 15 characters.'),
            'email.required' => __('The email address is required.'),
            'email.email' => __('The email address must be a valid email.'),
            'email.unique' => __('The email address is already in use.'),
            'password.required' => __('The password is required.'),
            'password.min' => __('The password must be at least 6 characters.'),
            'address.required' => __('The address is required.'),
            'address.string' => __('The address must be a string.'),
            'address.max' => __('The address must not exceed 255 characters.'),
            'city.required' => __('The city is required.'),
            'city.string' => __('The city must be a string.'),
            'city.max' => __('The city must not exceed 50 characters.'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Professor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $professor = $this->route('professor');

        if (! $professor instanceof Professor && $professor) {
            $professor = Professor::with('user')->find($professor);
        }

        $user = $professor?->user;

        return [
            'document' => [
                'required',
                'string',
                'max:20',
                Rule::unique('professors', 'document')->ignore($professor?->id),
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

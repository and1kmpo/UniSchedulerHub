<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
    public function rules()
    {
        // Obtén el usuario desde la ruta (puede ser un ID de usuario)
        $user = User::with('student')->find($this->route('student'));

        // Asegúrate de que $user sea un objeto válido y obtén el ID del estudiante
        $studentId = $user?->student?->id;


        return [
            'document' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'document')->ignore($studentId),
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:15',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id ?? null)],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:50',
            'semester' => 'required|integer',
            'program_id' => 'required|exists:programs,id',
        ];
    }

    public function messages()
    {
        return [
            'document.required' => __('The document number is required.'),
            'document.string' => __('The document number must be a string.'),
            'document.max' => __('The document number must not exceed 20 characters.'),
            'document.unique' => __('The document number is already in use.'),
            'first_name.required' => __('The first name is required.'),
            'first_name.string' => __('The first name must be a string.'),
            'first_name.max' => __('The first name must not exceed 50 characters.'),
            'last_name.required' => __('The last name is required.'),
            'last_name.string' => __('The last name must be a string.'),
            'last_name.max' => __('The last name must not exceed 50 characters.'),
            'phone.required' => __('The phone number is required.'),
            'phone.string' => __('The phone number must be a string.'),
            'phone.max' => __('The phone number must not exceed 15 characters.'),
            'email.required' => __('The email address is required.'),
            'email.email' => __('The email address must be a valid email.'),
            'email.unique' => __('The email address is already in use.'),
            'address.required' => __('The address is required.'),
            'address.string' => __('The address must be a string.'),
            'address.max' => __('The address must not exceed 255 characters.'),
            'city.required' => __('The city is required.'),
            'city.string' => __('The city must be a string.'),
            'city.max' => __('The city must not exceed 50 characters.'),
        ];
    }
}

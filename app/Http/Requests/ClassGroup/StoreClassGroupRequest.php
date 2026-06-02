<?php

namespace App\Http\Requests\ClassGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreClassGroupRequest extends FormRequest
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

            'academic_period_id' => [
                'required',
                'exists:academic_periods,id',
            ],

            'schedules' => [
                'required',
                'array',
                'min:1',
            ],

            'schedules.*.day' => [
                'required',
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
            ],

            'schedules.*.start_time' => [
                'required',
                'date_format:H:i',
            ],

            'schedules.*.end_time' => [
                'required',
                'date_format:H:i',
            ],

            'schedules.*.status' => [
                'nullable',
                Rule::in(['draft', 'published', 'cancelled', 'closed']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', 'published'),
            'schedules' => collect($this->input('schedules', []))
                ->map(fn($schedule) => [
                    ...$schedule,
                    'status' => $schedule['status'] ?? 'published',
                ])
                ->all(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('schedules', []) as $index => $schedule) {
                if (
                    ! empty($schedule['start_time']) &&
                    ! empty($schedule['end_time']) &&
                    $schedule['end_time'] <= $schedule['start_time']
                ) {
                    $validator->errors()->add(
                        "schedules.{$index}.end_time",
                        'The end time must be after the start time.'
                    );
                }
            }
        });
    }
}

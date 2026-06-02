<?php

namespace Database\Factories;

use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $program = Program::query()->inRandomOrder()->first();
        $curriculum = $program
            ? Curriculum::query()
                ->where('program_id', $program->id)
                ->where('is_active', true)
                ->first()
            : null;

        return [
            'user_id' => User::factory(),
            'document' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'phone' => $this->faker->numberBetween(1000000000, 9999999999),
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'semester' => $this->faker->numberBetween(1, 10),
            'program_id' => $program?->id ?? Program::factory(),
            'curriculum_id' => $curriculum?->id,
            'academic_status' => $this->faker->randomElement(Student::ENROLLABLE_STATUSES),
        ];
    }
}

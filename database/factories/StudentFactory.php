<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $program = Program::factory()->create();

        return [
            'document' => $this->faker->unique()->numberBetween(10000, 9999999999),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->numberBetween(1000000000, 9999999999),
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'semester' => $this->faker->numberBetween(1, 10),
            'program_id' => $program->id,
        ];
    }
}

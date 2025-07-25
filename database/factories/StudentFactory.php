<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'phone' => $this->faker->numberBetween(1000000000, 9999999999),
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'semester' => $this->faker->numberBetween(1, 10),
            'program_id' => Program::inRandomOrder()->first()->id, // Selecciona un programa existente aleatoriamente
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Software Engineering',
                'Business Administration',
                'Industrial Engineering',
                'Data Science',
                'Information Systems',
            ]) . ' ' . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(12),
        ];
    }
}

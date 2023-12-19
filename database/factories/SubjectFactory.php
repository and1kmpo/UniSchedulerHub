<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();

        return [
            'name' => $faker->word,
            'description' => $faker->sentence,
            'credits' => $faker->numberBetween(1, 5),
            'knowledge_area' => $faker->word,
            'elective' => $faker->boolean,
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Professor>
 */
class ProfessorFactory extends Factory
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
            'document' => $faker->unique()->numberBetween(1000000, 9999999),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'phone' => $faker->numberBetween(1000000000, 9999999999),
            'email' => $faker->unique()->safeEmail,
            'address' => $faker->address,
            'city' => $faker->city,
        ];
    }
}

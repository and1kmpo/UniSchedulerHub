<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Engineering Building',
                'Science Building',
                'Main Campus Block',
                'Innovation Center',
                'Academic Tower',
            ]) . ' ' . $this->faker->unique()->numberBetween(1, 9),
            'code' => $this->faker->unique()->bothify('B###'),
            'description' => $this->faker->sentence(10),
        ];
    }
}

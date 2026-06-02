<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->bothify('Room ###'),
            'building_id' => Building::query()->inRandomOrder()->first()?->id ?? Building::factory(),
            'floor' => $this->faker->numberBetween(1, 5),
            'capacity' => $this->faker->numberBetween(20, 45),
            'description' => $this->faker->sentence(8),
            'status' => 'active',
        ];
    }
}

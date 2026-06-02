<?php

namespace Database\Factories;

use App\Models\Curriculum;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurriculumFactory extends Factory
{
    protected $model = Curriculum::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::query()->inRandomOrder()->first()?->id ?? Program::factory(),
            'code' => $this->faker->unique()->bothify('CUR-####'),
            'name' => $this->faker->words(3, true) . ' Curriculum',
            'valid_from' => now()->startOfYear()->toDateString(),
            'valid_to' => null,
            'is_active' => true,
        ];
    }
}

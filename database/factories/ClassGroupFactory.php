<?php

namespace Database\Factories;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassGroupFactory extends Factory
{
    protected $model = ClassGroup::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('GRP[0-9]{3}'),
            'name' => $this->faker->words(3, true),
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? Subject::factory(),
            'professor_id' => User::role('professor')->inRandomOrder()->first()?->id ?? User::factory(),
            'semester' => $this->faker->randomElement(['2025-I', '2025-II']),
            'group_code' => $this->faker->unique()->regexify('G[1-9][A-Z]'),
            'capacity' => $this->faker->numberBetween(20, 50),
        ];
    }
}

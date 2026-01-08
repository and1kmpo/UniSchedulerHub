<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
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
            'code' => fake()->unique()->regexify('GRP[0-9]{3}'),
            'name' => fake()->words(3, true),
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? Subject::factory(),
            'professor_id' => User::role('professor')->inRandomOrder()->first()?->id ?? User::factory(),
            'academic_period_id' => function () {
                $active = AcademicPeriod::where('is_active', true)->pluck('id')->toArray();
                $inactive = AcademicPeriod::where('is_active', false)->pluck('id')->toArray();

                return fake()->boolean(80)
                    ? fake()->randomElement($active)
                    : fake()->randomElement($inactive);
            },
            'semester' => fake()->randomElement(['2025-I', '2025-II']),
            'group_code' => fake()->unique()->regexify('G[1-9][A-Z]'),
            'capacity' => fake()->numberBetween(20, 50),
        ];
    }
}

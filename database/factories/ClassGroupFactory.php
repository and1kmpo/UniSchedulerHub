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
        $activePeriod = AcademicPeriod::where('is_active', true)->inRandomOrder()->first();
        $anyPeriod = AcademicPeriod::inRandomOrder()->first();

        return [
            'code' => fake()->unique()->regexify('GRP[0-9]{3}'),
            'name' => fake()->words(3, true),
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? Subject::factory(),
            'professor_id' => User::role('professor')->inRandomOrder()->first()?->id ?? User::factory(),
            'academic_period_id' => $activePeriod?->id ?? $anyPeriod?->id ?? AcademicPeriod::factory(),
            'semester' => fake()->randomElement(['2025-I', '2025-II']),
            'group_code' => fake()->unique()->regexify('G[1-9][A-Z]'),
            'capacity' => fake()->numberBetween(20, 50),
            'modality' => fake()->randomElement(['In-person', 'Hybrid', 'Virtual']),
            'shift' => fake()->randomElement(['Day', 'Night']),
            'status' => ClassGroup::STATUS_PUBLISHED,
        ];
    }
}

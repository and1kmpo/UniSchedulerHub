<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicPeriodFactory extends Factory
{
    protected $model = AcademicPeriod::class;

    public function definition(): array
    {
        $statusId = AcademicPeriodStatus::query()
            ->where('code', 'enrollment_open')
            ->value('id');

        return [
            'name' => now()->year . '-' . $this->faker->randomElement(['I', 'II']),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->addMonths(5)->endOfMonth()->toDateString(),
            'enrollment_deadline' => now()->addWeeks(3)->toDateString(),
            'unenrollment_deadline' => now()->addWeeks(6)->toDateString(),
            'academic_period_status_id' => $statusId,
            'is_active' => true,
        ];
    }
}

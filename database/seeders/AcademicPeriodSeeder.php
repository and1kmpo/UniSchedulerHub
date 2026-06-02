<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;

class AcademicPeriodSeeder extends Seeder
{
    public function run()
    {
        $closedStatus = AcademicPeriodStatus::where('code', 'academically_closed')->value('id');
        $openStatus = AcademicPeriodStatus::where('code', 'enrollment_open')->value('id');

        AcademicPeriod::updateOrCreate(
            ['name' => '2025-I'],
            [
                'start_date' => '2025-01-01',
                'end_date' => '2025-06-30',
                'enrollment_deadline' => '2025-01-15',
                'unenrollment_deadline' => '2025-02-15',
                'academic_period_status_id' => $closedStatus,
                'is_active' => false,
            ]
        );

        AcademicPeriod::updateOrCreate(
            ['name' => '2026-II'],
            [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addMonths(5)->endOfMonth()->toDateString(),
                'enrollment_deadline' => now()->addWeeks(3)->toDateString(),
                'unenrollment_deadline' => now()->addWeeks(6)->toDateString(),
                'academic_period_status_id' => $openStatus,
                'is_active' => true,
            ]
        );
    }
}

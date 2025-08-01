<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicPeriod;

class AcademicPeriodSeeder extends Seeder
{
    public function run()
    {
        AcademicPeriod::create([
            'name' => '2025-I',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'enrollment_deadline' => '2025-01-01',
            'unenrollment_deadline' => '2025-01-15',
            'is_active' => false,
        ]);

        AcademicPeriod::create([
            'name' => '2025-II',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'enrollment_deadline' => '2025-07-01',
            'unenrollment_deadline' => '2025-07-15',
            'is_active' => true,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicPeriodStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'draft', 'name' => 'Draft', 'is_final' => false],
            ['code' => 'enrollment_open', 'name' => 'Enrollment Open', 'is_final' => false],
            ['code' => 'enrollment_closed', 'name' => 'Enrollment Closed', 'is_final' => false],
            ['code' => 'in_progress', 'name' => 'In Progress', 'is_final' => false],
            ['code' => 'academically_closed', 'name' => 'Academically Closed', 'is_final' => true],
            ['code' => 'archived', 'name' => 'Archived', 'is_final' => true],
        ];

        foreach ($statuses as $status) {
            DB::table('academic_period_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }
    }
}

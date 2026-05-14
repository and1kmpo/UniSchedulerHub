<?php

namespace Database\Seeders;

use App\Models\AcademicPeriodStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicPeriodStatusTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $map = AcademicPeriodStatus::pluck('id', 'code');

        $transitions = [
            ['draft', 'enrollment_open'],
            ['enrollment_open', 'draft'],
            ['enrollment_open', 'enrollment_closed'],
            ['enrollment_closed', 'in_progress'],
            ['in_progress', 'academically_closed'],
            ['academically_closed', 'archived'],
        ];

        foreach ($transitions as [$from, $to]) {
            DB::table('academic_period_status_transitions')->insert([
                'from_status_id' => $map[$from],
                'to_status_id'   => $map[$to],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}

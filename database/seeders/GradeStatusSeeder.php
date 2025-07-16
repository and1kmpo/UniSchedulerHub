<?php

namespace Database\Seeders;

use App\Models\GradeStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GradeStatus::insert([
            ['code' => 'passed', 'label' => 'Passed all course requirements'],
            ['code' => 'failed', 'label' => 'Did not achieve the minimum grade'],
            ['code' => 'failed_attendance', 'label' => 'Did not meet minimum attendance'],
        ]);
    }
}

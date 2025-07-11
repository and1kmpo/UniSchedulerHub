<?php

namespace Database\Seeders;

use App\Models\GradeState;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GradeState::insert([
            ['code' => 'passed', 'label' => 'Passed all course requirements'],
            ['code' => 'failed', 'label' => 'Did not achieve the minimum grade'],
            ['code' => 'failed_attendance', 'label' => 'Did not meet minimum attendance'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\SubjectEnrollmentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectEnrollmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubjectEnrollmentStatus::insert([
            [
                'code' => 'enrolled',
                'description' => 'The student is currently enrolled in the subject.',
                'color' => 'blue',
            ],
            [
                'code' => 'approved',
                'description' => 'The student has successfully passed the subject.',
                'color' => 'green',
            ],
            [
                'code' => 'failed',
                'description' => 'The student failed the subject.',
                'color' => 'red',
            ],
            [
                'code' => 'withdrawn',
                'description' => 'The student withdrew from the subject.',
                'color' => 'gray',
            ],
            [
                'code' => 'revalidation',
                'description' => 'The subject was validated by external means or past studies.',
                'color' => 'purple',
            ],
        ]);
    }
}

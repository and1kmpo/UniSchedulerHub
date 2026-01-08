<?php

namespace Database\Seeders;

use App\Models\SubjectEnrollmentStatus;
use Illuminate\Database\Seeder;

class SubjectEnrollmentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'pre_enrolled',
                'description' => 'The student has pre-enrolled in the subject, pending confirmation.',
                'color' => 'yellow',
            ],
            [
                'code' => 'enrolled',
                'description' => 'The student is currently enrolled in the subject.',
                'color' => 'blue',
            ],
            [
                'code' => 'cancelled',
                'description' => 'The subject or group was cancelled.',
                'color' => 'black',
            ],
            [
                'code' => 'withdrawn',
                'description' => 'The student withdrew from the subject.',
                'color' => 'gray',
            ],
            [
                'code' => 'failed',
                'description' => 'The student failed the subject.',
                'color' => 'red',
            ],
            [
                'code' => 'approved',
                'description' => 'The student successfully passed the subject.',
                'color' => 'green',
            ],
            [
                'code' => 'revalidation',
                'description' => 'The subject was validated by external means or past studies.',
                'color' => 'purple',
            ],
        ];

        SubjectEnrollmentStatus::upsert($statuses, ['code'], ['description', 'color']);
    }
}

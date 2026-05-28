<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectEnrollmentStatus;
use App\Models\SubjectEnrollmentStatusTransition;

class SubjectEnrollmentStatusTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = SubjectEnrollmentStatus::all()
            ->keyBy('code');

        $transitions = [
            // Pre-enrollment flow
            ['from' => 'pre_enrolled', 'to' => 'enrolled'],
            ['from' => 'pre_enrolled', 'to' => 'cancelled'],

            // Academic flow
            ['from' => 'enrolled', 'to' => 'withdrawn'],
            ['from' => 'enrolled', 'to' => 'failed'],
            ['from' => 'enrolled', 'to' => 'approved'],
        ];

        foreach ($transitions as $transition) {
            SubjectEnrollmentStatusTransition::updateOrCreate(
                [
                    'from_status_id' => $statuses[$transition['from']]->id,
                    'to_status_id'   => $statuses[$transition['to']]->id,
                ],
                []
            );
        }
    }
}

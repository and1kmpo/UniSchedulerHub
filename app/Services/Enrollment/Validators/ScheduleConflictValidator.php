<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class ScheduleConflictValidator
{
    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $studentEnrollments = $student
            ->subjectEnrollments()
            ->with('classGroup.subject', 'classGroup.schedules')
            ->where('academic_period_id', $group->academic_period_id)
            ->get();

        foreach ($studentEnrollments as $enrollment) {

            foreach ($enrollment->classGroup->schedules as $existing) {
                if ($existing->status === 'cancelled') {
                    continue;
                }

                foreach ($group->schedules as $incoming) {
                    if ($incoming->status === 'cancelled') {
                        continue;
                    }

                    if (
                        $existing->day === $incoming->day &&
                        $existing->start_time < $incoming->end_time &&
                        $existing->end_time > $incoming->start_time
                    ) {

                        $result->addConflict([
                            'type' => 'schedule_overlap',

                            'subject' => $enrollment
                                ->classGroup
                                ->subject
                                ->name,

                            'day' => $existing->day,

                            'existing' => [
                                'start' => $existing->start_time,
                                'end' => $existing->end_time,
                            ],

                            'incoming' => [
                                'start' => $incoming->start_time,
                                'end' => $incoming->end_time,
                            ],
                        ]);

                        $result->addError('Schedule conflict detected.');

                        return;
                    }
                }
            }
        }
    }
}

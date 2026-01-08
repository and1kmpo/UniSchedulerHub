<?php

namespace App\Services;

use App\Models\SubjectEnrollment;
use App\Models\ClassGroup;
use App\Models\Student;

class EnrollmentService
{
    public function canEnroll(Student $student, ClassGroup $group): array
    {
        // 1️⃣ Already enrolled in this subject
        $alreadyEnrolled = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $group->subject_id)
            ->where('academic_period_id', $group->academic_period_id)
            ->exists();

        if ($alreadyEnrolled) {
            return [
                'allowed' => false,
                'message' => 'Student is already enrolled in this subject for the current academic period.',
            ];
        }

        // 2️⃣ Group capacity reached
        $currentEnrollments = SubjectEnrollment::where('class_group_id', $group->id)
            ->count();

        if ($group->capacity !== null && $currentEnrollments >= $group->capacity) {
            return [
                'allowed' => false,
                'message' => 'This class group has reached its maximum capacity.',
            ];
        }

        // 3️⃣ Schedule conflict validation
        $studentEnrollments = SubjectEnrollment::with('classGroup.schedules')
            ->where('student_id', $student->id)
            ->where('academic_period_id', $group->academic_period_id)
            ->get();

        foreach ($studentEnrollments as $enrollment) {
            foreach ($enrollment->classGroup->schedules as $existingSchedule) {
                foreach ($group->schedules as $newSchedule) {

                    if (
                        $existingSchedule->day === $newSchedule->day &&
                        $this->schedulesOverlap($existingSchedule, $newSchedule)
                    ) {
                        return [
                            'allowed' => false,
                            'message' => 'Schedule conflict with another enrolled class group.',
                        ];
                    }
                }
            }
        }


        return [
            'allowed' => true,
            'message' => 'Enrollment allowed.',
        ];
    }

    private function schedulesOverlap($a, $b): bool
    {
        return $a->start_time < $b->end_time
            && $a->end_time > $b->start_time;
    }
}

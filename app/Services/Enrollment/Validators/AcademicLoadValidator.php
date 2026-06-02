<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class AcademicLoadValidator
{
    protected int $maxCredits = 21;

    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $currentCredits = $student
            ->subjectEnrollments()
            ->with('classGroup.subject')
            ->where('academic_period_id', $group->academic_period_id)
            ->get()
            ->sum(
                fn($enrollment) =>
                $enrollment->classGroup?->subject?->credits ?? 0
            );

        $incomingCredits = $group->subject?->credits ?? 0;

        $total = $currentCredits + $incomingCredits;

        if ($total > $this->maxCredits) {

            $result->addWarning(
                "Academic load exceeds {$this->maxCredits} credits."
            );
        }

        $currentGroups = $student
            ->subjectEnrollments()
            ->where('academic_period_id', $group->academic_period_id)
            ->count();

        $weeklyHours = $student
            ->subjectEnrollments()
            ->with('classGroup.schedules')
            ->where('academic_period_id', $group->academic_period_id)
            ->get()
            ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? [])
            ->sum(fn($schedule) => $this->hoursBetween($schedule->start_time, $schedule->end_time));

        $incomingHours = $group->schedules
            ->sum(fn($schedule) => $this->hoursBetween($schedule->start_time, $schedule->end_time));

        $result->meta['load'] = [
            'credits' => $total,
            'groups' => $currentGroups + 1,
            'weekly_hours' => $weeklyHours + $incomingHours,
        ];

        $result->meta['current_credits'] = $currentCredits;
        $result->meta['incoming_credits'] = $incomingCredits;
        $result->meta['projected_credits'] = $total;
    }

    private function hoursBetween(string $start, string $end): float|int
    {
        [$startHour, $startMinute] = array_map('intval', explode(':', $start));
        [$endHour, $endMinute] = array_map('intval', explode(':', $end));

        return max(0, (($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute)) / 60);
    }
}

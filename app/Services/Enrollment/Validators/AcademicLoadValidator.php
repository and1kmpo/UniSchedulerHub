<?php

namespace App\Services\Enrollment\Validators;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Services\Enrollment\DTOs\EnrollmentValidationResult;

class AcademicLoadValidator
{
    public function __construct(
        protected ?int $ignoreEnrollmentId = null,
    ) {}

    public function validate(
        Student $student,
        ClassGroup $group,
        EnrollmentValidationResult $result
    ): void {

        $currentCredits = $student
            ->subjectEnrollments()
            ->with('classGroup.subject')
            ->where('academic_period_id', $group->academic_period_id)
            ->when(
                $this->ignoreEnrollmentId,
                fn($query) => $query->where('id', '!=', $this->ignoreEnrollmentId)
            )
            ->whereHas(
                'status',
                fn($query) => $query->whereIn(
                    'code',
                    config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled'])
                )
            )
            ->get()
            ->sum(
                fn($enrollment) =>
                $enrollment->classGroup?->subject?->credits ?? 0
            );

        $incomingCredits = $group->subject?->credits ?? 0;

        $total = $currentCredits + $incomingCredits;
        $minCredits = config('enrollment.min_credits', 7);
        $maxCredits = config('enrollment.max_credits', 21);

        if ($total > $maxCredits) {

            $result->addWarning(
                "Academic load exceeds {$maxCredits} credits."
            );
        }

        if ($total < $minCredits) {
            $result->addWarning(
                "Academic load is below the minimum expected {$minCredits} credits."
            );
        }

        $currentGroups = $student
            ->subjectEnrollments()
            ->where('academic_period_id', $group->academic_period_id)
            ->when(
                $this->ignoreEnrollmentId,
                fn($query) => $query->where('id', '!=', $this->ignoreEnrollmentId)
            )
            ->whereHas(
                'status',
                fn($query) => $query->whereIn(
                    'code',
                    config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled'])
                )
            )
            ->count();

        $weeklyHours = $student
            ->subjectEnrollments()
            ->with('classGroup.schedules')
            ->where('academic_period_id', $group->academic_period_id)
            ->when(
                $this->ignoreEnrollmentId,
                fn($query) => $query->where('id', '!=', $this->ignoreEnrollmentId)
            )
            ->whereHas(
                'status',
                fn($query) => $query->whereIn(
                    'code',
                    config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled'])
                )
            )
            ->get()
            ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? [])
            ->sum(fn($schedule) => $this->hoursBetween($schedule->start_time, $schedule->end_time));

        $incomingHours = $group->schedules
            ->sum(fn($schedule) => $this->hoursBetween($schedule->start_time, $schedule->end_time));

        $result->meta['load'] = [
            'credits' => $total,
            'groups' => $currentGroups + 1,
            'weekly_hours' => $weeklyHours + $incomingHours,
            'min_credits' => $minCredits,
            'max_credits' => $maxCredits,
            'meets_minimum' => $total >= $minCredits,
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

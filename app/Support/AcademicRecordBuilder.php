<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Collection;

class AcademicRecordBuilder
{
    public static function forStudent(Student $student): array
    {
        $enrollments = $student->enrollments()
            ->with([
                'subject:id,code,name,credits',
                'academicPeriod:id,name,start_date,end_date',
                'status',
                'classGroup:id,code,name,professor_id',
                'classGroup.professor:id,name',
                'grade.state:id,code,label',
            ])
            ->get()
            ->sortBy([
                fn ($a, $b) => ($b->academicPeriod?->start_date ?? $b->academicPeriod?->created_at ?? $b->created_at) <=> ($a->academicPeriod?->start_date ?? $a->academicPeriod?->created_at ?? $a->created_at),
                fn ($a, $b) => ($a->subject?->name ?? '') <=> ($b->subject?->name ?? ''),
            ])
            ->values();

        $periods = $enrollments
            ->groupBy(fn ($enrollment) => $enrollment->academic_period_id ?: 'no-period')
            ->map(fn (Collection $periodEnrollments) => self::periodPayload($periodEnrollments))
            ->values();

        $allCourses = $periods->flatMap(fn ($period) => $period['courses']);
        $gradedCourses = $allCourses->filter(fn ($course) => $course['final_grade'] !== null);
        $attemptedCredits = $allCourses->sum('attempted_credits');
        $approvedCredits = $allCourses->sum('approved_credits');
        $weightedGradePoints = $gradedCourses->sum(fn ($course) => $course['final_grade'] * max(1, $course['credits']));
        $gradedCredits = $gradedCourses->sum(fn ($course) => max(1, $course['credits']));

        return [
            'summary' => [
                'periods' => $periods->count(),
                'subjects' => $allCourses->count(),
                'attempted_credits' => $attemptedCredits,
                'approved_credits' => $approvedCredits,
                'pending_subjects' => $allCourses->where('final_grade', null)->count(),
                'weighted_average' => $gradedCredits > 0 ? round($weightedGradePoints / $gradedCredits, 2) : null,
                'completion_rate' => $attemptedCredits > 0 ? round(($approvedCredits / $attemptedCredits) * 100, 1) : 0,
            ],
            'periods' => $periods,
        ];
    }

    private static function periodPayload(Collection $enrollments): array
    {
        $period = $enrollments->first()?->academicPeriod;
        $courses = $enrollments
            ->map(fn ($enrollment) => self::coursePayload($enrollment))
            ->values();

        $gradedCourses = $courses->filter(fn ($course) => $course['final_grade'] !== null);
        $attemptedCredits = $courses->sum('attempted_credits');
        $approvedCredits = $courses->sum('approved_credits');
        $weightedGradePoints = $gradedCourses->sum(fn ($course) => $course['final_grade'] * max(1, $course['credits']));
        $gradedCredits = $gradedCourses->sum(fn ($course) => max(1, $course['credits']));

        return [
            'id' => $period?->id,
            'name' => $period?->name ?? 'No academic period',
            'start_date' => $period?->start_date?->toDateString(),
            'end_date' => $period?->end_date?->toDateString(),
            'attempted_credits' => $attemptedCredits,
            'approved_credits' => $approvedCredits,
            'subjects' => $courses->count(),
            'weighted_average' => $gradedCredits > 0 ? round($weightedGradePoints / $gradedCredits, 2) : null,
            'courses' => $courses,
        ];
    }

    private static function coursePayload($enrollment): array
    {
        $credits = (int) ($enrollment->subject?->credits ?? 0);
        $gradeState = $enrollment->grade?->state?->code;
        $enrollmentState = $enrollment->status?->code;
        $isCancelled = in_array($enrollmentState, ['cancelled', 'withdrawn'], true);
        $isApproved = $gradeState === 'passed' || $enrollmentState === 'approved';

        return [
            'id' => $enrollment->id,
            'subject_code' => $enrollment->subject?->code,
            'subject_name' => $enrollment->subject?->name,
            'credits' => $credits,
            'attempted_credits' => $isCancelled ? 0 : $credits,
            'approved_credits' => $isApproved ? $credits : 0,
            'group' => $enrollment->classGroup?->code ?? $enrollment->classGroup?->name,
            'professor' => $enrollment->classGroup?->professor?->name ?? 'Unassigned',
            'enrollment_status' => $enrollment->status?->code,
            'enrollment_status_label' => self::statusLabel($enrollment->status?->description ?? $enrollmentState),
            'grade_status' => $gradeState,
            'grade_status_label' => $enrollment->grade?->state?->label,
            'partial_1' => $enrollment->grade?->partial_1,
            'partial_2' => $enrollment->grade?->partial_2,
            'partial_3' => $enrollment->grade?->partial_3,
            'activities' => $enrollment->grade?->activities,
            'attendance' => $enrollment->grade?->attendance,
            'final_grade' => $enrollment->grade?->final_grade !== null
                ? (float) $enrollment->grade->final_grade
                : null,
        ];
    }

    private static function statusLabel(?string $status): ?string
    {
        return $status
            ? str($status)->replace('_', ' ')->title()->toString()
            : null;
    }
}

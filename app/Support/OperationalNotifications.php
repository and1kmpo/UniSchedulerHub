<?php

namespace App\Support;

use App\Models\AcademicAuditLog;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use Illuminate\Support\Collection;

class OperationalNotifications
{
    public static function for(?User $user): array
    {
        if (! $user) {
            return [
                'items' => [],
                'unread_count' => 0,
            ];
        }

        $items = collect();
        $activePeriod = AcademicPeriod::with('status')->active()->latest('id')->first();
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes'))
            ->pluck('id');

        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            $items = $items
                ->merge(self::administrativeItems($activePeriod, $activeStatusIds))
                ->merge(self::recentAuditItems());
        } elseif ($user->hasRole('professor')) {
            $items = $items->merge(self::professorItems($user, $activePeriod, $activeStatusIds));
        } elseif ($user->hasRole('student')) {
            $items = $items->merge(self::studentItems($user, $activePeriod, $activeStatusIds));
        }

        $items = $items->take(6)->values();

        return [
            'items' => $items,
            'unread_count' => $items->count(),
        ];
    }

    private static function administrativeItems(?AcademicPeriod $activePeriod, Collection $activeStatusIds): Collection
    {
        $items = collect();

        if (! $activePeriod) {
            return $items->push(self::item(
                'period-missing',
                'No active academic period',
                'Activate an academic period to synchronize enrollments, schedules and reports.',
                'warning',
                'fa-solid fa-calendar-xmark',
                'academic-periods.index'
            ));
        }

        $deadlineItem = self::deadlineItem($activePeriod, 'academic-periods.index');
        if ($deadlineItem) {
            $items->push($deadlineItem);
        }

        $groupsWithoutSchedules = ClassGroup::query()
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->where('academic_period_id', $activePeriod->id)
            ->whereDoesntHave('schedules', fn ($query) => $query->where('status', ClassSchedule::STATUS_PUBLISHED))
            ->count();

        if ($groupsWithoutSchedules > 0) {
            $items->push(self::item(
                'groups-without-schedules',
                "{$groupsWithoutSchedules} published groups without schedule",
                'Complete schedule blocks before students and professors rely on weekly planning.',
                'warning',
                'fa-solid fa-calendar-plus',
                'class-groups.index'
            ));
        }

        $capacityAlerts = ClassGroup::query()
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->where('academic_period_id', $activePeriod->id)
            ->where('capacity', '>', 0)
            ->withCount([
                'subjectEnrollments as active_enrollments_count' => fn ($query) => $query->whereIn('status_id', $activeStatusIds),
            ])
            ->get()
            ->filter(fn ($group) => $group->active_enrollments_count >= ($group->capacity * 0.9))
            ->count();

        if ($capacityAlerts > 0) {
            $items->push(self::item(
                'capacity-alerts',
                "{$capacityAlerts} groups near or at capacity",
                'Review available seats and enrollment pressure by group.',
                'danger',
                'fa-solid fa-triangle-exclamation',
                'reports.group-capacity-conflicts.index'
            ));
        }

        return $items;
    }

    private static function professorItems(User $user, ?AcademicPeriod $activePeriod, Collection $activeStatusIds): Collection
    {
        $items = collect();

        if (! $activePeriod) {
            return $items;
        }

        $groupsWithoutSchedules = ClassGroup::query()
            ->where('professor_id', $user->id)
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->where('academic_period_id', $activePeriod->id)
            ->whereDoesntHave('schedules', fn ($query) => $query->where('status', ClassSchedule::STATUS_PUBLISHED))
            ->count();

        if ($groupsWithoutSchedules > 0) {
            $items->push(self::item(
                'professor-groups-without-schedules',
                "{$groupsWithoutSchedules} assigned groups need schedule",
                'Coordinate weekly schedule blocks so students can plan their academic load.',
                'warning',
                'fa-solid fa-calendar-plus',
                'professor.schedule'
            ));
        }

        $activeEnrollments = ClassGroup::query()
            ->where('professor_id', $user->id)
            ->where('academic_period_id', $activePeriod->id)
            ->withCount([
                'subjectEnrollments as active_enrollments_count' => fn ($query) => $query->whereIn('status_id', $activeStatusIds),
                'subjectEnrollments as graded_enrollments_count' => fn ($query) => $query
                    ->whereIn('status_id', $activeStatusIds)
                    ->whereHas('grade'),
            ])
            ->get();

        $pendingGrades = $activeEnrollments->sum(fn ($group) => max(0, $group->active_enrollments_count - $group->graded_enrollments_count));

        if ($pendingGrades > 0 && $activePeriod->canEditGrades()) {
            $items->push(self::item(
                'pending-grades',
                "{$pendingGrades} grade records pending",
                'Complete grade capture while the academic period allows grade edition.',
                'info',
                'fa-solid fa-clipboard-check',
                'admin.group-enrollments.index'
            ));
        }

        return $items;
    }

    private static function studentItems(User $user, ?AcademicPeriod $activePeriod, Collection $activeStatusIds): Collection
    {
        $items = collect();
        $student = $user->student;

        if (! $activePeriod || ! $student) {
            return $items;
        }

        $deadlineItem = self::deadlineItem($activePeriod, 'student.subject-enrollment.index');
        if ($deadlineItem && $activePeriod->canEnroll()) {
            $items->push($deadlineItem);
        }

        $activeEnrollments = $student->subjectEnrollments()
            ->with('subject')
            ->where('academic_period_id', $activePeriod->id)
            ->whereIn('status_id', $activeStatusIds)
            ->get();

        $credits = $activeEnrollments->sum(fn ($enrollment) => (int) ($enrollment->subject?->credits ?? 0));
        $minimumCredits = (int) config('enrollment.min_credits');

        if ($credits === 0) {
            $items->push(self::item(
                'student-no-enrollments',
                'No active subjects enrolled',
                'Select available groups to start building your weekly schedule.',
                'info',
                'fa-solid fa-route',
                'student.subject-enrollment.index'
            ));
        } elseif ($credits < $minimumCredits) {
            $items->push(self::item(
                'student-incomplete-load',
                'Academic load below minimum',
                "{$credits} of {$minimumCredits} required credits are currently active.",
                'warning',
                'fa-solid fa-gauge-simple-low',
                'student.subject-enrollment.index'
            ));
        }

        return $items;
    }

    private static function recentAuditItems(): Collection
    {
        return AcademicAuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->take(2)
            ->get()
            ->map(fn ($log) => self::item(
                "audit-{$log->id}",
                'Recent academic event',
                trim(($log->summary ?: $log->action) . ($log->user ? " by {$log->user->name}" : '')),
                'info',
                'fa-solid fa-clock-rotate-left',
                'academic-audit-logs.index'
            ));
    }

    private static function deadlineItem(AcademicPeriod $period, string $route): ?array
    {
        if (! $period->enrollment_deadline) {
            return null;
        }

        $daysLeft = now()->startOfDay()->diffInDays($period->enrollment_deadline->startOfDay(), false);

        if ($daysLeft < 0 || $daysLeft > 7) {
            return null;
        }

        $label = $daysLeft === 0 ? 'today' : "in {$daysLeft} days";

        return self::item(
            'enrollment-deadline',
            "Enrollment deadline {$label}",
            "The active period {$period->name} is approaching its enrollment deadline.",
            $daysLeft <= 2 ? 'warning' : 'info',
            'fa-solid fa-hourglass-half',
            $route
        );
    }

    private static function item(
        string $id,
        string $title,
        string $description,
        string $severity,
        string $icon,
        ?string $route = null
    ): array {
        return compact('id', 'title', 'description', 'severity', 'icon', 'route');
    }
}

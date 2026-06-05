<?php

namespace App\Http\Controllers;

use App\Models\AcademicAuditLog;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();

        if ($user->hasRole('professor')) {
            return Inertia::render('Dashboard', [
                'dashboardType' => 'professor',
                'professorDashboard' => $this->professorDashboard($user->id),
            ]);
        }

        return Inertia::render('Dashboard', [
            'dashboardType' => 'academic',
            'academicDashboard' => $this->academicDashboard(),
        ]);
    }

    private function academicDashboard(): array
    {
        $activePeriod = AcademicPeriod::with('status')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes'))
            ->pluck('id');

        $groupScope = ClassGroup::query()
            ->when($activePeriod, fn($query) => $query->where('academic_period_id', $activePeriod->id));

        $activeEnrollmentScope = SubjectEnrollment::query()
            ->whereIn('status_id', $activeStatusIds)
            ->when($activePeriod, fn($query) => $query->where('academic_period_id', $activePeriod->id));

        $publishedGroups = (clone $groupScope)
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->count();

        $activeEnrollments = (clone $activeEnrollmentScope)->count();

        $capacity = (clone $groupScope)
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->sum('capacity');

        $utilization = $capacity > 0
            ? round(($activeEnrollments / $capacity) * 100, 1)
            : 0;

        $groupsWithCounts = ClassGroup::query()
            ->select('class_groups.*')
            ->selectSub(function ($query) use ($activeStatusIds) {
                $query->from('subject_enrollments')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('subject_enrollments.class_group_id', 'class_groups.id')
                    ->whereIn('subject_enrollments.status_id', $activeStatusIds);
            }, 'active_enrollments_count')
            ->with(['subject:id,name,code', 'professor:id,name', 'academicPeriod:id,name'])
            ->when($activePeriod, fn($query) => $query->where('academic_period_id', $activePeriod->id))
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->orderByDesc('active_enrollments_count')
            ->get();

        $fullGroups = $groupsWithCounts
            ->filter(fn($group) => $group->capacity > 0 && $group->active_enrollments_count >= $group->capacity)
            ->count();

        $highOccupancyGroups = $groupsWithCounts
            ->filter(fn($group) => $group->capacity > 0 && ($group->active_enrollments_count / $group->capacity) >= 0.9)
            ->take(6)
            ->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'subject' => $group->subject?->name,
                'enrollments' => (int) $group->active_enrollments_count,
                'capacity' => (int) $group->capacity,
                'occupancy' => $group->capacity > 0
                    ? round(($group->active_enrollments_count / $group->capacity) * 100, 1)
                    : 0,
            ])
            ->values();

        return [
            'activePeriod' => $activePeriod ? [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
                'status' => $activePeriod->status?->code,
                'status_label' => $activePeriod->status?->description ?? Str::headline($activePeriod->status?->code ?? ''),
            ] : null,
            'metrics' => [
                'active_enrollments' => $activeEnrollments,
                'published_groups' => $publishedGroups,
                'schedule_conflicts' => $this->scheduleConflictCount($activePeriod?->id),
                'capacity_utilization' => $utilization,
                'full_groups' => $fullGroups,
                'professors_with_groups' => (clone $groupScope)->whereNotNull('professor_id')->distinct('professor_id')->count('professor_id'),
            ],
            'capacity' => [
                'total_capacity' => (int) $capacity,
                'used_seats' => $activeEnrollments,
                'available_seats' => max(0, $capacity - $activeEnrollments),
                'high_occupancy_groups' => $highOccupancyGroups,
            ],
            'enrollmentStatus' => $this->enrollmentStatusBreakdown($activePeriod?->id),
            'professorLoad' => $this->professorLoad($activePeriod?->id, $activeStatusIds),
            'scheduleConflicts' => $this->scheduleConflictPreview($activePeriod?->id),
            'attentionItems' => $this->attentionItems($activePeriod?->id, $activeStatusIds),
            'recentEvents' => $this->recentAuditEvents(),
            'assignmentPreview' => $this->assignmentReportPreview($activeStatusIds),
            'charts' => [
                'enrollment_trend' => $this->enrollmentTrend($activePeriod?->id),
                'status_distribution' => $this->enrollmentStatusBreakdown($activePeriod?->id),
                'capacity_by_group' => $groupsWithCounts
                    ->take(8)
                    ->map(fn($group) => [
                        'label' => $group->code,
                        'used' => (int) $group->active_enrollments_count,
                        'capacity' => (int) $group->capacity,
                    ])
                    ->values(),
                'subject_areas' => $this->subjectAreaBreakdown(),
            ],
        ];
    }

    private function professorDashboard(int $userId): array
    {
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes'))->pluck('id');

        $groups = ClassGroup::query()
            ->where('professor_id', $userId)
            ->with(['subject:id,name,code', 'academicPeriod:id,name', 'schedules.classroom:id,name'])
            ->withCount([
                'subjectEnrollments as active_enrollments_count' => fn($query) => $query->whereIn('status_id', $activeStatusIds),
                'subjectEnrollments as graded_enrollments_count' => fn($query) => $query
                    ->whereIn('status_id', $activeStatusIds)
                    ->whereHas('grade'),
            ])
            ->latest('id')
            ->get();

        return [
            'metrics' => [
                'assigned_groups' => $groups->count(),
                'active_students' => $groups->sum('active_enrollments_count'),
                'pending_grades' => $groups->sum(fn($group) => max(0, $group->active_enrollments_count - $group->graded_enrollments_count)),
                'scheduled_blocks' => $groups->sum(fn($group) => $group->schedules->count()),
            ],
            'groups' => $groups->take(6)->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'subject' => $group->subject?->name,
                'period' => $group->academicPeriod?->name,
                'students' => $group->active_enrollments_count,
                'pending_grades' => max(0, $group->active_enrollments_count - $group->graded_enrollments_count),
                'status' => $group->status,
            ])->values(),
            'charts' => [
                'students_by_group' => $groups->take(8)->map(fn($group) => [
                    'label' => $group->code,
                    'value' => (int) $group->active_enrollments_count,
                ])->values(),
                'grading_progress' => $groups->take(8)->map(fn($group) => [
                    'label' => $group->code,
                    'graded' => (int) $group->graded_enrollments_count,
                    'pending' => max(0, (int) $group->active_enrollments_count - (int) $group->graded_enrollments_count),
                ])->values(),
            ],
        ];
    }

    private function scheduleConflictCount(?int $periodId): int
    {
        return $this->scheduleConflictQuery($periodId)->count();
    }

    private function scheduleConflictPreview(?int $periodId)
    {
        return $this->scheduleConflictQuery($periodId)
            ->select([
                's1.id as schedule_id',
                'cg1.code as first_group',
                'cg2.code as second_group',
                's1.day',
                's1.start_time',
                's1.end_time',
                'classrooms.name as classroom',
                'u1.name as first_professor',
                'u2.name as second_professor',
            ])
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'id' => $row->schedule_id,
                'first_group' => $row->first_group,
                'second_group' => $row->second_group,
                'day' => Str::headline($row->day),
                'time' => "{$row->start_time} - {$row->end_time}",
                'classroom' => $row->classroom,
                'first_professor' => $row->first_professor,
                'second_professor' => $row->second_professor,
            ]);
    }

    private function scheduleConflictQuery(?int $periodId)
    {
        return DB::table('class_schedules as s1')
            ->join('class_schedules as s2', function ($join) {
                $join->on('s1.day', '=', 's2.day')
                    ->whereColumn('s1.id', '<', 's2.id')
                    ->whereColumn('s1.start_time', '<', 's2.end_time')
                    ->whereColumn('s2.start_time', '<', 's1.end_time');
            })
            ->join('class_groups as cg1', 's1.class_group_id', '=', 'cg1.id')
            ->join('class_groups as cg2', 's2.class_group_id', '=', 'cg2.id')
            ->leftJoin('classrooms', 's1.classroom_id', '=', 'classrooms.id')
            ->leftJoin('users as u1', 'cg1.professor_id', '=', 'u1.id')
            ->leftJoin('users as u2', 'cg2.professor_id', '=', 'u2.id')
            ->where('s1.status', ClassSchedule::STATUS_PUBLISHED)
            ->where('s2.status', ClassSchedule::STATUS_PUBLISHED)
            ->where('cg1.status', ClassGroup::STATUS_PUBLISHED)
            ->where('cg2.status', ClassGroup::STATUS_PUBLISHED)
            ->when($periodId, function ($query) use ($periodId) {
                $query->where('cg1.academic_period_id', $periodId)
                    ->where('cg2.academic_period_id', $periodId);
            })
            ->where(function ($query) {
                $query
                    ->where(function ($classroomConflict) {
                        $classroomConflict
                            ->whereNotNull('s1.classroom_id')
                            ->whereColumn('s1.classroom_id', 's2.classroom_id');
                    })
                    ->orWhere(function ($professorConflict) {
                        $professorConflict
                            ->whereNotNull('cg1.professor_id')
                            ->whereColumn('cg1.professor_id', 'cg2.professor_id');
                    });
            });
    }

    private function enrollmentStatusBreakdown(?int $periodId)
    {
        return SubjectEnrollment::query()
            ->join('subject_enrollment_statuses as statuses', 'subject_enrollments.status_id', '=', 'statuses.id')
            ->when($periodId, fn($query) => $query->where('academic_period_id', $periodId))
            ->select('statuses.code', DB::raw('COUNT(*) as total'))
            ->groupBy('statuses.code')
            ->orderByDesc('total')
            ->get()
            ->map(fn($status) => [
                'label' => Str::headline($status->code),
                'value' => (int) $status->total,
            ]);
    }

    private function enrollmentTrend(?int $periodId)
    {
        return SubjectEnrollment::query()
            ->when($periodId, fn($query) => $query->where('academic_period_id', $periodId))
            ->oldest('created_at')
            ->get(['created_at'])
            ->groupBy(fn($enrollment) => $enrollment->created_at?->format('Y-m') ?? 'Unknown')
            ->map(fn($items, $month) => [
                'label' => $month,
                'value' => $items->count(),
            ])
            ->values();
    }

    private function subjectAreaBreakdown()
    {
        return Subject::query()
            ->select('knowledge_area', DB::raw('COUNT(*) as total'))
            ->groupBy('knowledge_area')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn($area) => [
                'label' => $area->knowledge_area ?: 'Unassigned',
                'value' => (int) $area->total,
            ]);
    }

    private function professorLoad(?int $periodId, $activeStatusIds)
    {
        return ClassGroup::query()
            ->leftJoin('users', 'class_groups.professor_id', '=', 'users.id')
            ->leftJoin('subject_enrollments', function ($join) use ($activeStatusIds) {
                $join->on('class_groups.id', '=', 'subject_enrollments.class_group_id')
                    ->whereIn('subject_enrollments.status_id', $activeStatusIds);
            })
            ->whereNotNull('class_groups.professor_id')
            ->when($periodId, fn($query) => $query->where('class_groups.academic_period_id', $periodId))
            ->select(
                'users.name',
                DB::raw('COUNT(DISTINCT class_groups.id) as groups_count'),
                DB::raw('COUNT(subject_enrollments.id) as students_count')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('groups_count')
            ->orderByDesc('students_count')
            ->limit(6)
            ->get()
            ->map(fn($load) => [
                'name' => $load->name,
                'groups' => (int) $load->groups_count,
                'students' => (int) $load->students_count,
            ]);
    }

    private function attentionItems(?int $periodId, $activeStatusIds)
    {
        $items = collect();
        $minCredits = config('enrollment.min_credits', 7);

        $groupsWithoutSchedule = ClassGroup::query()
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->when($periodId, fn($query) => $query->where('academic_period_id', $periodId))
            ->whereDoesntHave('schedules', fn($query) => $query->where('status', ClassSchedule::STATUS_PUBLISHED))
            ->with('subject:id,name')
            ->limit(3)
            ->get()
            ->map(fn($group) => [
                'type' => 'Schedule',
                'severity' => 'warning',
                'title' => 'Published group without schedule',
                'description' => "{$group->code} - {$group->subject?->name}",
            ]);

        $items = $items->merge($groupsWithoutSchedule);

        $studentsBelowMinimum = Student::query()
            ->with([
                'user:id,name',
                'enrollments' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->when($periodId, fn($query) => $query->where('academic_period_id', $periodId))
                    ->with('subject:id,credits'),
            ])
            ->whereHas('enrollments', function ($enrollments) use ($activeStatusIds, $periodId) {
                $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->when($periodId, fn($query) => $query->where('academic_period_id', $periodId));
            })
            ->get()
            ->map(fn($student) => [
                'student' => $student,
                'credits' => $student->enrollments->sum(fn($enrollment) => (int) ($enrollment->subject?->credits ?? 0)),
            ])
            ->filter(fn($load) => $load['credits'] < $minCredits)
            ->take(3)
            ->map(fn($load) => [
                'type' => 'Academic load',
                'severity' => 'warning',
                'title' => 'Student below minimum credits',
                'description' => "{$load['student']->user?->name} has {$load['credits']} of {$minCredits} credits",
            ]);

        $items = $items->merge($studentsBelowMinimum);

        $roomsWithoutCapacity = Classroom::query()
            ->where(function ($query) {
                $query->whereNull('capacity')->orWhere('capacity', '<=', 0);
            })
            ->limit(2)
            ->get()
            ->map(fn($room) => [
                'type' => 'Classroom',
                'severity' => 'danger',
                'title' => 'Classroom without usable capacity',
                'description' => $room->name,
            ]);

        return $items->merge($roomsWithoutCapacity)->take(5)->values();
    }

    private function recentAuditEvents()
    {
        return AcademicAuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn($event) => [
                'id' => $event->id,
                'action' => Str::headline(str_replace('.', ' ', $event->action)),
                'summary' => $event->summary,
                'user' => $event->user?->name ?? 'System',
                'created_at' => $event->created_at?->toISOString(),
            ]);
    }

    private function assignmentReportPreview($activeStatusIds)
    {
        return Student::query()
            ->with([
                'user:id,name,email',
                'enrollments' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->with([
                        'subject:id,name,code,credits',
                        'classGroup:id,code,professor_id',
                        'classGroup.professor:id,name',
                    ]),
            ])
            ->whereHas('enrollments', fn($enrollments) => $enrollments->whereIn('status_id', $activeStatusIds))
            ->orderBy('document')
            ->limit(5)
            ->get()
            ->map(fn(Student $student) => [
                'id' => $student->id,
                'name' => $student->user?->name,
                'document' => $student->document,
                'credits' => $student->enrollments->sum(fn($enrollment) => (int) ($enrollment->subject?->credits ?? 0)),
                'subjects' => $student->enrollments->map(fn($enrollment) => [
                    'subject' => $enrollment->subject?->name,
                    'code' => $enrollment->subject?->code,
                    'professor' => $enrollment->classGroup?->professor?->name ?? 'No professor assigned',
                    'group' => $enrollment->classGroup?->code,
                ])->values(),
            ]);
    }

}

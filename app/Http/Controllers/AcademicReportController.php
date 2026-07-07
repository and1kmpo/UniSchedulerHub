<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\AcademicAuditLog;
use App\Models\Building;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Models\Professor;
use App\Models\Program;
use App\Models\Student;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'reports' => [
                [
                    'title' => 'Student Assignment Report',
                    'description' => 'Students, enrolled subjects, class groups and responsible professors.',
                    'route' => 'reports.student-assignments.index',
                    'icon' => 'fa-solid fa-user-graduate',
                    'category' => 'Academic enrollment',
                ],
                [
                    'title' => 'Professor Load Report',
                    'description' => 'Assigned groups, active students, scheduled blocks and pending grades.',
                    'route' => 'reports.professor-load.index',
                    'icon' => 'fa-solid fa-chalkboard-user',
                    'category' => 'Teaching operations',
                ],
                [
                    'title' => 'Classroom Occupancy Report',
                    'description' => 'Classrooms, capacity, scheduled blocks, assigned groups and utilization.',
                    'route' => 'reports.classroom-occupancy.index',
                    'icon' => 'fa-solid fa-door-open',
                    'category' => 'Space utilization',
                ],
                [
                    'title' => 'Group Capacity And Conflict Report',
                    'description' => 'Class groups, seats, utilization, schedule conflicts and operational alerts.',
                    'route' => 'reports.group-capacity-conflicts.index',
                    'icon' => 'fa-solid fa-triangle-exclamation',
                    'category' => 'Academic operations',
                ],
                [
                    'title' => 'Grade Operations Report',
                    'description' => 'Class groups, grade progress, pending grades and grade editing locks.',
                    'route' => 'reports.grade-operations.index',
                    'icon' => 'fa-solid fa-clipboard-check',
                    'category' => 'Academic evaluation',
                ],
                [
                    'title' => 'Academic Events Report',
                    'description' => 'Critical academic events, actors, affected records and audit context.',
                    'route' => 'reports.academic-events.index',
                    'icon' => 'fa-solid fa-shield-halved',
                    'category' => 'Academic audit',
                ],
            ],
        ]);
    }

    public function studentAssignments(Request $request)
    {
        $filters = $request->only([
            'search',
            'academic_period_id',
            'program_id',
            'professor_id',
            'status',
        ]);

        $activeStatusCodes = config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']);

        $students = Student::query()
            ->with([
                'user:id,name,email',
                'program:id,name',
                'enrollments' => fn($query) => $this->applyEnrollmentFilters($query, $request)
                    ->with([
                        'status:id,code,description,color',
                        'academicPeriod:id,name',
                        'subject:id,code,name,credits,knowledge_area,elective',
                        'classGroup:id,code,name,professor_id,status',
                        'classGroup.professor:id,name,email',
                    ])
                    ->latest(),
            ])
            ->whereHas('enrollments', fn($query) => $this->applyEnrollmentFilters($query, $request))
            ->when($request->filled('program_id'), fn($query) => $query
                ->where('program_id', $request->integer('program_id')))
            ->when($request->filled('search'), fn($query) => $this->applyStudentSearch($query, $request))
            ->orderBy('document')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Student $student) => $this->studentReportPayload($student, $activeStatusCodes));

        $summary = $this->summary($request, $activeStatusCodes);

        return Inertia::render('Reports/StudentAssignments', [
            'students' => $students,
            'summary' => $summary,
            'filters' => $filters,
            'options' => [
                'periods' => AcademicPeriod::query()
                    ->select('id', 'name')
                    ->latest('start_date')
                    ->get(),
                'programs' => Program::query()
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get(),
                'professors' => Professor::query()
                    ->with('user:id,name')
                    ->get()
                    ->map(fn($professor) => [
                        'id' => $professor->user_id,
                        'name' => $professor->user?->name,
                    ])
                    ->filter(fn($professor) => filled($professor['name']))
                    ->sortBy('name')
                    ->values(),
                'statuses' => SubjectEnrollmentStatus::query()
                    ->select('code', 'description')
                    ->orderBy('description')
                    ->get(),
            ],
        ]);
    }

    public function exportStudentAssignments(Request $request): StreamedResponse
    {
        $filename = 'student-assignment-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student document',
                'Student name',
                'Student email',
                'Program',
                'Semester',
                'Subject code',
                'Subject name',
                'Credits',
                'Subject type',
                'Knowledge area',
                'Professor',
                'Professor email',
                'Class group',
                'Academic period',
                'Enrollment status',
            ]);

            $this->studentAssignmentExportQuery($request)
                ->chunk(250, function ($enrollments) use ($handle) {
                    foreach ($enrollments as $enrollment) {
                        fputcsv($handle, [
                            $enrollment->student?->document,
                            $enrollment->student?->user?->name,
                            $enrollment->student?->user?->email,
                            $enrollment->student?->program?->name,
                            $enrollment->student?->semester,
                            $enrollment->subject?->code,
                            $enrollment->subject?->name,
                            $enrollment->subject?->credits,
                            $enrollment->subject?->elective ? 'Elective' : 'Required',
                            $enrollment->subject?->knowledge_area,
                            $enrollment->classGroup?->professor?->name ?? 'Unassigned',
                            $enrollment->classGroup?->professor?->email,
                            $enrollment->classGroup?->code,
                            $enrollment->academicPeriod?->name,
                            $enrollment->status?->description ?? Str::headline($enrollment->status?->code ?? ''),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function professorLoad(Request $request)
    {
        $filters = $request->only([
            'search',
            'academic_period_id',
            'status',
        ]);

        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');

        $professors = Professor::query()
            ->with('user:id,name,email')
            ->with([
                'classGroups' => fn($query) => $this->applyProfessorLoadGroupFilters($query, $request)
                    ->with([
                        'academicPeriod:id,name',
                        'subject:id,code,name,credits',
                        'schedules' => fn($schedules) => $schedules
                            ->where('status', ClassSchedule::STATUS_PUBLISHED)
                            ->with('classroom:id,name,building_id'),
                    ])
                    ->withCount([
                        'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                            ->whereIn('status_id', $activeStatusIds),
                        'subjectEnrollments as graded_students_count' => fn($enrollments) => $enrollments
                            ->whereIn('status_id', $activeStatusIds)
                            ->whereHas('grade'),
                    ])
                    ->orderBy('code'),
            ])
            ->whereHas('classGroups', fn($query) => $this->applyProfessorLoadGroupFilters($query, $request))
            ->when($request->filled('search'), fn($query) => $this->applyProfessorSearch($query, $request))
            ->orderBy('document')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Professor $professor) => $this->professorLoadPayload($professor));

        return Inertia::render('Reports/ProfessorLoad', [
            'professors' => $professors,
            'summary' => $this->professorLoadSummary($request, $activeStatusIds),
            'filters' => $filters,
            'options' => [
                'periods' => AcademicPeriod::query()
                    ->select('id', 'name')
                    ->latest('start_date')
                    ->get(),
                'statuses' => [
                    ['label' => 'Draft', 'value' => ClassGroup::STATUS_DRAFT],
                    ['label' => 'Published', 'value' => ClassGroup::STATUS_PUBLISHED],
                    ['label' => 'Cancelled', 'value' => ClassGroup::STATUS_CANCELLED],
                    ['label' => 'Closed', 'value' => ClassGroup::STATUS_CLOSED],
                ],
            ],
        ]);
    }

    public function exportProfessorLoad(Request $request): StreamedResponse
    {
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');
        $filename = 'professor-load-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request, $activeStatusIds) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Professor document',
                'Professor name',
                'Professor email',
                'Class group',
                'Subject',
                'Academic period',
                'Group status',
                'Active students',
                'Pending grades',
                'Scheduled blocks',
            ]);

            $this->professorLoadExportQuery($request, $activeStatusIds)
                ->chunk(250, function ($groups) use ($handle) {
                    foreach ($groups as $group) {
                        fputcsv($handle, [
                            $group->professorProfile?->document,
                            $group->professor?->name,
                            $group->professor?->email,
                            $group->code,
                            $group->subject?->name,
                            $group->academicPeriod?->name,
                            Str::headline($group->status),
                            (int) $group->active_students_count,
                            max(0, (int) $group->active_students_count - (int) $group->graded_students_count),
                            (int) $group->published_schedules_count,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function classroomOccupancy(Request $request)
    {
        $filters = $request->only([
            'search',
            'building_id',
            'academic_period_id',
            'status',
        ]);

        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');

        $classrooms = Classroom::query()
            ->with('building:id,code,name')
            ->with([
                'schedules' => fn($query) => $this->applyClassroomScheduleFilters($query, $request)
                    ->with([
                        'classGroup' => fn($group) => $group
                            ->with(['subject:id,code,name', 'professor:id,name,email', 'academicPeriod:id,name'])
                            ->withCount([
                                'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                                    ->whereIn('status_id', $activeStatusIds),
                            ]),
                    ])
                    ->orderBy('day')
                    ->orderBy('start_time'),
            ])
            ->when($request->filled('building_id'), fn($query) => $query
                ->where('building_id', $request->integer('building_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), fn($query) => $this->applyClassroomSearch($query, $request))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Classroom $classroom) => $this->classroomOccupancyPayload($classroom, $request));

        return Inertia::render('Reports/ClassroomOccupancy', [
            'classrooms' => $classrooms,
            'summary' => $this->classroomOccupancySummary($request, $activeStatusIds),
            'filters' => $filters,
            'options' => [
                'periods' => AcademicPeriod::query()
                    ->select('id', 'name')
                    ->latest('start_date')
                    ->get(),
                'buildings' => Building::query()
                    ->select('id', 'code', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($building) => [
                        'id' => $building->id,
                        'name' => "{$building->code} - {$building->name}",
                    ]),
                'statuses' => Classroom::query()
                    ->select('status')
                    ->whereNotNull('status')
                    ->distinct()
                    ->orderBy('status')
                    ->get()
                    ->map(fn($classroom) => [
                        'label' => Str::headline($classroom->status),
                        'value' => $classroom->status,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function exportClassroomOccupancy(Request $request): StreamedResponse
    {
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');
        $filename = 'classroom-occupancy-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request, $activeStatusIds) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Building',
                'Classroom',
                'Classroom status',
                'Classroom capacity',
                'Day',
                'Start time',
                'End time',
                'Class group',
                'Subject',
                'Professor',
                'Academic period',
                'Active students',
                'Seat utilization %',
                'Conflict',
            ]);

            $conflicts = $this->classroomConflictScheduleIds($request);

            $this->classroomOccupancyExportQuery($request, $activeStatusIds)
                ->chunk(250, function ($schedules) use ($handle, $conflicts) {
                    foreach ($schedules as $schedule) {
                        $capacity = (int) ($schedule->classroom?->capacity ?? 0);
                        $activeStudents = (int) ($schedule->classGroup?->active_students_count ?? 0);

                        fputcsv($handle, [
                            $schedule->classroom?->building?->name,
                            $schedule->classroom?->name,
                            Str::headline($schedule->classroom?->status ?? ''),
                            $capacity,
                            Str::headline($schedule->day),
                            $schedule->start_time,
                            $schedule->end_time,
                            $schedule->classGroup?->code,
                            $schedule->classGroup?->subject?->name,
                            $schedule->classGroup?->professor?->name,
                            $schedule->classGroup?->academicPeriod?->name,
                            $activeStudents,
                            $capacity > 0 ? round(($activeStudents / $capacity) * 100, 1) : 0,
                            $conflicts->contains($schedule->id) ? 'Yes' : 'No',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function groupCapacityConflicts(Request $request)
    {
        $filters = $request->only([
            'search',
            'academic_period_id',
            'status',
            'alert',
        ]);

        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');
        $conflictScheduleIds = $this->allConflictScheduleIds($request);

        $groups = $this->groupCapacityConflictQuery($request, $activeStatusIds)
            ->paginate(12)
            ->withQueryString()
            ->through(fn(ClassGroup $group) => $this->groupCapacityConflictPayload($group, $conflictScheduleIds));

        return Inertia::render('Reports/GroupCapacityConflicts', [
            'groups' => $groups,
            'summary' => $this->groupCapacityConflictSummary($request, $activeStatusIds, $conflictScheduleIds),
            'filters' => $filters,
            'options' => [
                'periods' => AcademicPeriod::query()
                    ->select('id', 'name')
                    ->latest('start_date')
                    ->get(),
                'statuses' => [
                    ['label' => 'Draft', 'value' => ClassGroup::STATUS_DRAFT],
                    ['label' => 'Published', 'value' => ClassGroup::STATUS_PUBLISHED],
                    ['label' => 'Cancelled', 'value' => ClassGroup::STATUS_CANCELLED],
                    ['label' => 'Closed', 'value' => ClassGroup::STATUS_CLOSED],
                ],
                'alerts' => [
                    ['label' => 'Has conflicts', 'value' => 'conflicts'],
                    ['label' => 'Full groups', 'value' => 'full'],
                    ['label' => 'Near capacity', 'value' => 'near_capacity'],
                    ['label' => 'No schedule', 'value' => 'no_schedule'],
                    ['label' => 'No capacity', 'value' => 'no_capacity'],
                ],
            ],
        ]);
    }

    public function exportGroupCapacityConflicts(Request $request): StreamedResponse
    {
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');
        $conflictScheduleIds = $this->allConflictScheduleIds($request);
        $filename = 'group-capacity-conflict-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request, $activeStatusIds, $conflictScheduleIds) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Class group',
                'Subject',
                'Professor',
                'Academic period',
                'Status',
                'Capacity',
                'Active students',
                'Available seats',
                'Utilization %',
                'Schedule blocks',
                'Conflict blocks',
                'Alerts',
            ]);

            $this->groupCapacityConflictQuery($request, $activeStatusIds)
                ->chunk(250, function ($groups) use ($handle, $conflictScheduleIds) {
                    foreach ($groups as $group) {
                        $payload = $this->groupCapacityConflictPayload($group, $conflictScheduleIds);

                        fputcsv($handle, [
                            $payload['code'],
                            $payload['subject']['name'],
                            $payload['professor'],
                            $payload['period'],
                            Str::headline($payload['status']),
                            $payload['capacity'],
                            $payload['active_students'],
                            $payload['available_seats'],
                            $payload['utilization'],
                            $payload['scheduled_blocks'],
                            $payload['conflict_blocks'],
                            implode(', ', $payload['alerts']),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function gradeOperations(Request $request)
    {
        $filters = $request->only([
            'search',
            'academic_period_id',
            'status',
            'grade_state',
        ]);

        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');

        $groups = $this->gradeOperationsQuery($request, $activeStatusIds)
            ->paginate(12)
            ->withQueryString()
            ->through(fn(ClassGroup $group) => $this->gradeOperationsPayload($group));

        return Inertia::render('Reports/GradeOperations', [
            'groups' => $groups,
            'summary' => $this->gradeOperationsSummary($request, $activeStatusIds),
            'filters' => $filters,
            'options' => [
                'periods' => AcademicPeriod::query()
                    ->select('id', 'name')
                    ->latest('start_date')
                    ->get(),
                'statuses' => [
                    ['label' => 'Draft', 'value' => ClassGroup::STATUS_DRAFT],
                    ['label' => 'Published', 'value' => ClassGroup::STATUS_PUBLISHED],
                    ['label' => 'Cancelled', 'value' => ClassGroup::STATUS_CANCELLED],
                    ['label' => 'Closed', 'value' => ClassGroup::STATUS_CLOSED],
                ],
                'gradeStates' => [
                    ['label' => 'Pending grades', 'value' => 'pending'],
                    ['label' => 'Completed grading', 'value' => 'completed'],
                    ['label' => 'Grade editing open', 'value' => 'open'],
                    ['label' => 'Grade editing locked', 'value' => 'locked'],
                ],
            ],
        ]);
    }

    public function exportGradeOperations(Request $request): StreamedResponse
    {
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes', ['pre_enrolled', 'enrolled']))
            ->pluck('id');
        $filename = 'grade-operations-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request, $activeStatusIds) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Class group',
                'Subject',
                'Professor',
                'Academic period',
                'Period status',
                'Group status',
                'Active students',
                'Graded students',
                'Pending grades',
                'Grade progress %',
                'Grade editing',
                'Lock reason',
            ]);

            $this->gradeOperationsQuery($request, $activeStatusIds)
                ->chunk(250, function ($groups) use ($handle) {
                    foreach ($groups as $group) {
                        $payload = $this->gradeOperationsPayload($group);

                        fputcsv($handle, [
                            $payload['code'],
                            $payload['subject']['name'],
                            $payload['professor'],
                            $payload['period'],
                            $payload['period_status'],
                            Str::headline($payload['status']),
                            $payload['active_students'],
                            $payload['graded_students'],
                            $payload['pending_grades'],
                            $payload['progress'],
                            $payload['can_edit_grades'] ? 'Open' : 'Locked',
                            $payload['lock_reason'] ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function academicEvents(Request $request)
    {
        $filters = $request->only([
            'search',
            'action',
            'event_type',
            'user_id',
            'auditable_type',
            'date_from',
            'date_to',
        ]);

        $events = $this->academicEventsQuery($request)
            ->paginate(15)
            ->withQueryString()
            ->through(fn(AcademicAuditLog $event) => $this->academicEventPayload($event));

        return Inertia::render('Reports/AcademicEvents', [
            'events' => $events,
            'summary' => $this->academicEventsSummary($request),
            'filters' => $filters,
            'options' => [
                'actions' => $this->academicEventActionOptions(),
                'eventTypes' => $this->academicEventTypeOptions(),
                'users' => $this->academicEventUserOptions(),
                'entities' => $this->academicEventEntityOptions(),
            ],
        ]);
    }

    public function exportAcademicEvents(Request $request): StreamedResponse
    {
        $filename = 'academic-events-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Event type',
                'Action',
                'Actor',
                'Actor email',
                'Entity',
                'Entity ID',
                'Summary',
                'Context',
            ]);

            $this->academicEventsQuery($request)
                ->chunk(250, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        $payload = $this->academicEventPayload($event);

                        fputcsv($handle, [
                            $event->created_at?->format('d M Y, h:i A'),
                            $payload['event_type_label'],
                            $payload['action_label'],
                            $payload['user']['name'] ?? 'System',
                            $payload['user']['email'] ?? '',
                            $payload['entity'],
                            $payload['entity_id'],
                            $payload['summary'],
                            $payload['context'],
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function applyEnrollmentFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('professor_id'), fn($query) => $query
                ->whereHas('classGroup', fn($group) => $group
                    ->where('professor_id', $request->integer('professor_id'))))
            ->when($request->filled('status'), fn($query) => $query
                ->whereHas('status', fn($status) => $status
                    ->where('code', $request->string('status')->toString())));
    }

    private function applyStudentSearch($query, Request $request)
    {
        $search = $request->string('search')->toString();

        return $query->where(function ($query) use ($search) {
            $query
                ->where('document', 'like', "%{$search}%")
                ->orWhereHas('user', fn($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
        });
    }

    private function applyProfessorSearch($query, Request $request)
    {
        $search = $request->string('search')->toString();

        return $query->where(function ($query) use ($search) {
            $query
                ->where('document', 'like', "%{$search}%")
                ->orWhereHas('user', fn($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
        });
    }

    private function applyProfessorLoadGroupFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()));
    }

    private function applyClassroomSearch($query, Request $request)
    {
        $search = $request->string('search')->toString();

        return $query->where(function ($query) use ($search) {
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhereHas('building', fn($building) => $building
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
        });
    }

    private function applyClassroomScheduleFilters($query, Request $request)
    {
        return $query
            ->where('status', ClassSchedule::STATUS_PUBLISHED)
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->whereHas('classGroup', fn($group) => $group
                    ->where('academic_period_id', $request->integer('academic_period_id'))));
    }

    private function applyGroupSearch($query, Request $request)
    {
        $search = $request->string('search')->toString();

        return $query->where(function ($query) use ($search) {
            $query
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhereHas('subject', fn($subject) => $subject
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"))
                ->orWhereHas('professor', fn($professor) => $professor
                    ->where('name', 'like', "%{$search}%"));
        });
    }

    private function applyGroupAlertFilter($query, Request $request, $activeStatusIds)
    {
        return match ($request->string('alert')->toString()) {
            'no_schedule' => $query->whereDoesntHave('schedules', fn($schedules) => $schedules
                ->where('status', ClassSchedule::STATUS_PUBLISHED)),
            'no_capacity' => $query->where(function ($query) {
                $query->whereNull('capacity')->orWhere('capacity', '<=', 0);
            }),
            'full', 'near_capacity' => $query
                ->whereNotNull('capacity')
                ->where('capacity', '>', 0)
                ->withCount([
                    'subjectEnrollments as alert_active_students_count' => fn($enrollments) => $enrollments
                        ->whereIn('status_id', $activeStatusIds),
                ])
                ->when($request->string('alert')->toString() === 'full', fn($query) => $query
                    ->havingRaw('alert_active_students_count >= capacity'))
                ->when($request->string('alert')->toString() === 'near_capacity', fn($query) => $query
                    ->havingRaw('(alert_active_students_count / capacity) >= 0.85')
                    ->havingRaw('alert_active_students_count < capacity')),
            default => $query,
        };
    }

    private function applyGradeStateFilter($query, Request $request)
    {
        return match ($request->string('grade_state')->toString()) {
            'pending' => $query
                ->havingRaw('active_students_count > graded_students_count'),
            'completed' => $query
                ->havingRaw('active_students_count > 0')
                ->havingRaw('graded_students_count >= active_students_count'),
            'open' => $query
                ->whereNotIn('status', [ClassGroup::STATUS_CANCELLED, ClassGroup::STATUS_CLOSED])
                ->whereHas('academicPeriod', fn($period) => $period
                    ->whereHas('status', fn($status) => $status->where('code', 'in_progress'))),
            'locked' => $query
                ->where(function ($query) {
                    $query
                        ->whereIn('status', [ClassGroup::STATUS_CANCELLED, ClassGroup::STATUS_CLOSED])
                        ->orWhereDoesntHave('academicPeriod', fn($period) => $period
                            ->whereHas('status', fn($status) => $status->where('code', 'in_progress')));
                }),
            default => $query,
        };
    }

    private function studentAssignmentExportQuery(Request $request)
    {
        return SubjectEnrollment::query()
            ->with([
                'academicPeriod:id,name',
                'classGroup:id,code,professor_id',
                'classGroup.professor:id,name,email',
                'status:id,code,description',
                'student:id,user_id,document,program_id,semester',
                'student.program:id,name',
                'student.user:id,name,email',
                'subject:id,code,name,credits,knowledge_area,elective',
            ])
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('program_id'), fn($query) => $query
                ->whereHas('student', fn($student) => $student
                    ->where('program_id', $request->integer('program_id'))))
            ->when($request->filled('professor_id'), fn($query) => $query
                ->whereHas('classGroup', fn($group) => $group
                    ->where('professor_id', $request->integer('professor_id'))))
            ->when($request->filled('status'), fn($query) => $query
                ->whereHas('status', fn($status) => $status
                    ->where('code', $request->string('status')->toString())))
            ->when($request->filled('search'), fn($query) => $query
                ->whereHas('student', fn($student) => $this->applyStudentSearch($student, $request)))
            ->orderBy('student_id')
            ->orderBy('subject_id');
    }

    private function professorLoadExportQuery(Request $request, $activeStatusIds)
    {
        return ClassGroup::query()
            ->with([
                'academicPeriod:id,name',
                'professor:id,name,email',
                'professorProfile:id,user_id,document',
                'subject:id,code,name',
            ])
            ->withCount([
                'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds),
                'subjectEnrollments as graded_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->whereHas('grade'),
                'schedules as published_schedules_count' => fn($schedules) => $schedules
                    ->where('status', ClassSchedule::STATUS_PUBLISHED),
            ])
            ->whereNotNull('professor_id')
            ->when($request->filled('search'), fn($query) => $query
                ->whereHas('professorProfile', fn($professor) => $this->applyProfessorSearch($professor, $request)))
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->orderBy('professor_id')
            ->orderBy('code');
    }

    private function classroomOccupancyExportQuery(Request $request, $activeStatusIds)
    {
        return ClassSchedule::query()
            ->with([
                'classroom:id,name,building_id,capacity,status',
                'classroom.building:id,code,name',
                'classGroup' => fn($group) => $group
                    ->with(['subject:id,code,name', 'professor:id,name,email', 'academicPeriod:id,name'])
                    ->withCount([
                        'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                            ->whereIn('status_id', $activeStatusIds),
                    ]),
            ])
            ->whereHas('classroom', function ($classroom) use ($request) {
                $classroom
                    ->when($request->filled('building_id'), fn($query) => $query
                        ->where('building_id', $request->integer('building_id')))
                    ->when($request->filled('status'), fn($query) => $query
                        ->where('status', $request->string('status')->toString()))
                    ->when($request->filled('search'), fn($query) => $this->applyClassroomSearch($query, $request));
            })
            ->tap(fn($query) => $this->applyClassroomScheduleFilters($query, $request))
            ->orderBy('classroom_id')
            ->orderBy('day')
            ->orderBy('start_time');
    }

    private function groupCapacityConflictQuery(Request $request, $activeStatusIds)
    {
        return ClassGroup::query()
            ->with([
                'academicPeriod:id,name',
                'professor:id,name,email',
                'subject:id,code,name',
                'schedules' => fn($schedules) => $schedules
                    ->where('status', ClassSchedule::STATUS_PUBLISHED)
                    ->with('classroom:id,name,building_id'),
                'schedules.classroom.building:id,code,name',
            ])
            ->withCount([
                'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds),
                'schedules as published_schedules_count' => fn($schedules) => $schedules
                    ->where('status', ClassSchedule::STATUS_PUBLISHED),
            ])
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), fn($query) => $this->applyGroupSearch($query, $request))
            ->when($request->string('alert')->toString() === 'conflicts', fn($query) => $query
                ->whereHas('schedules', fn($schedules) => $schedules
                    ->whereIn('id', $this->allConflictScheduleIds($request))))
            ->when($request->filled('alert') && $request->string('alert')->toString() !== 'conflicts', fn($query) => $this
                ->applyGroupAlertFilter($query, $request, $activeStatusIds))
            ->orderBy('code');
    }

    private function gradeOperationsQuery(Request $request, $activeStatusIds)
    {
        return ClassGroup::query()
            ->with([
                'academicPeriod.status:id,code,name',
                'professor:id,name,email',
                'subject:id,code,name',
            ])
            ->withCount([
                'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds),
                'subjectEnrollments as graded_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->whereHas('grade'),
            ])
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), fn($query) => $this->applyGroupSearch($query, $request))
            ->when($request->filled('grade_state'), fn($query) => $this->applyGradeStateFilter($query, $request))
            ->orderBy('code');
    }

    private function academicEventsQuery(Request $request)
    {
        return AcademicAuditLog::query()
            ->with('user:id,name,email')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('action', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhere('auditable_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($user) use ($search) {
                            $user
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('action'), fn($query) => $query
                ->where('action', $request->string('action')->toString()))
            ->when($request->filled('event_type'), fn($query) => $this->applyAcademicEventTypeFilter($query, $request))
            ->when($request->filled('user_id'), fn($query) => $query
                ->where('user_id', $request->integer('user_id')))
            ->when($request->filled('auditable_type'), fn($query) => $query
                ->where('auditable_type', $request->string('auditable_type')->toString()))
            ->when($request->filled('date_from'), fn($query) => $query
                ->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn($query) => $query
                ->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at');
    }

    private function applyAcademicEventTypeFilter($query, Request $request)
    {
        return match ($request->string('event_type')->toString()) {
            'enrollment' => $query->where('action', 'like', 'enrollment.%'),
            'schedule' => $query->where('action', 'like', 'schedule.%'),
            'grade' => $query->where('action', 'like', 'grade.%'),
            'academic_period' => $query->where('action', 'like', 'academic_period.%'),
            default => $query,
        };
    }

    private function academicEventActionOptions()
    {
        return AcademicAuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn($action) => [
                'value' => $action,
                'label' => Str::headline(str_replace('.', ' ', $action)),
            ])
            ->values();
    }

    private function academicEventTypeOptions(): array
    {
        return [
            ['label' => 'Enrollment events', 'value' => 'enrollment'],
            ['label' => 'Schedule events', 'value' => 'schedule'],
            ['label' => 'Grade events', 'value' => 'grade'],
            ['label' => 'Academic period events', 'value' => 'academic_period'],
        ];
    }

    private function academicEventUserOptions()
    {
        return AcademicAuditLog::query()
            ->with('user:id,name,email')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->sortBy('name')
            ->map(fn($user) => [
                'value' => $user->id,
                'label' => "{$user->name} ({$user->email})",
            ])
            ->values();
    }

    private function academicEventEntityOptions()
    {
        return AcademicAuditLog::query()
            ->whereNotNull('auditable_type')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->values();
    }

    private function studentReportPayload(Student $student, array $activeStatusCodes): array
    {
        return [
            'id' => $student->id,
            'document' => $student->document,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'program' => $student->program?->name,
            'semester' => $student->semester,
            'assignments_count' => $student->enrollments->count(),
            'active_credits' => $student->enrollments
                ->filter(fn($enrollment) => in_array($enrollment->status?->code, $activeStatusCodes, true))
                ->sum(fn($enrollment) => (int) ($enrollment->subject?->credits ?? 0)),
            'minimum_credits' => config('enrollment.min_credits', 7),
            'assignments' => $student->enrollments->map(fn($enrollment) => [
                'id' => $enrollment->id,
                'status' => $enrollment->status?->code,
                'status_label' => $enrollment->status?->description,
                'period' => $enrollment->academicPeriod?->name,
                'subject' => [
                    'code' => $enrollment->subject?->code,
                    'name' => $enrollment->subject?->name,
                    'credits' => $enrollment->subject?->credits,
                    'area' => $enrollment->subject?->knowledge_area,
                    'elective' => (bool) $enrollment->subject?->elective,
                ],
                'professor' => [
                    'name' => $enrollment->classGroup?->professor?->name ?? 'Unassigned',
                    'email' => $enrollment->classGroup?->professor?->email,
                ],
                'group' => [
                    'code' => $enrollment->classGroup?->code,
                    'name' => $enrollment->classGroup?->name,
                    'status' => $enrollment->classGroup?->status,
                ],
            ])->values(),
        ];
    }

    private function professorLoadPayload(Professor $professor): array
    {
        $groups = $professor->classGroups;

        return [
            'id' => $professor->id,
            'document' => $professor->document,
            'name' => $professor->user?->name,
            'email' => $professor->user?->email,
            'groups_count' => $groups->count(),
            'active_students' => $groups->sum('active_students_count'),
            'scheduled_blocks' => $groups->sum(fn($group) => $group->schedules->count()),
            'pending_grades' => $groups->sum(fn($group) => max(0, (int) $group->active_students_count - (int) $group->graded_students_count)),
            'groups' => $groups->map(fn($group) => [
                'id' => $group->id,
                'code' => $group->code,
                'status' => $group->status,
                'period' => $group->academicPeriod?->name,
                'subject' => [
                    'code' => $group->subject?->code,
                    'name' => $group->subject?->name,
                    'credits' => $group->subject?->credits,
                ],
                'active_students' => (int) $group->active_students_count,
                'pending_grades' => max(0, (int) $group->active_students_count - (int) $group->graded_students_count),
                'scheduled_blocks' => $group->schedules->count(),
            ])->values(),
        ];
    }

    private function classroomOccupancyPayload(Classroom $classroom, Request $request): array
    {
        $conflicts = $this->classroomConflictScheduleIds($request);
        $schedules = $classroom->schedules;
        $capacity = (int) ($classroom->capacity ?? 0);
        $activeStudents = $schedules->sum(fn($schedule) => (int) ($schedule->classGroup?->active_students_count ?? 0));
        $scheduledBlocks = $schedules->count();
        $averageUtilization = $scheduledBlocks > 0 && $capacity > 0
            ? round($schedules->avg(fn($schedule) => ((int) ($schedule->classGroup?->active_students_count ?? 0) / $capacity) * 100), 1)
            : 0;

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'status' => $classroom->status,
            'capacity' => $capacity,
            'building' => $classroom->building ? "{$classroom->building->code} - {$classroom->building->name}" : 'No building',
            'scheduled_blocks' => $scheduledBlocks,
            'assigned_groups' => $schedules->pluck('class_group_id')->unique()->count(),
            'active_students' => $activeStudents,
            'average_utilization' => $averageUtilization,
            'conflicts' => $schedules->filter(fn($schedule) => $conflicts->contains($schedule->id))->count(),
            'schedules' => $schedules->map(fn($schedule) => [
                'id' => $schedule->id,
                'day' => Str::headline($schedule->day),
                'time' => "{$schedule->start_time} - {$schedule->end_time}",
                'conflict' => $conflicts->contains($schedule->id),
                'group' => [
                    'code' => $schedule->classGroup?->code,
                    'status' => $schedule->classGroup?->status,
                    'active_students' => (int) ($schedule->classGroup?->active_students_count ?? 0),
                ],
                'subject' => [
                    'code' => $schedule->classGroup?->subject?->code,
                    'name' => $schedule->classGroup?->subject?->name,
                ],
                'professor' => $schedule->classGroup?->professor?->name,
                'period' => $schedule->classGroup?->academicPeriod?->name,
                'utilization' => $capacity > 0
                    ? round(((int) ($schedule->classGroup?->active_students_count ?? 0) / $capacity) * 100, 1)
                    : 0,
            ])->values(),
        ];
    }

    private function groupCapacityConflictPayload(ClassGroup $group, $conflictScheduleIds): array
    {
        $capacity = (int) ($group->capacity ?? 0);
        $activeStudents = (int) ($group->active_students_count ?? 0);
        $availableSeats = max(0, $capacity - $activeStudents);
        $utilization = $capacity > 0 ? round(($activeStudents / $capacity) * 100, 1) : 0;
        $conflictBlocks = $group->schedules
            ->filter(fn($schedule) => $conflictScheduleIds->contains($schedule->id))
            ->count();
        $alerts = [];

        if ($capacity <= 0) {
            $alerts[] = 'No capacity';
        } elseif ($activeStudents >= $capacity) {
            $alerts[] = 'Full';
        } elseif ($utilization >= 85) {
            $alerts[] = 'Near capacity';
        }

        if ($group->schedules->isEmpty()) {
            $alerts[] = 'No schedule';
        }

        if ($conflictBlocks > 0) {
            $alerts[] = 'Schedule conflict';
        }

        return [
            'id' => $group->id,
            'code' => $group->code,
            'name' => $group->name,
            'status' => $group->status,
            'period' => $group->academicPeriod?->name,
            'professor' => $group->professor?->name ?? 'Unassigned',
            'subject' => [
                'code' => $group->subject?->code,
                'name' => $group->subject?->name,
            ],
            'capacity' => $capacity,
            'active_students' => $activeStudents,
            'available_seats' => $availableSeats,
            'utilization' => $utilization,
            'scheduled_blocks' => (int) ($group->published_schedules_count ?? $group->schedules->count()),
            'conflict_blocks' => $conflictBlocks,
            'alerts' => $alerts,
            'schedules' => $group->schedules->map(fn($schedule) => [
                'id' => $schedule->id,
                'day' => Str::headline($schedule->day),
                'time' => "{$schedule->start_time} - {$schedule->end_time}",
                'classroom' => $schedule->classroom?->name ?? 'No classroom',
                'building' => $schedule->classroom?->building
                    ? "{$schedule->classroom->building->code} - {$schedule->classroom->building->name}"
                    : 'No building',
                'conflict' => $conflictScheduleIds->contains($schedule->id),
            ])->values(),
        ];
    }

    private function gradeOperationsPayload(ClassGroup $group): array
    {
        $activeStudents = (int) ($group->active_students_count ?? 0);
        $gradedStudents = (int) ($group->graded_students_count ?? 0);
        $pendingGrades = max(0, $activeStudents - $gradedStudents);
        $canEditGrades = $this->canEditGradesOperationally($group);

        return [
            'id' => $group->id,
            'code' => $group->code,
            'name' => $group->name,
            'status' => $group->status,
            'period' => $group->academicPeriod?->name,
            'period_status' => $group->academicPeriod?->status?->label
                ?? $group->academicPeriod?->status?->name
                ?? Str::headline($group->academicPeriod?->status?->code ?? 'No period'),
            'professor' => $group->professor?->name ?? 'Unassigned',
            'subject' => [
                'code' => $group->subject?->code,
                'name' => $group->subject?->name,
            ],
            'active_students' => $activeStudents,
            'graded_students' => $gradedStudents,
            'pending_grades' => $pendingGrades,
            'progress' => $activeStudents > 0 ? round(($gradedStudents / $activeStudents) * 100, 1) : 0,
            'can_edit_grades' => $canEditGrades,
            'lock_reason' => $canEditGrades ? null : $this->gradeLockReason($group),
        ];
    }

    private function academicEventPayload(AcademicAuditLog $event): array
    {
        $metadata = $event->metadata ?? [];

        return [
            'id' => $event->id,
            'created_at' => $event->created_at?->toISOString(),
            'action' => $event->action,
            'action_label' => Str::headline(str_replace('.', ' ', $event->action)),
            'event_type' => $this->academicEventType($event->action),
            'event_type_label' => $this->academicEventTypeLabel($event->action),
            'summary' => $event->summary ?: 'No summary available',
            'entity' => $event->auditable_type ? class_basename($event->auditable_type) : 'System',
            'entity_id' => $event->auditable_id,
            'user' => $event->user ? [
                'id' => $event->user->id,
                'name' => $event->user->name,
                'email' => $event->user->email,
            ] : null,
            'metadata' => $metadata,
            'context' => $this->academicEventContext($metadata),
            'change_count' => $this->academicEventChangeCount($metadata),
        ];
    }

    private function academicEventContext(array $metadata): string
    {
        $context = collect($metadata)
            ->except(['before', 'after'])
            ->map(fn($value, $key) => Str::headline($key) . ': ' . $this->academicEventValue($value))
            ->implode(' | ');

        return $context ?: 'No additional context recorded.';
    }

    private function academicEventValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    private function academicEventChangeCount(array $metadata): int
    {
        $before = $metadata['before'] ?? [];
        $after = $metadata['after'] ?? [];

        if (! is_array($before) || ! is_array($after)) {
            return 0;
        }

        return collect(array_unique(array_merge(array_keys($before), array_keys($after))))
            ->filter(fn($key) => json_encode($before[$key] ?? null) !== json_encode($after[$key] ?? null))
            ->count();
    }

    private function academicEventType(?string $action): string
    {
        return Str::before($action ?? 'system', '.');
    }

    private function academicEventTypeLabel(?string $action): string
    {
        return Str::headline($this->academicEventType($action));
    }

    private function canEditGradesOperationally(ClassGroup $group): bool
    {
        return $group->academicPeriod?->canEditGrades()
            && ! in_array($group->status, [ClassGroup::STATUS_CANCELLED, ClassGroup::STATUS_CLOSED], true);
    }

    private function gradeLockReason(ClassGroup $group): string
    {
        if (! $group->academicPeriod) {
            return 'No academic period assigned.';
        }

        if (! $group->academicPeriod->canEditGrades()) {
            $status = $group->academicPeriod->status?->label
                ?? $group->academicPeriod->status?->name
                ?? Str::headline($group->academicPeriod->status?->code ?? 'not editable');

            return "Grades can only be edited while the period is in progress. Current status: {$status}.";
        }

        if (in_array($group->status, [ClassGroup::STATUS_CANCELLED, ClassGroup::STATUS_CLOSED], true)) {
            return 'Group is closed or cancelled.';
        }

        return 'Grade editing is locked.';
    }

    private function professorLoadSummary(Request $request, $activeStatusIds): array
    {
        $groups = ClassGroup::query()
            ->withCount([
                'subjectEnrollments as active_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds),
                'subjectEnrollments as graded_students_count' => fn($enrollments) => $enrollments
                    ->whereIn('status_id', $activeStatusIds)
                    ->whereHas('grade'),
                'schedules as published_schedules_count' => fn($schedules) => $schedules
                    ->where('status', ClassSchedule::STATUS_PUBLISHED),
            ])
            ->whereNotNull('professor_id')
            ->when($request->filled('search'), fn($query) => $query
                ->whereHas('professorProfile', fn($professor) => $this->applyProfessorSearch($professor, $request)))
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->get();

        return [
            'professors' => $groups->pluck('professor_id')->unique()->count(),
            'groups' => $groups->count(),
            'active_students' => $groups->sum('active_students_count'),
            'scheduled_blocks' => $groups->sum('published_schedules_count'),
            'pending_grades' => $groups->sum(fn($group) => max(0, (int) $group->active_students_count - (int) $group->graded_students_count)),
        ];
    }

    private function classroomOccupancySummary(Request $request, $activeStatusIds): array
    {
        $classrooms = Classroom::query()
            ->when($request->filled('building_id'), fn($query) => $query
                ->where('building_id', $request->integer('building_id')))
            ->when($request->filled('status'), fn($query) => $query
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), fn($query) => $this->applyClassroomSearch($query, $request))
            ->get();

        $schedules = $this->classroomOccupancyExportQuery($request, $activeStatusIds)->get();
        $conflicts = $this->classroomConflictScheduleIds($request);
        $totalCapacity = $classrooms->sum(fn($classroom) => (int) ($classroom->capacity ?? 0));
        $scheduledCapacity = $schedules->sum(fn($schedule) => (int) ($schedule->classroom?->capacity ?? 0));
        $activeStudents = $schedules->sum(fn($schedule) => (int) ($schedule->classGroup?->active_students_count ?? 0));

        return [
            'classrooms' => $classrooms->count(),
            'total_capacity' => $totalCapacity,
            'scheduled_blocks' => $schedules->count(),
            'assigned_groups' => $schedules->pluck('class_group_id')->unique()->count(),
            'conflicts' => $conflicts->count(),
            'average_utilization' => $scheduledCapacity > 0
                ? round(($activeStudents / $scheduledCapacity) * 100, 1)
                : 0,
        ];
    }

    private function groupCapacityConflictSummary(Request $request, $activeStatusIds, $conflictScheduleIds): array
    {
        $groups = $this->groupCapacityConflictQuery($request, $activeStatusIds)->get();
        $groupsWithPayload = $groups->map(fn($group) => $this->groupCapacityConflictPayload($group, $conflictScheduleIds));
        $totalCapacity = $groupsWithPayload->sum('capacity');
        $activeStudents = $groupsWithPayload->sum('active_students');

        return [
            'groups' => $groupsWithPayload->count(),
            'active_students' => $activeStudents,
            'total_capacity' => $totalCapacity,
            'available_seats' => $groupsWithPayload->sum('available_seats'),
            'full_groups' => $groupsWithPayload->filter(fn($group) => in_array('Full', $group['alerts'], true))->count(),
            'near_capacity' => $groupsWithPayload->filter(fn($group) => in_array('Near capacity', $group['alerts'], true))->count(),
            'conflicts' => $groupsWithPayload->filter(fn($group) => $group['conflict_blocks'] > 0)->count(),
            'utilization' => $totalCapacity > 0 ? round(($activeStudents / $totalCapacity) * 100, 1) : 0,
        ];
    }

    private function gradeOperationsSummary(Request $request, $activeStatusIds): array
    {
        $groups = $this->gradeOperationsQuery($request, $activeStatusIds)->get();
        $payloads = $groups->map(fn($group) => $this->gradeOperationsPayload($group));
        $activeStudents = $payloads->sum('active_students');
        $gradedStudents = $payloads->sum('graded_students');

        return [
            'groups' => $payloads->count(),
            'active_students' => $activeStudents,
            'graded_students' => $gradedStudents,
            'pending_grades' => $payloads->sum('pending_grades'),
            'locked_groups' => $payloads->filter(fn($group) => ! $group['can_edit_grades'])->count(),
            'completed_groups' => $payloads->filter(fn($group) => $group['active_students'] > 0 && $group['pending_grades'] === 0)->count(),
            'progress' => $activeStudents > 0 ? round(($gradedStudents / $activeStudents) * 100, 1) : 0,
        ];
    }

    private function academicEventsSummary(Request $request): array
    {
        $events = $this->academicEventsQuery($request)->get();

        return [
            'events' => $events->count(),
            'today' => $events->filter(fn($event) => $event->created_at?->isToday())->count(),
            'enrollment_events' => $events->filter(fn($event) => str_starts_with($event->action, 'enrollment.'))->count(),
            'schedule_events' => $events->filter(fn($event) => str_starts_with($event->action, 'schedule.'))->count(),
            'grade_events' => $events->filter(fn($event) => str_starts_with($event->action, 'grade.'))->count(),
            'period_events' => $events->filter(fn($event) => str_starts_with($event->action, 'academic_period.'))->count(),
            'actors' => $events->pluck('user_id')->filter()->unique()->count(),
        ];
    }

    private function allConflictScheduleIds(Request $request)
    {
        return $this->classroomConflictScheduleIds($request)
            ->merge($this->professorConflictScheduleIds($request))
            ->unique()
            ->values();
    }

    private function classroomConflictScheduleIds(Request $request)
    {
        return ClassSchedule::query()
            ->from('class_schedules as s1')
            ->join('class_schedules as s2', function ($join) {
                $join->on('s1.classroom_id', '=', 's2.classroom_id')
                    ->whereColumn('s1.id', '<', 's2.id')
                    ->whereColumn('s1.day', 's2.day')
                    ->whereColumn('s1.start_time', '<', 's2.end_time')
                    ->whereColumn('s2.start_time', '<', 's1.end_time');
            })
            ->join('class_groups as cg1', 's1.class_group_id', '=', 'cg1.id')
            ->join('class_groups as cg2', 's2.class_group_id', '=', 'cg2.id')
            ->where('s1.status', ClassSchedule::STATUS_PUBLISHED)
            ->where('s2.status', ClassSchedule::STATUS_PUBLISHED)
            ->when($request->filled('academic_period_id'), function ($query) use ($request) {
                $query
                    ->where('cg1.academic_period_id', $request->integer('academic_period_id'))
                    ->where('cg2.academic_period_id', $request->integer('academic_period_id'));
            })
            ->when($request->filled('building_id'), fn($query) => $query
                ->join('classrooms as cr', 's1.classroom_id', '=', 'cr.id')
                ->where('cr.building_id', $request->integer('building_id')))
            ->selectRaw('s1.id as first_id, s2.id as second_id')
            ->get()
            ->flatMap(fn($row) => [(int) $row->first_id, (int) $row->second_id])
            ->unique()
            ->values();
    }

    private function professorConflictScheduleIds(Request $request)
    {
        return ClassSchedule::query()
            ->from('class_schedules as s1')
            ->join('class_schedules as s2', function ($join) {
                $join->whereColumn('s1.id', '<', 's2.id')
                    ->whereColumn('s1.day', 's2.day')
                    ->whereColumn('s1.start_time', '<', 's2.end_time')
                    ->whereColumn('s2.start_time', '<', 's1.end_time');
            })
            ->join('class_groups as cg1', 's1.class_group_id', '=', 'cg1.id')
            ->join('class_groups as cg2', 's2.class_group_id', '=', 'cg2.id')
            ->whereColumn('cg1.professor_id', 'cg2.professor_id')
            ->whereNotNull('cg1.professor_id')
            ->where('s1.status', ClassSchedule::STATUS_PUBLISHED)
            ->where('s2.status', ClassSchedule::STATUS_PUBLISHED)
            ->when($request->filled('academic_period_id'), function ($query) use ($request) {
                $query
                    ->where('cg1.academic_period_id', $request->integer('academic_period_id'))
                    ->where('cg2.academic_period_id', $request->integer('academic_period_id'));
            })
            ->selectRaw('s1.id as first_id, s2.id as second_id')
            ->get()
            ->flatMap(fn($row) => [(int) $row->first_id, (int) $row->second_id])
            ->unique()
            ->values();
    }

    private function summary(Request $request, array $activeStatusCodes): array
    {
        $query = SubjectEnrollment::query()
            ->with(['status', 'subject'])
            ->when($request->filled('academic_period_id'), fn($query) => $query
                ->where('academic_period_id', $request->integer('academic_period_id')))
            ->when($request->filled('program_id'), fn($query) => $query
                ->whereHas('student', fn($student) => $student
                    ->where('program_id', $request->integer('program_id'))))
            ->when($request->filled('search'), fn($query) => $query
                ->whereHas('student', fn($student) => $this->applyStudentSearch($student, $request)))
            ->when($request->filled('professor_id'), fn($query) => $query
                ->whereHas('classGroup', fn($group) => $group
                    ->where('professor_id', $request->integer('professor_id'))))
            ->when($request->filled('status'), fn($query) => $query
                ->whereHas('status', fn($status) => $status
                    ->where('code', $request->string('status')->toString())));

        $enrollments = $query->get();
        $activeEnrollments = $enrollments->filter(fn($enrollment) => in_array($enrollment->status?->code, $activeStatusCodes, true));

        return [
            'students' => $enrollments->pluck('student_id')->unique()->count(),
            'assignments' => $enrollments->count(),
            'active_credits' => $activeEnrollments->sum(fn($enrollment) => (int) ($enrollment->subject?->credits ?? 0)),
            'minimum_credits' => config('enrollment.min_credits', 7),
        ];
    }
}

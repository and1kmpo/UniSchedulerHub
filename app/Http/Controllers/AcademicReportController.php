<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
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

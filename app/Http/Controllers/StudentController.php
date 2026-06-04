<?php

namespace App\Http\Controllers;

use App\Filters\StudentFilter;
use App\Http\Requests\StudentRequest;
use App\Models\Curriculum;
use App\Models\AcademicPeriod;
use App\Models\Grade;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request, StudentFilter $filters)
    {
        $students = $filters
            ->apply(
                Student::query()
                    ->with(['user', 'program', 'curriculum'])
                    ->withCount('enrollments')
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => $request->only([
                'search',
                'program',
                'academic_status',
                'semester',
                'sort',
                'direction',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Students/Create', $this->formOptions());
    }

    public function store(StudentRequest $request)
    {
        $validated = $request->validated();

        $student = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => User::STATUS_ACTIVE,
            ]);

            $user->assignRole('student');

            return $user->student()->create([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'semester' => $validated['semester'],
                'program_id' => $validated['program_id'],
                'curriculum_id' => $validated['curriculum_id'] ?? null,
                'academic_status' => $validated['academic_status'] ?? Student::STATUS_ACTIVE,
            ]);
        });

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Student created successfully',
                'data' => $student->load(['user', 'program', 'curriculum']),
            ], 201)
            : redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully');
    }

    public function show(Student $student)
    {
        $student
            ->load(['user', 'program', 'curriculum'])
            ->loadCount(['enrollments', 'enrollmentGrades']);

        $enrollments = $student
            ->enrollments()
            ->with([
                'subject',
                'academicPeriod',
                'status',
                'classGroup',
                'grade.state',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'enrollments' => $enrollments,
        ]);
    }

    public function edit(Student $student)
    {
        $student->load(['user', 'program', 'curriculum']);

        return Inertia::render('Students/Edit', [
            'student' => $student,
            ...$this->formOptions(),
        ]);
    }

    public function update(StudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($student, $validated) {
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $student->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $student->update([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'semester' => $validated['semester'],
                'program_id' => $validated['program_id'],
                'curriculum_id' => $validated['curriculum_id'] ?? null,
                'academic_status' => $validated['academic_status'] ?? $student->academic_status,
            ]);
        });

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Student updated successfully',
            ])
            : redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully');
    }

    public function destroy(Student $student)
    {
        $blockers = $this->deletionBlockers($student);

        if (! empty($blockers)) {
            return back()->withErrors([
                'message' => 'This student cannot be deleted because it is associated with: '
                    . implode(', ', $blockers)
                    . '. Remove those associations first.',
            ]);
        }

        try {
            $student->user?->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'message' => 'This student cannot be deleted because it is associated with other records.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully');
    }

    public function mySubjects()
    {
        $student = auth()->user()->student;
        $period = AcademicPeriod::active()->with('status')->first();

        $subjects = $student
            ->enrollments()
            ->with([
                'subject',
                'status',
                'classGroup.professor',
                'classGroup.schedules.classroom.building',
                'grade.state',
                'academicPeriod',
            ])
            ->when($period, fn($query) => $query->where('academic_period_id', $period->id))
            ->latest()
            ->get()
            ->map(fn($enrollment) => [
                'enrollment_id' => $enrollment->id,
                'id' => $enrollment->subject?->id,
                'name' => $enrollment->subject?->name,
                'credits' => $enrollment->subject?->credits,
                'status' => $enrollment->status?->code,
                'status_label' => $enrollment->status?->label,
                'professor_name' => $enrollment->classGroup?->professor?->name ?? 'Unassigned',
                'group' => $enrollment->classGroup?->code,
                'group_name' => $enrollment->classGroup?->name,
                'modality' => $enrollment->classGroup?->modality,
                'shift' => $enrollment->classGroup?->shift,
                'period' => $enrollment->academicPeriod?->name,
                'schedules' => $enrollment->classGroup?->schedules
                    ? $enrollment->classGroup->schedules->map(fn($schedule) => [
                        'id' => $schedule->id,
                        'day' => $schedule->day,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'classroom' => $schedule->classroom?->name,
                        'classroom_location' => $this->classroomLocation($schedule->classroom),
                    ])->values()
                    : [],
                'grade' => $enrollment->grade,
                'grade_state' => $enrollment->grade?->state,
            ])
            ->filter(fn($subject) => $subject['id'])
            ->values();

        $activeStatusCodes = config('enrollment.active_status_codes');
        $activeSubjects = $subjects->filter(fn($subject) => in_array($subject['status'], $activeStatusCodes, true));

        return Inertia::render('Students/MySubjects', [
            'subjects' => $subjects,
            'summary' => [
                'current_credits' => $activeSubjects->sum('credits'),
                'active_subjects' => $activeSubjects->count(),
                'graded_subjects' => $subjects->filter(fn($subject) => filled($subject['grade']?->final_grade))->count(),
            ],
            'currentPeriod' => $period ? [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
                'enrollment_deadline' => $period->enrollment_deadline,
                'unenrollment_deadline' => $period->unenrollment_deadline,
                'can_enroll' => $period->canEnroll()
                    && in_array($student->academic_status, Student::ENROLLABLE_STATUSES, true)
                    && filled($student->curriculum_id),
            ] : null,
        ]);
    }

    public function schedule()
    {
        $student = auth()->user()->student;
        $period = AcademicPeriod::active()->with('status')->first();

        return Inertia::render('Students/Schedule', [
            'currentSchedules' => $this->currentSchedulePayload($student, $period),
            'currentPeriod' => $period ? [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
            ] : null,
        ]);
    }

    public function viewGrades($subjectId)
    {
        $student = auth()->user()->student;
        $subject = Subject::findOrFail($subjectId);

        $grade = Grade::with('state')
            ->whereHas('enrollment', function ($query) use ($student, $subjectId) {
                $query
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subjectId);
            })
            ->latest()
            ->first();

        return Inertia::render('Students/SubjectGrades', [
            'subject' => $subject,
            'grade' => $grade,
        ]);
    }

    public function getGradeJson($subjectId)
    {
        $student = auth()->user()->student;

        $grade = Grade::with('state')
            ->whereHas('enrollment', function ($query) use ($student, $subjectId) {
                $query
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subjectId);
            })
            ->latest()
            ->first();

        return response()->json(['grade' => $grade]);
    }

    public function gradesSummary()
    {
        $student = auth()->user()->student;

        $gradesSummary = $student
            ->enrollments()
            ->with(['subject', 'grade.state'])
            ->get()
            ->map(fn($enrollment) => [
                'subject' => [
                    'id' => $enrollment->subject?->id,
                    'name' => $enrollment->subject?->name,
                ],
                'partial_1' => $enrollment->grade?->partial_1,
                'partial_2' => $enrollment->grade?->partial_2,
                'partial_3' => $enrollment->grade?->partial_3,
                'activities' => $enrollment->grade?->activities,
                'attendance' => $enrollment->grade?->attendance,
                'final_grade' => $enrollment->grade?->final_grade,
                'state' => $enrollment->grade?->state,
                'period' => $enrollment->academicPeriod?->name,
                'group' => $enrollment->classGroup?->code,
            ]);

        return response()->json(['grades' => $gradesSummary]);
    }

    private function currentSchedulePayload(Student $student, ?AcademicPeriod $period)
    {
        if (! $period) {
            return collect();
        }

        return $student
            ->enrollments()
            ->with([
                'status',
                'subject:id,code,name',
                'classGroup:id,code,name,subject_id,professor_id,modality,shift',
                'classGroup.professor:id,name',
                'classGroup.schedules.classroom.building:id,code,name',
            ])
            ->where('academic_period_id', $period->id)
            ->whereHas(
                'status',
                fn($query) => $query->whereIn('code', config('enrollment.active_status_codes'))
            )
            ->get()
            ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules
                ? $enrollment->classGroup->schedules
                    ->where('status', '!=', 'cancelled')
                    ->map(fn($schedule) => [
                        'id' => $schedule->id,
                        'day' => strtolower($schedule->day),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'subject' => [
                            'id' => $enrollment->subject?->id,
                            'code' => $enrollment->subject?->code,
                            'name' => $enrollment->subject?->name,
                        ],
                        'group' => [
                            'id' => $enrollment->classGroup?->id,
                            'code' => $enrollment->classGroup?->code,
                            'name' => $enrollment->classGroup?->name,
                            'modality' => $enrollment->classGroup?->modality,
                            'shift' => $enrollment->classGroup?->shift,
                        ],
                        'professor' => $enrollment->classGroup?->professor?->name,
                        'classroom' => $schedule->classroom?->name,
                        'classroom_location' => $this->classroomLocation($schedule->classroom),
                        'status' => $enrollment->status?->code,
                    ])
                : collect())
            ->values();
    }

    private function classroomLocation($classroom): ?string
    {
        if (! $classroom) {
            return null;
        }

        return collect([
            $classroom->name,
            $classroom->building?->name,
            $classroom->building?->code,
        ])
            ->filter()
            ->unique()
            ->join(' - ');
    }

    private function formOptions(): array
    {
        return [
            'programs' => Program::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'curricula' => Curriculum::query()
                ->select('id', 'program_id', 'name', 'code', 'is_active')
                ->orderBy('name')
                ->get(),
            'academicStatuses' => collect(array_merge(
                Student::ENROLLABLE_STATUSES,
                Student::BLOCKED_STATUSES
            ))
                ->map(fn($status) => [
                    'label' => str($status)->replace('_', ' ')->title()->toString(),
                    'value' => $status,
                ])
                ->values(),
        ];
    }

    private function deletionBlockers(Student $student): array
    {
        $relations = [
            'enrollments' => 'enrollments',
            'enrollmentGrades' => 'grades',
        ];

        $blockers = [];

        foreach ($relations as $relation => $label) {
            if ($student->{$relation}()->exists()) {
                $blockers[] = $label;
            }
        }

        return array_unique($blockers);
    }
}

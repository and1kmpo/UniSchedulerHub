<?php

namespace App\Http\Controllers;

use App\Filters\ProfessorFilter;
use App\Http\Requests\ProfessorRequest;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfessorController extends Controller
{
    public function index(Request $request, ProfessorFilter $filters)
    {
        $professors = $filters
            ->apply(
                Professor::query()
                    ->with('user')
                    ->withCount(['subjects', 'classGroups'])
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Professors/Index', [
            'professors' => $professors,
            'filters' => $request->only([
                'search',
                'sort',
                'direction',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Professors/Create');
    }

    public function store(ProfessorRequest $request)
    {
        $validated = $request->validated();

        $professor = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => User::STATUS_ACTIVE,
            ]);

            $user->assignRole('professor');

            return $user->professor()->create([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
            ]);
        });

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Professor created successfully',
                'data' => $professor->load('user'),
            ], 201)
            : redirect()
            ->route('professors.index')
            ->with('success', 'Professor created successfully');
    }

    public function show(Professor $professor)
    {
        $professor
            ->load(['user', 'subjects'])
            ->loadCount(['subjects', 'classGroups', 'grades']);

        $classGroups = $professor
            ->classGroups()
            ->with(['subject', 'academicPeriod'])
            ->withCount('subjectEnrollments')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Professors/Show', [
            'professor' => $professor,
            'classGroups' => $classGroups,
        ]);
    }

    public function edit(Professor $professor)
    {
        $professor->load('user');

        return Inertia::render('Professors/Edit', [
            'professor' => $professor,
        ]);
    }

    public function update(ProfessorRequest $request, Professor $professor)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($professor, $validated) {
            $professor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $professor->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $professor->update([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
            ]);
        });

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Professor updated successfully',
            ])
            : redirect()
            ->route('professors.index')
            ->with('success', 'Professor updated successfully');
    }

    public function destroy(Professor $professor)
    {
        $blockers = $this->deletionBlockers($professor);

        if (! empty($blockers)) {
            return back()->withErrors([
                'message' => 'This professor cannot be deleted because it is associated with: '
                    . implode(', ', $blockers)
                    . '. Remove those associations first.',
            ]);
        }

        try {
            $professor->user?->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'message' => 'This professor cannot be deleted because it is associated with other records.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('professors.index')
            ->with('success', 'Professor deleted successfully');
    }

    public function mySubjects()
    {
        $period = AcademicPeriod::active()->with('status')->first();

        if (! $period) {
            return Inertia::render('Professors/MySubjects', [
                'groups' => [],
                'period' => null,
                'summary' => [
                    'groups' => 0,
                    'students' => 0,
                    'credits' => 0,
                ],
                'systemState' => 'no_period',
            ]);
        }

        $groups = $this->currentProfessorGroups($period);

        return Inertia::render('Professors/MySubjects', [
            'groups' => $groups,
            'period' => $period ? [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
                'can_edit_grades' => $period->canEditGrades(),
            ] : null,
            'summary' => [
                'groups' => $groups->count(),
                'students' => $groups->sum('subject_enrollments_count'),
                'credits' => $groups->sum(fn($group) => $group['subject']['credits'] ?? 0),
            ],
            'systemState' => 'ready',
        ]);
    }

    public function schedule()
    {
        $period = AcademicPeriod::active()->with('status')->first();
        $groups = $period ? $this->currentProfessorGroups($period) : collect();
        $currentSchedules = $this->professorSchedulePayload($groups);

        return Inertia::render('Professors/Schedule', [
            'currentSchedules' => $currentSchedules,
            'currentPeriod' => $period ? [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
            ] : null,
            'summary' => [
                'groups' => $groups->count(),
                'blocks' => $currentSchedules->count(),
                'students' => $groups->sum('subject_enrollments_count'),
            ],
        ]);
    }

    private function currentProfessorGroups(AcademicPeriod $period)
    {
        return ClassGroup::with([
            'subject',
            'academicPeriod',
            'schedules.classroom.building',
            'subjectEnrollments' => fn($query) => $query->whereHas(
                'status',
                fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
            )->with(['student.user', 'status', 'grade.state']),
        ])
            ->where('professor_id', auth()->id())
            ->where('academic_period_id', $period->id)
            ->withCount([
                'subjectEnrollments' => fn($query) => $query->whereHas(
                    'status',
                    fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                ),
            ])
            ->get()
            ->map(fn($group) => [
                'id' => $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'status' => $group->status,
                'capacity' => $group->capacity,
                'modality' => $group->modality,
                'shift' => $group->shift,
                'subject_enrollments_count' => $group->subject_enrollments_count,
                'can_view_grades' => auth()->user()->can('manageGrades', $group),
                'can_edit_grades' => auth()->user()->can('editGrades', $group),
                'subject' => [
                    'id' => $group->subject?->id,
                    'name' => $group->subject?->name,
                    'code' => $group->subject?->code,
                    'knowledge_area' => $group->subject?->knowledge_area,
                    'credits' => $group->subject?->credits,
                ],
                'schedules' => $group->schedules->map(fn($schedule) => [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'classroom' => $schedule->classroom?->name,
                    'classroom_location' => $this->classroomLocation($schedule->classroom),
                    'status' => $schedule->status,
                ])->values(),
                'subject_enrollments' => $group->subjectEnrollments->map(fn($enrollment) => [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status?->code,
                    'student' => [
                        'id' => $enrollment->student?->id,
                        'name' => $enrollment->student?->user?->name,
                        'document' => $enrollment->student?->document,
                        'email' => $enrollment->student?->user?->email,
                    ],
                    'grade' => $enrollment->grade ? [
                        'final_grade' => $enrollment->grade->final_grade,
                        'state' => $enrollment->grade->state?->label,
                    ] : null,
                ])->values(),
            ]);
    }

    private function professorSchedulePayload($groups)
    {
        return $groups
            ->flatMap(fn($group) => collect($group['schedules'])
                ->where('status', '!=', 'cancelled')
                ->map(fn($schedule) => [
                    'id' => $schedule['id'],
                    'day' => strtolower($schedule['day']),
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'subject' => [
                        'id' => $group['subject']['id'],
                        'code' => $group['subject']['code'],
                        'name' => $group['subject']['name'],
                    ],
                    'group' => [
                        'id' => $group['id'],
                        'code' => $group['code'],
                        'name' => $group['name'],
                        'modality' => $group['modality'],
                        'shift' => $group['shift'],
                    ],
                    'professor' => 'You',
                    'classroom' => $schedule['classroom'],
                    'classroom_location' => $schedule['classroom_location'],
                    'status' => $group['status'],
                ]))
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

    public function viewAllStudents(Subject $subject)
    {
        $this->authorize('view', $subject);

        return Inertia::render('Subjects/ViewStudents', [
            'subject' => $subject,
            'students' => $subject->enrollments()
                ->with(['student.user', 'student.program'])
                ->paginate(10),
        ]);
    }

    private function deletionBlockers(Professor $professor): array
    {
        $relations = [
            'subjects' => 'teaching capabilities',
            'classGroups' => 'class groups',
            'grades' => 'grades',
        ];

        $blockers = [];

        foreach ($relations as $relation => $label) {
            if ($professor->{$relation}()->exists()) {
                $blockers[] = $label;
            }
        }

        return $blockers;
    }
}

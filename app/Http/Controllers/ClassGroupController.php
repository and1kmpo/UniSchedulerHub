<?php

namespace App\Http\Controllers;

use App\Filters\ClassGroupFilter;
use App\Http\Requests\ClassGroup\StoreClassGroupRequest;
use App\Http\Requests\ClassGroup\UpdateClassGroupRequest;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ClassGroupService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    public function index(Request $request, ClassGroupFilter $filters)
    {
        $classGroups = $filters
            ->apply(
                ClassGroup::query()
                    ->with(['subject', 'professor', 'academicPeriod'])
                    ->withCount([
                        'subjectEnrollments' => fn($query) => $query->whereHas(
                            'status',
                            fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                        ),
                    ])
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ClassGroups/Index', [
            'classGroups' => $classGroups,
            'filters' => $request->only([
                'search',
                'modality',
                'shift',
                'academic_period',
                'sort',
                'direction',
                'status',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('ClassGroups/Create', [
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
            'currentPeriodId' => AcademicPeriod::where('is_active', true)->first()?->id,
        ]);
    }

    public function store(StoreClassGroupRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $schedules = $data['schedules'];

            unset($data['schedules']);

            $group = ClassGroup::create($data);

            foreach ($schedules as $schedule) {
                $group->schedules()->create($schedule);
            }
        });

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Class group created successfully.');
    }

    public function show($id)
    {
        $group = ClassGroup::with([
            'subject',
            'professor',
            'academicPeriod',
            'schedules.classroom',
            'schedules.createdBy',
            'schedules.updatedBy',
            'schedules.cancelledBy',
            'subjectEnrollments.student',
            'subjectEnrollments.student.user',
            'subjectEnrollments.status',
        ])
            ->withCount([
                'subjectEnrollments' => fn($query) => $query->whereHas(
                    'status',
                    fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                ),
            ])
            ->findOrFail($id);

        $activeEnrollments = $group->subjectEnrollments
            ->filter(fn($enrollment) => in_array(
                $enrollment->status?->code,
                config('enrollment.active_status_codes'),
                true
            ));

        $canManageSchedules = $group->canManageSchedules()
            && $group->academicPeriod
            && ! $group->academicPeriod->isAcademicallyClosed();

        return Inertia::render('ClassGroups/Show', [
            'classGroup' => [
                'id' => $group->id,
                'code' => $group->code,
                'subject' => [
                    'id' => $group->subject->id,
                    'name' => $group->subject->name,
                ],
                'professor' => [
                    'id' => $group->professor->id,
                    'name' => $group->professor->name,
                ],
                'modality' => $group->modality,
                'shift' => $group->shift,
                'status' => $group->status,
                'capacity' => $group->capacity,
                'subject_enrollments_count' => $group->subject_enrollments_count,
                'can_manage_schedules' => $canManageSchedules,
                'students' => $activeEnrollments->map(fn($enrollment) => [
                    'id' => $enrollment->student->id,
                    'document' => $enrollment->student->document,
                    'name' => $enrollment->student->user->name,
                    'status' => [
                        'code' => $enrollment->status->code,
                        'description' => $enrollment->status->description,
                        'color' => $enrollment->status->color,
                    ],
                ])->values(),
                'schedules' => $group->schedules->map(fn($schedule) => [
                    'id' => $schedule->id,
                    'subject' => $group->subject->name,
                    'professor' => $group->professor?->name,
                    'classroom' => $schedule->classroom?->name,
                    'classroom_id' => $schedule->classroom_id,
                    'day' => $schedule->day,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $schedule->status,
                    'created_by' => $schedule->createdBy?->name,
                    'updated_by' => $schedule->updatedBy?->name,
                    'cancelled_by' => $schedule->cancelledBy?->name,
                    'cancelled_at' => $schedule->cancelled_at,
                ])->values(),
                'academicPeriod' => [
                    'id' => $group->academicPeriod?->id,
                    'name' => $group->academicPeriod?->name,
                    'is_active' => $group->academicPeriod?->is_active,
                    'state' => $group->academicPeriod?->state()?->value,
                ],
            ],
            'classrooms' => Classroom::with('building')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit($id)
    {
        $classGroup = ClassGroup::with('schedules')->findOrFail($id);

        return Inertia::render('ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateClassGroupRequest $request, $id)
    {
        $classGroup = ClassGroup::findOrFail($id);

        $classGroup->update($request->validated());

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Class group updated successfully.');
    }

    public function destroy($id, ClassGroupService $service)
    {
        $classGroup = ClassGroup::findOrFail($id);

        $result = $service->delete($classGroup);

        return redirect()
            ->route('class-groups.index')
            ->with(
                'success',
                $result === 'cancelled'
                    ? 'Class group has academic history and was cancelled.'
                    : 'Class group deleted'
            );
    }

    public function canEnroll(
        ClassGroup $classGroup,
        Student $student,
        EnrollmentService $service
    ) {
        return response()->json($service->canEnroll($student, $classGroup));
    }
}

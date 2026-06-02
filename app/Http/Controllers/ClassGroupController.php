<?php

namespace App\Http\Controllers;

use App\Filters\ClassGroupFilter;
use App\Http\Requests\ClassGroup\StoreClassGroupRequest;
use App\Http\Requests\ClassGroup\UpdateClassGroupRequest;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\EnrollmentService;
use App\Services\ClassGroupService;

class ClassGroupController extends Controller
{
    public function index(
        Request $request,
        ClassGroupFilter $filters
    ) {
        $classGroups = $filters
            ->apply(

                ClassGroup::query()
                    ->with([
                        'subject',
                        'professor',
                        'academicPeriod',
                    ])
                    ->withCount([
                        'subjectEnrollments' => fn($query) => $query->whereHas(
                            'status',
                            fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                        ),
                    ])
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'ClassGroups/Index',
            [

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
            ]
        );
    }

    public function create()
    {
        return Inertia::render('ClassGroups/Create', [
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
            'currentPeriodId' => AcademicPeriod::where('is_active', true)->first()?->id
        ]);
    }

    public function store(
        StoreClassGroupRequest $request
    ) {
        $data = $request->validated();

        DB::transaction(function () use ($data) {

            $schedules = $data['schedules'];

            unset($data['schedules']);

            $group = ClassGroup::create($data);

            foreach ($schedules as $schedule) {

                $group
                    ->schedules()
                    ->create($schedule);
            }
        });

        return redirect()
            ->route('class-groups.index')
            ->with(
                'success',
                'Class group created successfully.'
            );
    }


    public function show($id)
    {


        $editable = auth()->user()->hasRole('admin');
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
            'subjectEnrollments.status'
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

        // Lista de IDs de estudiante ya inscritos
        $enrolledIds = $activeEnrollments
            ->pluck('student_id')
            ->all();

        // Todos los usuarios con rol 'student' (para el dropdown de inscripción)
        $allStudents = User::role('student')
            ->with('student')
            ->get()
            ->filter(fn($u) => $u->student)
            ->map(fn($u) => [
                'id'   => $u->student->id,
                'document' => $u->student->document,
                'name' => $u->name,
            ])
            ->values();

        $studentSchedules = [];
        $canManageSchedules = $group->canManageSchedules()
            && $group->academicPeriod
            && ! $group->academicPeriod->isAcademicallyClosed();

        if ($editable) {
            $studentSchedules = \App\Models\SubjectEnrollment::with('classGroup.schedules')
                ->whereIn('student_id', $enrolledIds)
                ->get()
                ->flatMap(
                    fn($e) =>
                    $e->classGroup->schedules->map(fn($s) => [
                        'day' => $s->day,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ])
                )
                ->values();
        }

        return Inertia::render('ClassGroups/Show', [
            'classGroup' => [
                'id'                        => $group->id,
                'code'                      => $group->code,
                'subject'                   => [
                    'id'   => $group->subject->id,
                    'name' => $group->subject->name,
                ],
                'professor'                 => [
                    'id'   => $group->professor->id,
                    'name' => $group->professor->name,
                ],
                'modality'                  => $group->modality,
                'shift'                     => $group->shift,
                'status'                    => $group->status,
                'capacity'                  => $group->capacity,
                'subject_enrollments_count' => $group->subject_enrollments_count,
                'can_manage_schedules'       => $canManageSchedules,
                'students'                  => $activeEnrollments->map(fn($e) => [
                    'id'   => $e->student->id,
                    'document' => $e->student->document,
                    'name' => $e->student->user->name,
                    'status' => [
                        'code' => $e->status->code,
                        'description' => $e->status->description,
                        'color' => $e->status->color
                    ],
                ]),
                'schedules' => $group->schedules->map(fn($s) => [
                    'id' => $s->id,
                    'subject' => $group->subject->name,
                    'professor' => $group->professor?->name,
                    'classroom' => $s->classroom?->name,
                    'classroom_id' => $s->classroom_id,
                    'day' => $s->day,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'status' => $s->status,
                    'created_by' => $s->createdBy?->name,
                    'updated_by' => $s->updatedBy?->name,
                    'cancelled_by' => $s->cancelledBy?->name,
                    'cancelled_at' => $s->cancelled_at,
                ]),
                'academicPeriod' => [
                    'id' => $group->academicPeriod?->id,
                    'name' => $group->academicPeriod?->name,
                    'is_active' => $group->academicPeriod?->is_active,
                    'state' => $group->academicPeriod?->state()?->value,
                ],

            ],
            'allStudents' => $allStudents,
            'enrolledIds' => $enrolledIds,
            'studentSchedules' => $studentSchedules,

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

    public function update(
        UpdateClassGroupRequest $request,
        $id
    ) {
        $classGroup = ClassGroup::findOrFail($id);

        $classGroup->update(
            $request->validated()
        );

        return redirect()
            ->route('class-groups.index')
            ->with(
                'success',
                'Class group updated successfully.'
            );
    }

    public function destroy($id, ClassGroupService $service)
    {
        $classGroup = ClassGroup::findOrFail($id);

        $result = $service->delete($classGroup);

        return redirect()->route('class-groups.index')
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
        return response()->json(
            $service->canEnroll($student, $classGroup)
        );
    }

}

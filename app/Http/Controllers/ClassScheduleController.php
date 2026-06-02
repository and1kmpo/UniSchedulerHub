<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Services\ClassScheduleService;
use DomainException;

class ClassScheduleController extends Controller
{
    public function index($classGroupId)
    {
        $classGroup = ClassGroup::with(['subject', 'professor', 'schedules.classroom'])
            ->findOrFail($classGroupId);

        return Inertia::render('ClassSchedules/Index', [
            'classGroup' => $classGroup,
            'schedules'  => $classGroup->schedules,
        ]);
    }

    public function create(ClassGroup $classGroup)
    {
        return Inertia::render('ClassSchedules/Create', [
            'classGroup' => $classGroup->load(['subject', 'professor']),
            'classrooms' => Classroom::with('building')->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function edit(ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $this->ensureScheduleBelongsToGroup($classGroup, $schedule);

        return Inertia::render('ClassSchedules/Edit', [
            'classGroup' => $classGroup->load(['subject', 'professor']),
            'schedule' => $schedule->load('classroom'),
            'classrooms' => Classroom::with('building')->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(
        Request $request,
        ClassGroup $classGroup,
        ClassScheduleService $service
    ) {
        $data = $this->validateSchedule($request);

        try {
            $schedule = $service->create($classGroup, $data);
        } catch (DomainException $exception) {
            return $this->domainExceptionResponse($exception);
        }

        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'message' => 'Schedule created.',
                'schedule' => $schedule->load(['classroom', 'classGroup.subject', 'classGroup.professor']),
            ], 201);
        }

        return redirect()
            ->route('class-groups.show', $classGroup)
            ->with('success', 'Schedule created.');
    }

    public function update(
        Request $request,
        ClassGroup $classGroup,
        ClassSchedule $schedule,
        ClassScheduleService $service
    ) {
        $this->ensureScheduleBelongsToGroup($classGroup, $schedule);

        $data = $this->validateSchedule($request);

        try {
            $schedule = $service->update($schedule, $data);
        } catch (DomainException $exception) {
            return $this->domainExceptionResponse($exception);
        }

        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'message' => 'Schedule updated.',
                'schedule' => $schedule,
            ]);
        }

        return redirect()
            ->route('class-groups.show', $classGroup)
            ->with('success', 'Schedule updated.');
    }

    public function destroy(
        ClassGroup $classGroup,
        ClassSchedule $schedule,
        ClassScheduleService $service
    ) {
        $this->ensureScheduleBelongsToGroup($classGroup, $schedule);

        try {
            $service->delete($schedule);
        } catch (DomainException $exception) {
            return $this->domainExceptionResponse($exception);
        }

        return back()->with('success', 'Schedule deleted.');
    }

    public function calendar($classGroupId)
    {
        $classGroup = ClassGroup::with([
            'subject',
            'professor',
            'schedules.classGroup.subject',
            'schedules.classGroup.professor',
            'schedules.classroom',
        ])->findOrFail($classGroupId);

        $classrooms = Classroom::where('status', 'active')->orderBy('name')->get();

        $editable = auth()->check() && auth()->user()->hasRole('admin');

        /*   // temporal: inspección rápida
        return response()->json($classGroup->load('schedules.classGroup.professor', 'schedules.classroom')->toArray()); */


        return Inertia::render('ClassSchedules/Calendar', [
            'classGroup' => $classGroup,
            'schedules'  => $classGroup->schedules,
            'classrooms' => $classrooms,
            'editable' => $editable
        ]);
    }

    public function schedulesJson($classGroupId)
    {
        $schedules = ClassSchedule::with(
            'classroom',
            'classGroup.subject',
            'classGroup.professor'
        )
            ->where('class_group_id', $classGroupId)
            ->get();

        return response()->json($schedules);
    }

    private function ensureScheduleBelongsToGroup(ClassGroup $classGroup, ClassSchedule $schedule): void
    {
        abort_unless($schedule->class_group_id === $classGroup->id, 404);
    }

    private function validateSchedule(Request $request)
    {
        return $request->validate([
            'day'          => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'status'       => 'nullable|in:draft,published,cancelled,closed',
        ]);
    }

    /**
     * Standard OK response (returns JSON or redirects with flash message)
     */
    protected function expectsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson();
    }

    protected function domainExceptionResponse(DomainException $exception)
    {
        $messages = [
            'BLOCK_NO_ACADEMIC_PERIOD' => 'This class group has no academic period assigned.',
            'BLOCK_PERIOD_FROZEN' => 'This academic period is closed and schedules can no longer be changed.',
            'BLOCK_GROUP_SCHEDULE_LOCKED' => 'This class group status does not allow schedule changes.',
            'BLOCK_GROUP_SCHEDULE_CONFLICT' => 'This class group already has a schedule that overlaps this time.',
            'BLOCK_CLASSROOM_SCHEDULE_CONFLICT' => 'This classroom is already occupied during this time.',
            'BLOCK_PROFESSOR_SCHEDULE_CONFLICT' => 'This professor already has another class during this time.',
        ];

        throw ValidationException::withMessages([
            'schedule' => [
                $messages[$exception->getMessage()] ?? 'The schedule change is not allowed.',
            ],
        ]);
    }
}

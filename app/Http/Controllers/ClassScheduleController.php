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

        if ($conflict = $this->findConflict(
            ClassSchedule::where('class_group_id', $classGroup->id),
            $data['day'],
            $data['start_time'],
            $data['end_time']
        )) {
            return $this->conflictResponse($request, $conflict, 'group');
        }

        if (
            !empty($data['classroom_id']) &&
            ($conflict = $this->findConflict(
                ClassSchedule::where('classroom_id', $data['classroom_id']),
                $data['day'],
                $data['start_time'],
                $data['end_time']
            ))
        ) {
            return $this->conflictResponse($request, $conflict, 'classroom');
        }

        if ($conflict = $this->findProfessorConflict($classGroup, $data)) {
            return $this->conflictResponse($request, $conflict, 'professor');
        }

        try {
            $service->create($classGroup, $data);
        } catch (DomainException $exception) {
            return $this->domainExceptionResponse($exception);
        }

        if ($this->expectsJsonResponse($request)) {
            return response()->json(['message' => 'Schedule created.'], 201);
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
        $data = $this->validateSchedule($request);

        if ($conflict = $this->findConflict(
            $classGroup->schedules(),
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $schedule->id
        )) {
            return $this->conflictResponse($request, $conflict, 'group');
        }

        if (
            !empty($data['classroom_id']) &&
            ($conflict = $this->findConflict(
                ClassSchedule::where('classroom_id', $data['classroom_id']),
                $data['day'],
                $data['start_time'],
                $data['end_time'],
                $schedule->id
            ))
        ) {
            return $this->conflictResponse($request, $conflict, 'classroom');
        }

        if ($conflict = $this->findProfessorConflict($classGroup, $data, $schedule->id)) {
            return $this->conflictResponse($request, $conflict, 'professor');
        }

        try {
            $service->update($schedule, $data);
        } catch (DomainException $exception) {
            return $this->domainExceptionResponse($exception);
        }

        if ($this->expectsJsonResponse($request)) {
            return response()->json(['message' => 'Schedule updated.']);
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
     * Checks for a scheduling conflict using TIME comparisons.
     * $query must be a Builder instance (e.g., ClassSchedule::where(...))
     */
    private function findConflict($query, string $day, string $start, string $end, $ignoreId = null)
    {
        // Use whereTime to avoid format mismatches ('H:i' vs 'H:i:s')
        return $query
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('status', '!=', 'cancelled')
            ->where('day', $day)
            ->whereTime('start_time', '<', $end)
            ->whereTime('end_time', '>', $start)
            ->with(['classGroup.subject', 'classroom'])
            ->first();
    }

    private function findProfessorConflict(ClassGroup $classGroup, array $data, $ignoreId = null)
    {
        return ClassSchedule::query()
            ->whereHas('classGroup', fn($query) => $query->where('professor_id', $classGroup->professor_id))
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->where('status', '!=', 'cancelled')
            ->where('day', $data['day'])
            ->whereTime('start_time', '<', $data['end_time'])
            ->whereTime('end_time', '>', $data['start_time'])
            ->with(['classGroup.subject', 'classroom'])
            ->first();
    }

    /**
     * Standard response for schedule conflicts.
     * Returns 422 with validation-style format:
     * { errors: { start_time: [ "message" ] } }
     */
    protected function conflictResponse(Request $request, $conflict, string $type)
    {
        $subjectName = $conflict->classGroup->subject->name ?? ($conflict->classGroup->name ?? 'Subject');
        $startTime = $conflict->start_time;
        $endTime = $conflict->end_time;
        $roomName = $conflict->classroom->name ?? 'no classroom';

        if ($type === 'group') {
            $message = "Conflict: this group already has {$subjectName} from {$startTime} to {$endTime} (room: {$roomName}).";
        } elseif ($type === 'classroom') {
            $message = "Conflict: room {$roomName} is already occupied by {$subjectName} from {$startTime} to {$endTime}.";
        } else {
            $message = "Conflict: this professor already teaches {$subjectName} from {$startTime} to {$endTime}.";
        }

        throw ValidationException::withMessages([
            'schedule' => [$message]
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
        ];

        throw ValidationException::withMessages([
            'schedule' => [
                $messages[$exception->getMessage()] ?? 'The schedule change is not allowed.',
            ],
        ]);
    }
}

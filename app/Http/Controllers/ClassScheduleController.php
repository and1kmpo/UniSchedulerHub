<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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

    public function store(Request $request, ClassGroup $classGroup)
    {
        $data = $this->validateSchedule($request);

        // Conflict same group
        if ($conflict = $this->findConflict(
            ClassSchedule::where('class_group_id', $classGroup->id),
            $data['day'],
            $data['start_time'],
            $data['end_time']
        )) {
            return $this->conflictResponse($request, $conflict, 'group');
        }

        // Conflict same classroom
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

        // If all is OK, SAVE
        $classGroup->schedules()->create($data);

        return $this->successResponse($request, 'Schedule created.', 201);
    }

    public function update(Request $request, ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $data = $this->validateSchedule($request);

        // Conflicto en el mismo grupo (ignorando este mismo horario)
        if ($conflict = $this->findConflict(
            $classGroup->schedules(),
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $schedule->id
        )) {
            return $this->conflictResponse($request, $conflict, 'group');
        }

        // Conflicto en el mismo aula (ignorando este mismo horario)
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

        $schedule->update($data);

        return $request->expectsJson()
            ? response()->json(['message' => 'Schedule updated.'])
            : back()->with('success', 'Schedule updated.');
    }

    public function destroy(ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $schedule->delete();
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

        $editable = auth()->user()->hasRole('admin');

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
            ->where('day', $day)
            ->whereTime('start_time', '<', $end)
            ->whereTime('end_time', '>', $start)
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
        } else {
            $message = "Conflict: room {$roomName} is already occupied by {$subjectName} from {$startTime} to {$endTime}.";
        }

        throw ValidationException::withMessages([
            'schedule' => [$message]
        ]);
    }

    /**
     * Standard OK response (returns JSON or redirects with flash message)
     */
    protected function successResponse(Request $request, string $message, int $status = 200)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->header('X-Inertia')) {
            return response()->json(['message' => $message], $status);
        }
        return back()->with('success', $message);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassScheduleController extends Controller
{
    public function index($classGroupId)
    {
        $classGroup = ClassGroup::with(['subject', 'professor', 'schedules'])->findOrFail($classGroupId);

        return Inertia::render('ClassSchedules/Index', [
            'classGroup' => $classGroup,
            'schedules' => $classGroup->schedules,
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup)
    {
        $data = $this->validateSchedule($request);

        if ($this->hasConflict($classGroup, $data)) {
            return back()->withErrors(['start_time' => 'There is a schedule conflict.']);
        }

        $classGroup->schedules()->create($data);
        return back()->with('success', 'Schedule created.');
    }

    public function update(Request $request, ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $data = $this->validateSchedule($request);

        if ($this->hasConflict($classGroup, $data, $schedule->id)) {
            return back()->withErrors(['start_time' => 'There is a schedule conflict.']);
        }

        $schedule->update($data);
        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Schedule deleted.');
    }

    private function validateSchedule(Request $request)
    {
        return $request->validate([
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom' => 'nullable|string|max:255',
        ]);
    }

    private function hasConflict($classGroup, $data, $ignoreId = null): bool
    {
        return $classGroup->schedules()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('day', $data['day'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_time', '<', $data['start_time'])
                            ->where('end_time', '>', $data['end_time']);
                    });
            })->exists();
    }

    public function calendar($classGroupId)
    {
        $classGroup = ClassGroup::with(['schedules', 'subject', 'professor'])->findOrFail($classGroupId);

        return Inertia::render('ClassSchedules/Calendar', [
            'classGroup' => $classGroup,
            'schedules' => $classGroup->schedules, // Pasamos eventos
        ]);
    }

    public function schedulesJson($classGroupId)
    {
        $schedules = ClassSchedule::where('class_group_id', $classGroupId)->get([
            'id',
            'day',
            'start_time',
            'end_time',
            'classroom'
        ]);
        return response()->json($schedules);
    }
}

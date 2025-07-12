<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ClassGroup $classGroup)
    {
        return Inertia::render('ClassSchedules/Index', [
            'classGroup' => $classGroup->load('subject', 'professor'),
            'schedules' => $classGroup->schedules()->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ClassGroup $classGroup)
    {
        return Inertia::render('ClassSchedules/Create', [
            'classGroup' => $classGroup,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom' => 'nullable|string|max:255',
        ]);

        // Validar traslape de horarios dentro del mismo grupo
        $conflict = $classGroup->schedules()
            ->where('day', $data['day'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_time', '<', $data['start_time'])
                            ->where('end_time', '>', $data['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'There is a schedule conflict.']);
        }

        $classGroup->schedules()->create($data);

        return redirect()->route('class-schedules.index', $classGroup)->with('success', 'Schedule added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassGroup $classGroup, ClassSchedule $schedule)
    {
        return Inertia::render('ClassSchedules/Edit', [
            'classGroup' => $classGroup,
            'schedule' => $schedule,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $data = $request->validate([
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom' => 'nullable|string|max:255',
        ]);

        // Evitar traslapes (excluyendo este mismo horario)
        $conflict = $classGroup->schedules()
            ->where('id', '!=', $schedule->id)
            ->where('day', $data['day'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_time', '<', $data['start_time'])
                            ->where('end_time', '>', $data['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'There is a schedule conflict.']);
        }

        $schedule->update($data);

        return redirect()->route('class-schedules.index', $classGroup)->with('success', 'Schedule updated');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassGroup $classGroup, ClassSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('class-schedules.index', $classGroup)->with('success', 'Schedule deleted');
    }
}

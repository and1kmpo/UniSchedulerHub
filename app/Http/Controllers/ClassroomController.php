<?php

namespace App\Http\Controllers;

use App\Filters\ClassroomFilter;
use App\Http\Requests\ClassroomRequest;
use App\Models\Building;
use App\Models\Classroom;
use App\Services\ClassroomService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index(Request $request, ClassroomFilter $filters)
    {
        $classrooms = $filters
            ->apply(
                Classroom::query()
                    ->with('building')
                    ->withCount('schedules')
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Classrooms/Index', [
            'classrooms' => $classrooms,
            'buildings' => Building::orderBy('name')->get(['id', 'name', 'code']),
            'filters' => $request->only([
                'search',
                'building',
                'floor',
                'status',
                'sort',
                'direction',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Classrooms/Create', [
            'buildings' => Building::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(ClassroomRequest $request, ClassroomService $service)
    {
        $service->create($request->validated());

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom created successfully.');
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'floor' => ['required', 'integer', 'min:-5'],
        ]);

        $building = Building::findOrFail($validated['building_id']);
        $floor = $validated['floor'];

        $count = Classroom::where('building_id', $building->id)
            ->where('floor', $floor)
            ->withTrashed()
            ->count() + 1;

        $consecutive = str_pad($count, 2, '0', STR_PAD_LEFT);

        return response()->json([
            'name' => "{$building->code}-F{$floor}-{$consecutive}",
            'consecutive' => $count,
        ]);
    }

    public function show(Classroom $classroom)
    {
        $classroom->load([
            'building',
            'schedules.classGroup.subject',
            'schedules.classGroup.professor',
        ])->loadCount('schedules');

        return Inertia::render('Classrooms/Show', [
            'classroom' => $classroom,
        ]);
    }

    public function edit(Classroom $classroom)
    {
        return Inertia::render('Classrooms/Edit', [
            'classroom' => $classroom,
            'buildings' => Building::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom, ClassroomService $service)
    {
        $service->update($classroom, $request->validated());

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Classroom $classroom, ClassroomService $service)
    {
        try {
            $service->delete($classroom);

            return redirect()
                ->route('classrooms.index')
                ->with('success', 'Classroom deleted successfully.');
        } catch (\DomainException) {
            return back()->withErrors([
                'error' => 'This classroom is currently in use.',
            ]);
        }
    }

    public function restore(int $id)
    {
        Classroom::withTrashed()->findOrFail($id)->restore();

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom restored successfully.');
    }

    public function schedule(Classroom $classroom)
    {
        $classroom->load([
            'schedules.classGroup.subject',
            'schedules.classGroup.professor',
        ]);

        $days = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        $classroom->setRelation(
            'schedules',
            $classroom->schedules
                ->sort(fn($a, $b) => ($days[strtolower($a->day)] ?? 8) <=> ($days[strtolower($b->day)] ?? 8)
                    ?: strcmp($a->start_time, $b->start_time))
                ->values()
        );

        return Inertia::render('Classrooms/Schedule', [
            'classroom' => $classroom,
        ]);
    }
}

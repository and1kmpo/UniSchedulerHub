<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Building;
use App\Models\ClassGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::with('building')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Classrooms/Index', [
            'classrooms' => $classrooms
        ]);
    }

    public function create()
    {
        $buildings = Building::orderBy('name')->get();

        return Inertia::render('Classrooms/Create', [
            'buildings' => $buildings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'floor'       => 'required|integer|min:0',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $building = Building::findOrFail($validated['building_id']);
        $prefix   = $building->code;
        $floor    = $validated['floor'];

        $count = Classroom::where('building_id', $building->id)
            ->where('floor', $floor)
            ->count() + 1;

        $consecutive = str_pad($count, 2, '0', STR_PAD_LEFT);
        $name = "{$prefix}-F{$floor}-{$consecutive}";

        Classroom::create([
            'name'        => $name,
            'building_id' => $building->id,
            'floor'       => $floor,
            'capacity'    => $validated['capacity'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'floor'       => 'required|integer|min:0',
        ]);

        $building = Building::findOrFail($validated['building_id']);
        $floor    = $validated['floor'];

        $count = Classroom::where('building_id', $building->id)
            ->where('floor', $floor)
            ->count() + 1;

        $consecutive = str_pad($count, 2, '0', STR_PAD_LEFT);
        $name = "{$building->code}-F{$floor}-{$consecutive}";

        return response()->json([
            'name'        => $name,
            'consecutive' => $count,
        ]);
    }
    public function show(Classroom $classroom)
    {
        return inertia('Classrooms/Show', [
            'classroom' => $classroom
        ]);
    }


    public function edit(Classroom $classroom)
    {

        if ($classroom->status !== 'active') {
            return redirect()
                ->route('classrooms.index')
                ->with('error', 'This classroom is inactive and cannot be edited.');
        }


        $buildings = Building::orderBy('name')->get();

        return Inertia::render('Classrooms/Edit', [
            'classroom' => $classroom,
            'buildings' => $buildings
        ]);
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'floor'       => 'required|integer|min:0',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Si el building o floor cambió
        if ($validated['building_id'] != $classroom->building_id || $validated['floor'] != $classroom->floor) {
            // Inactivar el aula actual
            $classroom->update(['status' => 'inactive']);

            // Crear nueva aula con lógica de store()
            $building = Building::findOrFail($validated['building_id']);
            $prefix   = $building->code;
            $floor    = $validated['floor'];

            $count = Classroom::where('building_id', $building->id)
                ->where('floor', $floor)
                ->count() + 1;

            $consecutive = str_pad($count, 2, '0', STR_PAD_LEFT);
            $name = "{$prefix}-F{$floor}-{$consecutive}";

            Classroom::create([
                'name'        => $name,
                'building_id' => $building->id,
                'floor'       => $floor,
                'capacity'    => $validated['capacity'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->route('classrooms.index')->with('success', 'Aula movida correctamente. Se creó un nuevo registro.');
        }

        // Si no cambió la ubicación, actualizar normalmente
        $classroom->update([
            'capacity'    => $validated['capacity'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }


    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('success', 'Classroom deleted successfully');
    }

    public function schedule($id)
    {
        $classroom = Classroom::with([
            'schedules.classGroup.subject',
            'schedules.classGroup.professor',
        ])->findOrFail($id);

        // Ordenar los schedules manualmente
        $sortedSchedules = $classroom->schedules->sort(function ($a, $b) {
            $days = [
                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
                'sunday' => 7,
            ];

            $dayA = $days[strtolower($a->day)] ?? 8;
            $dayB = $days[strtolower($b->day)] ?? 8;

            if ($dayA === $dayB) {
                return strcmp($a->start_time, $b->start_time);
            }

            return $dayA <=> $dayB;
        });


        $classroom->setRelation('schedules', $sortedSchedules->values());

        return Inertia::render('Classrooms/Schedule', [
            'classroom' => $classroom,
        ]);
    }
}

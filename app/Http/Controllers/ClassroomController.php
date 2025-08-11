<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Building;
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
        $buildings = Building::orderBy('name')->get();

        return Inertia::render('Classrooms/Edit', [
            'classroom' => $classroom,
            'buildings' => $buildings
        ]);
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }


    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('success', 'Classroom deleted successfully');
    }
}

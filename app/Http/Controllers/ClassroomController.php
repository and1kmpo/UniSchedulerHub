<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Building;
use Illuminate\Http\Request;
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
            'name'        => 'required|string|max:255',
            'building_id' => 'nullable|exists:buildings,id',
            'floor'       => 'nullable|integer|min:0',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')
            ->with('success', 'Classroom created successfully');
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
            'name'        => 'required|string|max:255',
            'building_id' => 'nullable|exists:buildings,id',
            'floor'       => 'nullable|integer|min:0',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')
            ->with('success', 'Classroom updated successfully');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('success', 'Classroom deleted successfully');
    }
}

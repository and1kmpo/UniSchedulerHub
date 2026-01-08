<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('id', 'desc')->get();

        return Inertia::render('Programs/Index', [
            'programs' => $programs
        ]);
    }

    public function create()
    {
        return Inertia::render('Programs/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Program::create($validated);

        return redirect()->route('programs.index')->with('success', 'Programa creado correctamente.');
    }


    public function edit(Program $program)
    {
        return Inertia::render('Programs/Edit', [
            'program' => $program
        ]);
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')->with('success', 'Programa actualizado.');
    }

    public function destroy(Program $program)
    {
        $program->delete();
        return redirect()->route('programs.index')->with('success', 'Programa eliminado correctamente.');
    }
}

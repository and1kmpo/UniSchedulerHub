<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Requests\ProgramRequest;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct()
    {
        // Middleware para proteger las rutas
        $this->middleware('role:admin')->except(['index', 'show']);
        $this->middleware('permission:manage programs')->only(['index', 'show']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            // Si es una petición axios (Accept: application/json)
            return response()->json(Program::all());
        }

        // Para navegación normal en el panel (Inertia)
        $programs = Program::paginate(5);
        return inertia('Programs/Index', ['programs' => $programs]);
    }
    public function create()
    {
        return inertia('Programs/Create');
    }

    public function store(ProgramRequest $request)
    {
        $validatedData = $request->validated();
        Program::create($validatedData);
        return redirect()->route('programs.index');
    }

    public function show(Program $program)
    {
        return inertia('Programs/Show', ['program' => $program]);
    }

    public function edit(Program $program)
    {
        return inertia('Programs/Edit', ['program' => $program]);
    }

    public function update(ProgramRequest $request, Program $program)
    {
        $validatedData = $request->validated();
        $program->update($validatedData);
        return redirect()->route('programs.index');
    }

    public function destroy(Program $program)
    {
        if ($program->students()->exists()) {
            return back()->withErrors('This program has associated students and cannot be deleted.');
        }

        $program->delete();
        return redirect()->route('programs.index');
    }
}

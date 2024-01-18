<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfessorRequest;
use Illuminate\Http\Request;
use App\Models\Professor;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professors = Professor::paginate(5);

        if (request()->wantsJson()) {
            return response()->json($professors);
        }

        return inertia('Professors/Index', ['professors' => $professors]);
    }

    public function create()
    {
        return inertia('Professors/Create');
    }

    public function store(ProfessorRequest $request)
    {
        $validatedData = $request->validated();
        Professor::create($validatedData);
        return redirect()->route('professors.index');
    }

    public function edit(Professor $professor)
    {
        return inertia('Professors/Edit', ['professor' => $professor]);
    }

    public function update(ProfessorRequest $request, Professor $professor)
    {
        $validatedData = $request->validated();
        $professor->update($validatedData);
        return redirect()->route('professors.index');
    }

    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professors.index');
    }

    public function assignSubjectForm()
    {
        $professors = Professor::all();
        return inertia('Professors/AssignSubject', ['professors' => $professors]);
    }

    public function getAssignedSubjects($professorId)
    {
        $professor = Professor::findOrFail($professorId);
        $assignedSubjects = $professor ? $professor->subjects : [];

        return response()->json($assignedSubjects);
    }

    public function assignSubjects(Request $request)
    {
        try {
            $request->validate([
                'professor_id' => 'required|exists:professors,id',
                'subject_ids' => 'required|array',
            ]);

            $professorId = $request->input('professor_id');
            $subjectIds = $request->input('subject_ids');

            $professor = Professor::findOrFail($professorId);
            $professor->subjects()->syncWithoutDetaching($subjectIds);

            return redirect()->route('professors.assignSubjectForm')->with('success', 'Asignaturas asignadas con éxito.');
        } catch (\Exception $exception) {
            return back()->with('error', 'Error al asignar asignaturas: ' . $exception->getMessage());
        }
    }

    public function unassignSubject($professorId, $subjectId)
    {
        try {
            $professor = Professor::findOrFail($professorId);
            $professor->subjects()->detach($subjectId);

            return response()->json(['success' => true, 'message' => 'Asignatura desasignada con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al desasignar asignatura.'], 500);
        }
    }
}

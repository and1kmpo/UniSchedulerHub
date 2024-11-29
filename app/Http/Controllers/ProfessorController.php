<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\Professor;
use App\Http\Requests\ProfessorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professors = User::role('Professor')->with('professor')->paginate(5);

        if (request()->wantsJson()) {
            return response()->json($professors);
        }

        return inertia('Professors/Index', ['professors' => $professors]);
    }

    public function create()
    {
        return inertia('Professors/Create');
    }

    public function store(Request $request)
    {
        //
    }


    public function edit(User $professor)
    {
        return inertia('Professors/Edit', ['professor' => $professor->load('professor')]);
    }

    public function update(ProfessorRequest $request, User $professor)
    {
        $validatedData = $request->validated();

        $professor->professor()->update($validatedData);
        return redirect()->route('professors.index');
    }

    public function destroy(User  $professor)
    {
        $professor->professor()->delete();
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
            ], [
                'professor_id.required' => 'A professor must be selected.',
                'professor_id.exists' => 'The selected professor does not exist.',
                'subject_ids.required' => 'At least one subject must be selected.',
            ]);

            if (empty($request->input('professor_id'))) {
                return response()->json(['error' => 'Professor ID is missing.'], 400);
            }


            $professorId = $request->input('professor_id');
            $subjectIds = $request->input('subject_ids');

            $professor = Professor::findOrFail($professorId);
            $professor->subjects()->syncWithoutDetaching($subjectIds);

            return response()->json(['message' => 'Subjects assigned successfully.'], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => 'Error when assigning subject: ' . $exception->getMessage()], 500);
        }
    }

    public function unassignSubject($professorId, $subjectId)
    {
        try {
            $professor = Professor::findOrFail($professorId);
            $professor->subjects()->detach($subjectId);

            return response()->json(['success' => true, 'message' => 'Subject successfully unassigned.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error when unassigning subject.'], 500);
        }
    }

    public function unassignSelectedSubjects(Request $request)
    {
        try {
            $request->validate([
                'professor_id' => 'required|exists:professors,id',
                'subject_ids' => 'required|array',
            ]);

            $professor = Professor::findOrFail($request->professor_id);
            $professor->subjects()->detach($request->subject_ids);

            return response()->json(['success' => true, 'message' => 'Subjects successfully unassigned.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error when unassigning subjects.'], 500);
        }
    }
}

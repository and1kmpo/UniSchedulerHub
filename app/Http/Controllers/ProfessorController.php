<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\Professor;
use App\Http\Requests\ProfessorRequest;
use Illuminate\Http\Request;



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

    public function store(ProfessorRequest $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'document' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $user = User::create(
            [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]
        );

        $user->assignRole('Professor');

        $user->professor()->create([
            'document' => $validatedData['document'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
        ]);

        return redirect()->route('professors.index');
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
            ]);

            $professorId = $request->input('professor_id');
            $subjectIds = $request->input('subject_ids');

            $professor = Professor::findOrFail($professorId);
            $professor->subjects()->syncWithoutDetaching($subjectIds);

            return redirect()->route('professors.assignSubjectForm')->with('success', 'Successfully assigned subjects.');
        } catch (\Exception $exception) {
            return back()->with('error', 'Error when assigning subject: ' . $exception->getMessage());
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

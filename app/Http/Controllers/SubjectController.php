<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\Student;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::paginate(5);

        if (request()->wantsJson()) {
            return response()->json($subjects);
        }

        return inertia('Subjects/Index', ['subjects' => $subjects]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Subjects/Create');
    }

    /**
     * 
     * Store a newly created resource in storage.
     * @param App\Http\Requests\SubjectRequest
     * @return \illuminate\Http\Response
     */
    public function store(SubjectRequest $request)
    {
        $validatedData = $request->validated();
        Subject::create($validatedData);
        return redirect()->route('subjects.index');
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
    public function edit(Subject $subject)
    {
        return inertia('Subjects/Edit', ['subject' => $subject]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(SubjectRequest $request, Subject $subject)
    {
        $validatedData = $request->validated();
        $subject->update($validatedData);
        return redirect()->route('subjects.index');
    }

    /**
     * @param Program $program
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        $hasStudents = Student::whereHas('subjects', function ($query) use ($subject) {
            $query->where('subject_id', $subject->id);
        })->exists();

        if ($hasStudents) {
            return response()->json(['error' => 'This subject has associated students and cannot be eliminated.'], 422);
        }

        $subject->delete();
        return response()->json(['message' => 'Subject successfully deleted.']);
    }

    public function getSubjectsWithProfessors()
    {
        // Get only subjects with an assigned professor
        $subjectsWithProfessors = Subject::has('professors')->get();

        return response()->json($subjectsWithProfessors);
    }
}

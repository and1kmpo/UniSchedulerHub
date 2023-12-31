<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::paginate(5);
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
        $subject->delete();
        return redirect()->route('subject.index');
    }
}

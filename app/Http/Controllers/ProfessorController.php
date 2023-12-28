<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfessorRequest;
use App\Http\Requests\SubjectRequest;
use App\Models\Professor;
use App\Models\Subject;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professors = Professor::paginate(5);
        return inertia('Professors/Index', ['professors' => $professors]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Professors/Create');
    }

    /**
     * 
     * Store a newly created resource in storage.
     * @param App\Http\Requests\ProfessorRequest
     * @return \illuminate\Http\Response
     */
    public function store(ProfessorRequest $request)
    {
        $validatedData = $request->validated();
        Professor::create($validatedData);
        return redirect()->route('professors.index');
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
    public function edit(Professor $professor)
    {
        return inertia('Professors/Edit', ['professor' => $professor]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProfessorRequest $request, Professor $professor)
    {
        $validatedData = $request->validated();
        $professor->update($validatedData);
        return redirect()->route('professors.index');
    }

    /**
     * @param Program $program
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professors.index');
    }
}

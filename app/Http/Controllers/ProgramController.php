<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Requests\ProgramRequest;
use App\Models\Student;

use function Laravel\Prompts\error;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programs = Program::paginate(5);
        return inertia('Programs/Index', ['programs' => $programs]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Programs/Create');
    }

    /**
     * 
     * Store a newly created resource in storage.
     * @param App\Http\Requests\ProgramRequest
     * @return \illuminate\Http\Response
     */

    public function store(ProgramRequest $request)
    {
        $validatedData = $request->validated();
        Program::create($validatedData);
        return redirect()->route('programs.index');
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
    public function edit(Program $program)
    {
        return inertia('Programs/Edit', ['program' => $program]);
    }

    /**
     *  @param App\Http\Requests\ProgramRequest
     * @param Program $program
     * Update the specified resource in storage.
     */
    public function update(ProgramRequest $request, Program $program)
    {
        $validatedData = $request->validated();
        $program->update($validatedData);
        return redirect()->route('programs.index');
    }


    /**
     * @param Program $program
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        $hasStudents = $program->students()->exists();

        if ($hasStudents) {
            return response()->json(['error' => 'This program has associated students and cannot be eliminated.'], 422);
        }

        $program->delete();
        return redirect()->route('programs.index');
    }
}

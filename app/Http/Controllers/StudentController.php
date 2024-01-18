<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StudentRequest;
use App\Models\Program;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with('program')->paginate(5);
        return inertia('Students/Index', ['students' => $students]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $programs = Program::all()->pluck('name', 'id');
        return inertia('Students/Create', ['programs' => $programs]);
    }

    /**
     * 
     * Store a newly created resource in storage.
     * @param App\Http\Requests\StudentRequest
     * @return \illuminate\Http\Response
     */
    public function store(StudentRequest $request)
    {
        $validatedData = $request->validated();
        Student::create($validatedData);
        return redirect()->route('students.index');
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
    public function edit(Student $student)
    {
        // Obtén la lista de programas
        $programs = Program::all()->pluck('name', 'id');

        return inertia('Students/Edit', [
            'student' => $student,
            'programs' => $programs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
        $validatedData = $request->validated();
        $student->update($validatedData);
        return redirect()->route('students.index');
    }

    /**
     * @param Program $program
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index');
    }
}

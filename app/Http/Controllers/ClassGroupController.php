<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('ClassGroups/Index', [
            'classGroups' => ClassGroup::with('subject', 'professor')->latest()->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ClassGroups/Create', [
            'subjects' => Subject::all(['id', 'name']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:class_groups',
            'name' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'professor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
        ]);

        ClassGroup::create($data);

        return redirect()->route('class-groups.index')->with('success', 'Class group created');
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
    public function edit(ClassGroup $classGroup)
    {
        return Inertia::render('ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'subjects' => Subject::all(['id', 'name']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'code' => 'required|unique:class_groups,code,' . $classGroup->id,
            'name' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'professor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
        ]);

        $classGroup->update($data);

        return redirect()->route('class-groups.index')->with('success', 'Class group updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassGroup $classGroup)
    {
        $classGroup->delete();

        return redirect()->route('class-groups.index')->with('success', 'Class group deleted');
    }
}

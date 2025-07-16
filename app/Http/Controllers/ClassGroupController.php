<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    public function index()
    {
        return Inertia::render('ClassGroups/Index', [
            'classGroups' => ClassGroup::with('subject', 'professor')->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('ClassGroups/Create', [
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'professor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'modality' => 'required|string',
            'shift' => 'required|string',
            // 'code' => 'required|unique:class_groups,code',
            // 'group_code' => 'required|string|unique:class_groups,group_code',
            // 'name' => 'required|string|max:255',
            // 'semester' => 'required|string',
        ]);

        Log::info('Creating ClassGroup with data:', $data);

        ClassGroup::create($data);

        return redirect()->route('class-groups.index')->with('success', 'Class group created');
    }


    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $classGroup = ClassGroup::findOrFail($id);

        return Inertia::render('ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $classGroup = ClassGroup::findOrFail($id);

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

    public function destroy($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $classGroup->delete();

        return redirect()->route('class-groups.index')->with('success', 'Class group deleted');
    }
}

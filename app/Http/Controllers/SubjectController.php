<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::paginate(5);

        if (request()->wantsJson()) {
            return response()->json($subjects);
        }

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects
        ]);
    }

    public function create()
    {
        return Inertia::render('Subjects/Create');
    }

    public function store(SubjectRequest $request)
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create($request->validated());

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Subject created successfully',
                'data' => $subject
            ], 201)
            : redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully');
    }

    public function show(Subject $subject)
    {
        $this->authorize('view', $subject);

        return Inertia::render('Subjects/Show', [
            'subject' => $subject,
        ]);
    }

    public function edit(Subject $subject)
    {
        $this->authorize('update', $subject);

        return Inertia::render('Subjects/Edit', [
            'subject' => $subject
        ]);
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
        $this->authorize('update', $subject);

        $subject->update($request->validated());

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Subject updated successfully'
            ])
            : redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully');
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);

        $hasStudents = $subject->students()->exists();

        if ($hasStudents) {
            return response()->json([
                'error' => 'This subject has associated students and cannot be deleted.'
            ], 422);
        }

        $subject->delete();

        return response()->json([
            'message' => 'Subject successfully deleted.'
        ]);
    }

    public function getSubjectsWithProfessors()
    {
        $subjects = Subject::with('professors')
            ->has('professors')
            ->get();

        return response()->json($subjects);
    }
}

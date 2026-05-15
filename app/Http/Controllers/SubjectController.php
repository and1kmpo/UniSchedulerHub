<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use Illuminate\Database\QueryException;

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

        $blockers = $this->deletionBlockers($subject);

        if (! empty($blockers)) {
            return back()->withErrors([
                'message' => 'This subject cannot be deleted because it is associated with: '
                    . implode(', ', $blockers)
                    . '. Remove those associations first.'
            ]);
        }

        try {
            $subject->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'message' => 'This subject cannot be deleted because it is associated with other records.'
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully');
    }

    private function deletionBlockers(Subject $subject): array
    {
        $relations = [
            'professors' => 'professors',
            'students' => 'students',
            'enrollments' => 'enrollments',
            'classGroups' => 'class groups',
            'curricula' => 'curricula',
            'programs' => 'programs',
            'grades' => 'grades',
            'prerequisites' => 'prerequisites',
            'isPrerequisiteFor' => 'subjects that use it as a prerequisite',
        ];

        $blockers = [];

        foreach ($relations as $relation => $label) {
            if ($subject->{$relation}()->exists()) {
                $blockers[] = $label;
            }
        }

        return $blockers;
    }

    public function getSubjectsWithProfessors()
    {
        $subjects = Subject::with('professors')
            ->has('professors')
            ->get();

        return response()->json($subjects);
    }
}

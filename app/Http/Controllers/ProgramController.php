<?php

namespace App\Http\Controllers;

use App\Filters\ProgramFilter;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index(Request $request, ProgramFilter $filters)
    {
        $programs = $filters
            ->apply(
                Program::query()
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Programs/Index', [

            'programs' => $programs,

            'filters' => $request->only([
                'search',
                'sort',
                'direction',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Programs/Create');
    }

    public function store(ProgramRequest $request)
    {
        $program = Program::create($request->validated());

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Program created successfully',
                'data' => $program,
            ], 201)
            : redirect()
            ->route('programs.index')
            ->with('success', 'Program created successfully');
    }


    public function edit(Program $program)
    {
        return Inertia::render('Programs/Edit', [
            'program' => $program
        ]);
    }

    public function update(ProgramRequest $request, Program $program)
    {
        $program->update($request->validated());

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Program updated successfully',
            ])
            : redirect()
            ->route('programs.index')
            ->with('success', 'Program updated successfully');
    }

    public function show(Program $program)
    {
        $program
            ->load('activeCurriculum')
            ->loadCount([
                'students',
                'subjects',
                'curricula',
            ]);

        $students = $program
            ->students()
            ->with('user')
            ->paginate(10, ['*'], 'students_page')
            ->withQueryString();

        $subjects = $program
            ->subjects()
            ->paginate(10, ['subjects.*'], 'subjects_page')
            ->withQueryString();

        return Inertia::render('Programs/Show', [
            'program' => $program,
            'students' => $students,
            'subjects' => $subjects,
        ]);
    }

    public function destroy(Program $program)
    {
        $blockers = $this->deletionBlockers($program);

        if (! empty($blockers)) {
            return back()->withErrors([
                'message' => 'This program cannot be deleted because it is associated with: '
                    . implode(', ', $blockers)
                    . '. Remove those associations first.',
            ]);
        }

        try {
            $program->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'message' => 'This program cannot be deleted because it is associated with other records.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program deleted successfully');
    }

    private function deletionBlockers(Program $program): array
    {
        $relations = [
            'students' => 'students',
            'subjects' => 'subjects',
            'curricula' => 'curricula',
        ];

        $blockers = [];

        foreach ($relations as $relation => $label) {
            if ($program->{$relation}()->exists()) {
                $blockers[] = $label;
            }
        }

        return $blockers;
    }
}

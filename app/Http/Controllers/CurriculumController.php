<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\Program;
use App\Rules\SingleActiveCurriculum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurriculumController extends Controller
{
    public function index()
    {
        return Inertia::render('Curricula/Index', [
            'curricula' => Curriculum::with('program')
                ->orderByDesc('is_active')
                ->orderBy('valid_from')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Curricula/Create', [
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'code'       => ['required', 'string', 'unique:curricula,code'],
            'name'       => ['required', 'string'],
            'valid_from' => ['required', 'date'],
            'valid_to'   => ['nullable', 'date', 'after:valid_from'],
            'is_active'  => [
                'boolean',
                new SingleActiveCurriculum($request->program_id),
            ],
        ]);

        Curriculum::create($request->all());

        return redirect()
            ->route('curricula.index')
            ->with('success', 'Curriculum created successfully.');
    }
}

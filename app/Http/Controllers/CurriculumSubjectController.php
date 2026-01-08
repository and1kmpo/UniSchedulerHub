<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use Illuminate\Http\Request;

class CurriculumSubjectController extends Controller
{
    public function store(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'subject_id'            => ['required', 'exists:subjects,id'],
            'semester_recommended'  => ['required', 'integer', 'min:1'],
            'credits'               => ['required', 'integer', 'min:1'],
            'type'                  => ['required', 'in:required,elective'],
            'area_id'               => ['nullable', 'exists:subject_areas,id'],
        ]);

        $curriculum->subjects()->attach(
            $request->subject_id,
            $request->only([
                'semester_recommended',
                'credits',
                'type',
                'area_id',
            ])
        );

        return back()->with('success', 'Subject added to curriculum.');
    }
}

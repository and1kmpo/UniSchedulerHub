<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Services\GradeService;

class GradeController extends Controller
{
    public function index(ClassGroup $group)
    {
        $this->authorize('manageGrades', $group);

        $group->loadMissing(['subject', 'academicPeriod']);

        $enrollments = $group->subjectEnrollments()
            ->with([
                'student.user',
                'grade.state',
                'status'
            ])
            ->get()
            ->sortBy(fn($e) => $e->student->user->name)
            ->values();

        return Inertia::render('Grades/Manage', [
            'group' => $group,
            'subject' => $group->subject,
            'academicPeriod' => $group->academicPeriod,

            'canEdit' => auth()->user()->can('editGrades', $group),

            'enrollments' => $enrollments->map(fn($enrollment) => [
                'id' => $enrollment->id,
                'student' => [
                    'id' => $enrollment->student->id,
                    'name' => $enrollment->student->user->name,
                ],
                'grade' => $enrollment->grade ? [
                    'partial_1'   => $enrollment->grade->partial_1,
                    'partial_2'   => $enrollment->grade->partial_2,
                    'partial_3'   => $enrollment->grade->partial_3,
                    'activities'  => $enrollment->grade->activities,
                    'attendance'  => $enrollment->grade->attendance,
                    'final_grade' => $enrollment->grade->final_grade,
                    'state' => $enrollment->grade->state ? [
                        'code'  => $enrollment->grade->state->code,
                        'label' => $enrollment->grade->state->label,
                    ] : null,
                ] : null,
                'status' => $enrollment->status->code,
            ]),
        ]);
    }

    public function store(Request $request, ClassGroup $group, GradeService $gradeService)
    {
        $this->authorize('manageGrades', $group);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.first_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.second_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.third_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.activities' => 'nullable|numeric|min:0|max:5',
            'grades.*.attendance' => 'nullable|numeric|min:0|max:100',
        ]);

        $professorId = auth()->user()->professor->id;
        $updatedGrades = [];

        foreach ($request->grades as $enrollmentId => $gradeData) {

            $enrollment = $group->subjectEnrollments()
                ->with('academicPeriod')
                ->findOrFail($enrollmentId);

            $updatedGrades[$enrollmentId] = $gradeService
                ->update($enrollment, $gradeData, $professorId);
        }

        return response()->json([
            'success' => true,
            'updated_grades' => $updatedGrades,
        ]);
    }
}

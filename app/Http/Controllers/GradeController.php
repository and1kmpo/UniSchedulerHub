<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ClassGroup;
use App\Services\GradeService;
use DomainException;
use Illuminate\Validation\ValidationException;

class GradeController extends Controller
{
    public function index(ClassGroup $group)
    {
        $this->authorize('manageGrades', $group);

        $group->loadMissing(['subject', 'academicPeriod']);

        $enrollments = $group->subjectEnrollments()
            ->whereHas(
                'status',
                fn($query) => $query->whereIn('code', config('enrollment.active_status_codes'))
            )
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
                    'document' => $enrollment->student->document,
                ],
                'can_edit' => $enrollment->canEditGrades(),
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
        $this->authorize('editGrades', $group);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.first_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.second_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.third_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.activities' => 'nullable|numeric|min:0|max:5',
            'grades.*.attendance' => 'nullable|numeric|min:0|max:100',
        ]);

        $professorId = auth()->user()->professor?->id;
        $updatedGrades = [];

        try {
            foreach ($request->grades as $enrollmentId => $gradeData) {

                $enrollment = $group->subjectEnrollments()
                    ->with(['academicPeriod', 'classGroup', 'status'])
                    ->findOrFail($enrollmentId);

                $updatedGrades[$enrollmentId] = $gradeService
                    ->update($enrollment, $gradeData, $professorId);
            }
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'grades' => [
                    $this->domainMessage($exception->getMessage()),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'updated_grades' => $updatedGrades,
        ]);
    }

    private function domainMessage(string $code): string
    {
        return [
            'BLOCK_NO_ACADEMIC_PERIOD' => 'This enrollment has no academic period assigned.',
            'BLOCK_PERIOD_FROZEN' => 'This academic period is closed and grades can no longer be changed.',
            'BLOCK_PERIOD_DOES_NOT_ALLOW_GRADES' => 'This academic period does not allow grade editing.',
            'BLOCK_ENROLLMENT_DOES_NOT_ALLOW_GRADES' => 'One or more enrollments do not allow grade editing.',
            'BLOCK_GROUP_DOES_NOT_ALLOW_GRADES' => 'This class group does not allow grade editing.',
        ][$code] ?? 'The grade change is not allowed.';
    }
}

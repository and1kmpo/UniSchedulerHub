<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ClassGroup;
use App\Models\Professor;
use App\Services\GradeService;
use DomainException;
use Illuminate\Validation\ValidationException;

class GradeController extends Controller
{
    public function index(ClassGroup $group)
    {
        $this->authorize('manageGrades', $group);

        $group->loadMissing(['subject', 'academicPeriod.status']);

        $enrollments = $group->subjectEnrollments()
            ->whereHas(
                'status',
                fn($query) => $query->whereIn('code', config('enrollment.active_status_codes'))
            )
            ->with([
                'student.user',
                'grade.state',
                'grade.createdBy',
                'grade.updatedBy',
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
            'lockReason' => $this->lockReason($group),
            'storeRoute' => route('groups.grades.store', $group),

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
                    'updated_at' => $enrollment->grade->updated_at?->toISOString(),
                    'updated_by' => $enrollment->grade->updatedBy?->name,
                    'created_by' => $enrollment->grade->createdBy?->name,
                ] : null,
                'status' => $enrollment->status->code,
            ]),
        ]);
    }

    public function indexByGroup(ClassGroup $classGroup)
    {
        return $this->index($classGroup);
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

        $professorId = auth()->user()->professor?->id
            ?? Professor::where('user_id', $group->professor_id)->value('id');
        $updatedGrades = [];

        try {
            foreach ($request->grades as $enrollmentId => $gradeData) {

                $enrollment = $group->subjectEnrollments()
                    ->with(['academicPeriod', 'classGroup', 'status'])
                    ->findOrFail($enrollmentId);

                $updatedGrades[$enrollmentId] = $gradeService
                    ->update($enrollment, $gradeData, $professorId)
                    ->load(['state', 'createdBy', 'updatedBy']);
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
            'updated_grades' => collect($updatedGrades)->map(fn($grade) => $this->gradePayload($grade)),
        ]);
    }

    public function storeByGroup(Request $request, ClassGroup $classGroup, GradeService $gradeService)
    {
        return $this->store($request, $classGroup, $gradeService);
    }

    private function gradePayload($grade): array
    {
        return [
            'partial_1' => $grade->partial_1,
            'partial_2' => $grade->partial_2,
            'partial_3' => $grade->partial_3,
            'activities' => $grade->activities,
            'attendance' => $grade->attendance,
            'final_grade' => $grade->final_grade,
            'state' => $grade->state ? [
                'code' => $grade->state->code,
                'label' => $grade->state->label,
            ] : null,
            'updated_at' => $grade->updated_at?->toISOString(),
            'updated_by' => $grade->updatedBy?->name,
            'created_by' => $grade->createdBy?->name,
        ];
    }

    private function lockReason(ClassGroup $group): ?string
    {
        if (! $group->academicPeriod) {
            return 'This group has no academic period assigned.';
        }

        if (! $group->academicPeriod->canEditGrades()) {
            $status = $group->academicPeriod->status?->label
                ?? $group->academicPeriod->status?->code
                ?? 'not editable';

            return "Grades can only be edited while the academic period is in progress. Current period status: {$status}.";
        }

        if (in_array($group->status, [
            ClassGroup::STATUS_CANCELLED,
            ClassGroup::STATUS_CLOSED,
        ], true)) {
            return 'Grades are locked because this class group is closed or cancelled.';
        }

        if (! auth()->user()->can('editGrades', $group)) {
            return 'You do not have permission to edit grades for this group.';
        }

        return null;
    }

    private function domainMessage(string $code): string
    {
        return [
            'BLOCK_NO_ACADEMIC_PERIOD' => 'This enrollment has no academic period assigned.',
            'BLOCK_PERIOD_FROZEN' => 'This academic period is closed and grades can no longer be changed.',
            'BLOCK_PERIOD_DOES_NOT_ALLOW_GRADES' => 'This academic period does not allow grade editing.',
            'BLOCK_ENROLLMENT_DOES_NOT_ALLOW_GRADES' => 'One or more enrollments do not allow grade editing.',
            'BLOCK_GROUP_DOES_NOT_ALLOW_GRADES' => 'This class group does not allow grade editing.',
            'BLOCK_NO_PROFESSOR_ASSIGNED' => 'This class group has no professor record assigned for grade ownership.',
        ][$code] ?? 'The grade change is not allowed.';
    }
}

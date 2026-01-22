<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\GradeStatus;
use App\Enums\GradeStatuses;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use Illuminate\Support\Facades\Log;

class GradeController extends Controller
{
    public function index(ClassGroup $group)
    {
        $enrollments = $group->subjectEnrollments()->with([
            'student.user',
            'grade.state',
            'status'
        ])->get()
            ->sortBy(fn($e) => $e->student->user->name)
            ->values();


        return Inertia::render('Grades/Manage', [
            'group' => $group,
            'subject' => $group->subject,
            'academicPeriod' => $group->academicPeriod,
            'canEdit' => $group->academicPeriod->isInProgress(),
            'enrollments' => $enrollments->map(fn($enrollment) => [
                'id' => $enrollment->id,
                'student' => [
                    'id' => $enrollment->student->id,
                    'name' => $enrollment->student->user->name,
                ],
                'grade' => optional($enrollment->grade),
                'status' => $enrollment->status->code,
            ]),
        ]);
    }

    public function store(Request $request, ClassGroup $group)
    {
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

        foreach ($request->grades as $enrollmentId  => $gradeData) {

            $enrollment = $group->subjectEnrollments()
                ->with('AcademicPeriod')
                ->findOrFail($enrollmentId);

            if (!$enrollment->academicPeriod->isInProgress()) {
                abort(403, 'Grades can only be edited while the academic period is in progress.');
            }

            $updatedGrades[$enrollmentId] = $this->updateStudentGrade(
                $enrollment,
                $gradeData,
                $professorId
            );
        }

        return response()->json([
            'success' => true,
            'updated_grades' => $updatedGrades,
        ]);
    }

    private function updateStudentGrade(SubjectEnrollment $enrollment, array $gradeData, int $professorId)
    {
        $finalGrade = $this->calculateFinalGrade($gradeData);
        $statusCode = $this->determineStatus($gradeData, $finalGrade);

        $state = GradeStatus::where('code', $statusCode)->first();

        $grade = Grade::updateOrCreate(
            ['subject_enrollment_id' => $enrollment->id],
            [
                'professor_id' => $professorId,
                'partial_1' => $gradeData['first_exam'] ?? null,
                'partial_2' => $gradeData['second_exam'] ?? null,
                'partial_3' => $gradeData['third_exam'] ?? null,
                'activities' => $gradeData['activities'] ?? null,
                'attendance' => $gradeData['attendance'] ?? null,
                'final_grade' => $finalGrade,
                'grade_status_id' => optional($state)->id
            ]
        );

        return $grade->load('state');
    }

    private function calculateFinalGrade($data): ?float
    {
        $required = ['first_exam', 'second_exam', 'third_exam', 'activities'];

        foreach ($required as $key) {
            if (!isset($data[$key]) || !is_numeric($data[$key])) {
                return null; // Nota incompleta, no se calcula aún
            }
        }

        return round(
            ($data['first_exam'] ?? 0) * 0.25 +
                ($data['second_exam'] ?? 0) * 0.25 +
                ($data['third_exam'] ?? 0) * 0.30 +
                ($data['activities'] ?? 0) * 0.20,
            2
        );
    }

    private function determineStatus(array $gradeData, ?float $finalGrade): ?string
    {
        if (
            $finalGrade === null ||
            !isset($gradeData['attendance']) ||
            !is_numeric($gradeData['attendance'])
        ) {
            return null;
        }

        if ($gradeData['attendance'] < 80) {
            return GradeStatuses::FAILED_ATTENDANCE->value;
        }

        return $finalGrade >= 3.0
            ? GradeStatuses::PASSED->value
            : GradeStatuses::FAILED->value;
    }
}

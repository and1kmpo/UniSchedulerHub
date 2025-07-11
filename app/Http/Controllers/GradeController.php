<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeState;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\GradeStatus;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\Log;

class GradeController extends Controller
{
    public function index(Subject $subject)
    {
        $students = $subject->students()->with('user')->get();

        $grades = Grade::where('subject_id', $subject->id)
            ->with(['student.user', 'state'])
            ->get()
            ->keyBy('student_id');

        return Inertia::render('Grades/Manage', [
            'subject' => $subject,
            'students' => $students,
            'grades' => $grades,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array',
            'grades.*.first_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.second_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.third_exam' => 'nullable|numeric|min:0|max:5',
            'grades.*.activities' => 'nullable|numeric|min:0|max:5',
            'grades.*.attendance' => 'nullable|numeric|min:0|max:100',
        ]);

        $professorId = auth()->user()->professor->id;
        $updatedGrades = [];

        foreach ($request->grades as $studentId => $gradeData) {
            $updatedGrades[$studentId] = $this->updateStudentGrade(
                $studentId,
                $request->subject_id,
                $gradeData,
                $professorId
            );
        }

        return response()->json([
            'success' => true,
            'updated_grades' => $updatedGrades,
        ]);
    }

    private function updateStudentGrade($studentId, $subjectId, $gradeData, $professorId)
    {
        $finalGrade = $this->calculateFinalGrade($gradeData);
        $statusCode = $this->determineStatus($gradeData, $finalGrade);

        $state = GradeState::where('code', $statusCode)->first();

        if (!$state) {
            Log::warning("No se encontró un estado con código: {$statusCode}");
            $stateId = null;
        } else {
            Log::info("Se encontró estado: {$state->code} con ID {$state->id}");
            $stateId = $state->id;
        }

        $grade = Grade::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
            ],
            [
                'professor_id' => $professorId,
                'partial_1' => $gradeData['first_exam'] ?? null,
                'partial_2' => $gradeData['second_exam'] ?? null,
                'partial_3' => $gradeData['third_exam'] ?? null,
                'activities' => $gradeData['activities'] ?? null,
                'attendance' => $gradeData['attendance'] ?? null,
                'final_grade' => $finalGrade,
                'grade_state_id' => $stateId,
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
            return GradeStatus::FAILED_ATTENDANCE->value;
        }

        return $finalGrade >= 3.0
            ? GradeStatus::PASSED->value
            : GradeStatus::FAILED->value;
    }
}

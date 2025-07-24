<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class SubjectEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $student = $user->student;

        // Obtener materias del programa
        $subjects = $student->program
            ->subjects()
            ->with('prerequisites')
            ->get()
            ->map(function ($subject) use ($student) {
                // Revisar si cumple los prerrequisitos
                $hasAllPrerequisites = $subject->prerequisites->every(function ($prereq) use ($student) {
                    return SubjectEnrollment::where('student_id', $student->id)
                        ->where('subject_id', $prereq->id)
                        ->where('status', 'approved')
                        ->exists();
                });

                // Ver si ya está inscrito o la ha cursado
                $alreadyEnrolled = SubjectEnrollment::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->exists();

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'credits' => $subject->credits,
                    'semester' => $subject->pivot->semester,
                    'hasAllPrerequisites' => $hasAllPrerequisites,
                    'alreadyEnrolled' => $alreadyEnrolled,
                ];
            });

        return Inertia::render('Students/SubjectEnrollment', [
            'subjects' => $subjects,
        ]);
    }

    public function enroll(Request $request, Subject $subject)
    {
        try {
            $student = $request->user()->student;

            $isInProgram = $student->program
                ->subjects()
                ->where('subjects.id', $subject->id)
                ->exists();

            if (!$isInProgram) {
                return response()->json(['error' => 'This subject is not part of your program.'], 403);
            }

            $missing = $subject->prerequisites->filter(function ($prereq) use ($student) {
                return !SubjectEnrollment::where('student_id', $student->id)
                    ->where('subject_id', $prereq->id)
                    ->where('status', 'approved')
                    ->exists();
            });

            if ($missing->isNotEmpty()) {
                return response()->json(['error' => 'You have not passed the required prerequisites.'], 422);
            }

            $already = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->exists();

            if ($already) {
                return response()->json(['error' => 'You have already enrolled in or completed this subject.'], 422);
            }

            $period = AcademicPeriod::where('is_active', true)->first();

            if (!$period) {
                return response()->json(['error' => 'There is no active academic period.'], 500);
            }

            // ✅ Verificación correcta de fecha límite
            if ($period->enrollment_deadline) {
                $deadline = Carbon::parse($period->enrollment_deadline)->endOfDay();
                if (now()->greaterThan($deadline)) {
                    return response()->json(['error' => 'The enrollment deadline has passed.'], 403);
                }
            }


            SubjectEnrollment::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'status' => 'approved', // o "enrolled" si deseas una etapa previa
            ]);

            return response()->json(['message' => 'Enrollment successful.']);
        } catch (\Exception $e) {
            Log::error('Enrollment error', ['exception' => $e]);
            return response()->json([
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function unenroll(Request $request, Subject $subject)
    {
        $student = $request->user()->student;

        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        if (! $activePeriod) {
            return response()->json(['error' => 'There is no active academic period.'], 500);
        }

        // ✅ Verificación robusta de la fecha límite de cancelación
        if (
            $activePeriod->unenrollment_deadline &&
            now()->greaterThan(Carbon::parse($activePeriod->unenrollment_deadline)->endOfDay())
        ) {
            return response()->json(['error' => 'The unenrollment deadline has passed.'], 403);
        }

        $enrollment = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $activePeriod->id)
            ->first();

        if (! $enrollment) {
            return response()->json(['error' => 'You are not enrolled in this subject.'], 404);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Unenrollment successful.']);
    }
}

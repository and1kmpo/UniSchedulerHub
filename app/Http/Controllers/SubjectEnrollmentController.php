<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $student = $request->user()->student;

        // Check if the subject is part of the student's program
        $isInProgram = $student->program
            ->subjects()
            ->where('subjects.id', $subject->id)
            ->exists();

        if (! $isInProgram) {
            return response()->json(['error' => 'This subject is not part of your program.'], 403);
        }

        // Check prerequisites
        $missing = $subject->prerequisites->filter(function ($prereq) use ($student) {
            return !SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $prereq->id)
                ->where('status', 'approved')
                ->exists();
        });

        if ($missing->isNotEmpty()) {
            return response()->json(['error' => 'You have not passed the required prerequisites.'], 422);
        }

        // Prevent duplicate enrollment
        $already = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->exists();

        if ($already) {
            return response()->json(['error' => 'You have already enrolled in or completed this subject.'], 422);
        }

        // Get the active academic period
        $period = AcademicPeriod::where('is_active', true)->first();

        if (! $period) {
            return response()->json(['error' => 'There is no active academic period.'], 500);
        }

        SubjectEnrollment::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'status' => 'approved', // Change to 'enrolled' or 'pending' if applicable
        ]);

        return response()->json(['message' => 'Enrollment successful.']);
    }
}

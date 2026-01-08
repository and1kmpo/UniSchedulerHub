<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\EnrollmentService;


class GroupEnrollmentController extends Controller
{
    public function index()
    {
        /* return Inertia::render('Admin/GroupEnrollments/Index', [
            'classGroups' => ClassGroup::with('subject', 'professor')->withCount('subjectEnrollments')->latest()->paginate(15),
        ]); */
    }

    public function show(ClassGroup $classGroup)
    {
        $group = $classGroup->load(['subject', 'schedules', 'professor'])->loadCount('subjectEnrollments');

        $enrollments = $classGroup->subjectEnrollments()
            ->with(['student', 'status'])
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'student_name' => $e->student->user->name,
                'code' => $e->student->code,
                'status' => $e->status->code,
                'statusColor' => $e->status->color,
            ]);

        return Inertia::render('Admin/GroupEnrollments/Show', [
            'group' => [
                'id' => $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'subject' => $group->subject->name,
                'professor' => optional($group->professor->user)->name,
                'schedules' => $group->schedules->map(fn($s) => [
                    'day' => $s->day,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                ]),
                'subject_enrollments_count' => $group->subject_enrollments_count
            ],
            'enrollments' => $enrollments
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup, EnrollmentService $service)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id']
        ]);

        $student = Student::findOrFail($request->student_id);

        $result = $service->canEnroll($student, $classGroup);

        if (! $result['allowed']) {
            return response()->json([
                'message' => $result['message']
            ], 422);
        }

        // Crear inscripción
        SubjectEnrollment::create([
            'student_id' => $student->id,
            'subject_id' => $classGroup->subject_id,
            'academic_period_id' => currentAcademicPeriodId(),
            'class_group_id' => $classGroup->id,
            'status_id' => 1,
        ]);

        return response()->json(['message' => 'Student enrolled successfully']);
    }

    /* Remove a student's enrollment from a group  */
    public function destroy($classGroupId, $studentId)
    {
        try {
            // Buscar la inscripción
            $enrollment = SubjectEnrollment::where('class_group_id', $classGroupId)
                ->where('student_id', $studentId)
                ->firstOrFail();

            $enrollment->delete();

            return response()->json([
                'message' => 'Enrollment removed successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Enrollment not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error removing enrollment', ['exception' => $e]);
            return response()->json([
                'error' => 'Could not remove enrollment.',
            ], 500);
        }
    }
}

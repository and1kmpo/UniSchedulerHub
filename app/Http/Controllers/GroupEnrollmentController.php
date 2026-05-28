<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Models\Student;
use App\Models\SubjectEnrollmentStatus;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\EnrollmentService;
use App\Services\EnrollmentStatusService;

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

    public function store(
        Request $request,
        ClassGroup $classGroup,
        EnrollmentService $service
    ) {

        $this->authorize('enroll', SubjectEnrollment::class);

        $request->validate([
            'student_id' => ['required', 'exists:students,id']
        ]);

        $student = Student::findOrFail($request->student_id);

        try {
            $service->enroll($student, $classGroup);

            return response()->json([
                'status' => 'success',
                'message' => 'Student enrolled successfully',
            ], 200);
        } catch (\DomainException $e) {

            return response()->json([
                'status' => 'blocked',
                'code' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {

            Log::error('Enrollment error', [
                'student_id' => $student->id,
                'class_group_id' => $classGroup->id,
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /* Remove a student's enrollment from a group  */
    public function destroy(
        $classGroupId,
        $studentId
    ) {
        try {
            $enrollment = SubjectEnrollment::where('class_group_id', $classGroupId)
                ->where('student_id', $studentId)
                ->firstOrFail();

            $this->authorize('unenroll', $enrollment);

            app(EnrollmentService::class)->unenroll($enrollment);

            return response()->json([
                'status' => 'success',
                'message' => 'Enrollment removed successfully.',
            ], 200);
        } catch (\DomainException $e) {

            return response()->json([
                'status' => 'blocked',
                'code' => $e->getMessage(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status' => 'not_found',
                'code' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Could not remove enrollment. An unexpected error occurred',
            ], 500);
        }
    }

    public function changeStatus(
        Request $request,
        SubjectEnrollment $enrollment,
        EnrollmentStatusService $service
    ) {
        $request->validate([
            'to' => ['required', 'string'],
        ]);

        $this->authorize('changeStatus', [$enrollment, $request->to]);

        try {
            $service->transition($enrollment, $request->to);

            return response()->json([
                'status' => 'success',
                'message' => 'Enrollment status updated',
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status' => 'blocked',
                'code' => $e->getMessage(),
            ], 422);
        }
    }
}

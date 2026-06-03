<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use App\Models\Student;
use App\Models\SubjectEnrollmentStatus;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\EnrollmentService;
use App\Services\EnrollmentStatusService;
use App\Services\Enrollment\EnrollmentValidationService;

class GroupEnrollmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $period = AcademicPeriod::active()->with('status')->first();

        $groups = collect();

        if ($period) {
            $groups = ClassGroup::query()
                ->with(['subject', 'professor', 'academicPeriod'])
                ->where('academic_period_id', $period->id)
                ->when(
                    $user->hasRole('professor') && ! $user->hasRole('admin'),
                    fn($query) => $query->where('professor_id', $user->id)
                )
                ->withCount([
                    'subjectEnrollments' => fn($query) => $query->whereHas(
                        'status',
                        fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                    ),
                ])
                ->latest()
                ->get()
                ->map(fn($group) => [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'subject' => $group->subject?->name,
                    'professor' => $group->professor?->name,
                    'period' => $group->academicPeriod?->name,
                    'capacity' => $group->capacity,
                    'status' => $group->status,
                    'enrolled' => $group->subject_enrollments_count,
                ]);
        }

        return Inertia::render('Admin/GroupEnrollments/Index', [
            'classGroups' => $groups,
            'canManageEnrollments' => $user->hasAnyRole(['admin', 'academic_coordinator']),
            'period' => $period ? [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
            ] : null,
            'summary' => [
                'groups' => $groups->count(),
                'students' => $groups->sum('enrolled'),
                'capacity' => $groups->sum('capacity'),
            ],
            'systemState' => $period ? 'ready' : 'no_period',
        ]);
    }

    public function show(ClassGroup $classGroup)
    {
        $user = auth()->user();

        if ($user->hasRole('professor') && ! $user->hasRole('admin') && $classGroup->professor_id !== $user->id) {
            abort(403);
        }

        $group = $classGroup
            ->load(['subject', 'schedules.classroom', 'professor', 'academicPeriod'])
            ->loadCount([
                'subjectEnrollments' => fn($query) => $query->whereHas(
                    'status',
                    fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                ),
            ]);

        $enrollments = $classGroup->subjectEnrollments()
            ->whereHas(
                'status',
                fn($query) => $query->whereIn('code', config('enrollment.active_status_codes'))
            )
            ->with(['student.user', 'status', 'grade.state'])
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'student_id' => $e->student_id,
                'student_name' => $e->student?->user?->name,
                'document' => $e->student?->document,
                'email' => $e->student?->user?->email,
                'status' => $e->status?->code,
                'statusColor' => $e->status?->color,
                'final_grade' => $e->grade?->final_grade,
                'grade_state' => $e->grade?->state?->label,
            ]);

        $allStudents = collect();

        if ($user->hasAnyRole(['admin', 'academic_coordinator'])) {
            $enrolledIds = $enrollments->pluck('student_id')->all();

            $allStudents = Student::with('user')
                ->whereNotIn('id', $enrolledIds)
                ->orderBy('document')
                ->get()
                ->map(fn($student) => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'document' => $student->document,
                ]);
        }

        return Inertia::render('Admin/GroupEnrollments/Show', [
            'classGroup' => [
                'id' => $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'subject' => $group->subject?->name,
                'professor' => $group->professor?->name,
                'period' => $group->academicPeriod?->name,
                'capacity' => $group->capacity,
                'status' => $group->status,
                'schedules' => $group->schedules->map(fn($s) => [
                    'day' => $s->day,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'classroom' => $s->classroom?->name,
                ]),
                'subject_enrollments_count' => $group->subject_enrollments_count,
                'can_manage_grades' => $user->can('manageGrades', $group),
                'can_edit_grades' => $user->can('editGrades', $group),
            ],
            'enrollments' => $enrollments,
            'allStudents' => $allStudents,
            'canManageEnrollments' => $user->hasAnyRole(['admin', 'academic_coordinator']),
        ]);
    }

    public function store(
        Request $request,
        ClassGroup $classGroup,
        EnrollmentService $service
    ) {

        $this->authorize('enroll', [SubjectEnrollment::class, $classGroup]);

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

    public function validateEnrollment(
        Request $request,
        ClassGroup $classGroup,
        EnrollmentValidationService $service
    ) {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::findOrFail($request->student_id);

        $result = $service->validate($student, $classGroup);

        return response()->json($result->toArray());
    }

    /* Remove a student's enrollment from a group  */
    public function destroy(
        $classGroupId,
        $studentId
    ) {
        try {
            $enrollment = SubjectEnrollment::where('class_group_id', $classGroupId)
                ->where('student_id', $studentId)
                ->whereHas(
                    'status',
                    fn($query) => $query->whereIn('code', config('enrollment.active_status_codes'))
                )
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

<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Services\DegreeAuditService;
use App\Services\EnrollmentService;
use App\Services\Enrollment\EnrollmentValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SubjectEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        if (! $student->curriculum) {
            return Inertia::render('Students/SubjectEnrollment', [
                'subjects' => [],
                'enrollmentDeadline' => null,
                'unenrollmentDeadline' => null,
                'currentSchedules' => [],
                'currentCredits' => 0,
                'minCredits' => config('enrollment.min_credits', 7),
                'maxCredits' => config('enrollment.max_credits', 21),
                'canEnroll' => false,
                'canUnenroll' => false,
                'currentPeriod' => null,
                'systemState' => 'no_curriculum',
            ]);
        }

        $period = AcademicPeriod::active()->with('status')->first();

        if (! $period) {
            return Inertia::render('Students/SubjectEnrollment', [
                'subjects' => [],
                'enrollmentDeadline' => null,
                'unenrollmentDeadline' => null,
                'currentSchedules' => [],
                'currentCredits' => 0,
                'minCredits' => config('enrollment.min_credits', 7),
                'maxCredits' => config('enrollment.max_credits', 21),
                'canEnroll' => false,
                'canUnenroll' => false,
                'currentPeriod' => null,
                'systemState' => 'no_period',
            ]);
        }

        $enrollments = SubjectEnrollment::with([
            'status',
            'subject:id,code,name',
            'classGroup:id,code,name,subject_id,professor_id,modality,shift',
            'classGroup.professor:id,name',
            'classGroup.schedules.classroom:id,name',
        ])
            ->where('student_id', $student->id)
            ->where('academic_period_id', $period->id)
            ->get();

        $activeStatusCodes = config('enrollment.active_status_codes');
        $currentSchedules = $enrollments
            ->filter(fn($enrollment) => in_array($enrollment->status?->code, $activeStatusCodes, true))
            ->flatMap(
                fn($e) => ($e->classGroup?->schedules ?? [])->map(fn($s) => [
                    'id' => $s->id,
                    'day' => strtolower($s->day),
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'subject' => [
                        'id' => $e->subject?->id,
                        'code' => $e->subject?->code,
                        'name' => $e->subject?->name,
                    ],
                    'group' => [
                        'id' => $e->classGroup?->id,
                        'code' => $e->classGroup?->code,
                        'name' => $e->classGroup?->name,
                        'modality' => $e->classGroup?->modality,
                        'shift' => $e->classGroup?->shift,
                    ],
                    'professor' => $e->classGroup?->professor?->name,
                    'classroom' => $s->classroom?->name,
                    'status' => $e->status?->code,
                ])
            )
            ->values();

        $audit = new DegreeAuditService($student);

        $subjects = $student->curriculum->subjects()
            ->with([
                'prerequisites',
                'classGroups' => fn($q) =>
                $q->where('academic_period_id', $period->id)
                    ->with(['schedules', 'professor'])
                    ->withCount('subjectEnrollments'),
            ])
            ->get()
            ->map(function ($subject) use ($audit, $enrollments) {

                $evaluation = $audit->evaluateSubject($subject);

                $enrollment = $enrollments->firstWhere('subject_id', $subject->id);
                $currentGroupId = optional($enrollment)->class_group_id;
                $availableGroups = $subject->classGroups
                    ->where('status', ClassGroup::STATUS_PUBLISHED)
                    ->filter(fn($group) => $group->schedules->where('status', '!=', 'cancelled')->isNotEmpty());

                return [
                    ...$evaluation,
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'credits' => $subject->credits,
                    'semester' => $subject->pivot?->semester_recommended,
                    'enrollmentId' => $enrollment?->id,
                    'status' => $enrollment?->status?->code,
                    'statusColor' => $enrollment?->status?->color,
                    'alreadyEnrolled' => (bool) $enrollment,
                    'currentGroupId' => $currentGroupId,
                    'availableGroupsCount' => $availableGroups->count(),
                ];
            });

        return Inertia::render('Students/SubjectEnrollment', [
            'subjects' => $subjects,
            'currentSchedules' => $currentSchedules,
            'currentCredits' => $audit->currentPeriodCredits($period),
            'minCredits' => config('enrollment.min_credits', 7),
            'maxCredits' => $audit->maxCreditsPerPeriod,
            'enrollmentDeadline' => $period->enrollment_deadline,
            'unenrollmentDeadline' => $period->unenrollment_deadline,
            'canEnroll' => $period->canEnroll()
                && in_array($student->academic_status, Student::ENROLLABLE_STATUSES, true)
                && filled($student->curriculum_id),
            'canUnenroll' => $period->canUnenroll(),
            'currentPeriod' => [
                'id' => $period->id,
                'name' => $period->name,
                'state' => $period->state()?->value,
            ],
            'systemState' => 'ready',
        ]);
    }

    /**
     * 🎯 Smart endpoint: enroll OR change group
     */
    public function enroll(
        Request $request,
        ClassGroup $classGroup,
        EnrollmentService $service
    ) {
        try {

            $student = $request->user()->student;

            $existing = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $classGroup->subject_id)
                ->where('academic_period_id', $classGroup->academic_period_id)
                ->whereHas(
                    'status',
                    fn($q) => $q->whereIn('code', config('enrollment.active_status_codes'))
                )
                ->first();

            if ($existing) {

                $enrollment = $service->changeGroup($student, $classGroup);

                return response()->json([
                    'message' => 'Group changed successfully.',
                    'type' => 'group_changed',
                    'data' => $enrollment
                ]);
            }

            $enrollment = $service->enroll($student, $classGroup);

            return response()->json([
                'message' => 'Enrollment successful.',
                'type' => 'enrolled',
                'data' => $enrollment
            ], 201);
        } catch (\DomainException $e) {

            return response()->json([
                'error' => __('domain.' . $e->getMessage()),
                'code' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {

            Log::error('Enrollment error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred.',
            ], 500);
        }
    }
    /**
     * ❌ Unenroll seguro
     */
    public function unenroll(
        Request $request,
        SubjectEnrollment $enrollment,
        EnrollmentService $service
    ) {
        try {
            $student = $request->user()->student;

            // 🔐 Seguridad CRÍTICA
            if ($enrollment->student_id !== $student->id) {
                abort(403, 'Unauthorized action.');
            }

            $service->unenroll($enrollment);

            return response()->json([
                'message' => 'Unenrollment successful.'
            ]);
        } catch (\DomainException $e) {

            return response()->json([
                'error' => __('domain.' . $e->getMessage()),
                'code' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unenrollment error', ['exception' => $e]);

            return response()->json([
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * 📦 Obtener grupos disponibles
     */
    public function groups(Subject $subject, EnrollmentValidationService $validator)
    {
        $student = auth()->user()->student;
        $period = AcademicPeriod::active()->firstOrFail();

        $enrollment = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $currentGroupId = optional($enrollment)->class_group_id;

        $groups = $subject->classGroups()
            ->where('academic_period_id', $period->id)
            ->where('status', ClassGroup::STATUS_PUBLISHED)
            ->whereHas('schedules', fn($query) => $query->where('status', '!=', 'cancelled'))
            ->withCount([
                'subjectEnrollments as active_enrollments_count' => fn($query) => $query->whereHas(
                    'status',
                    fn($status) => $status->whereIn('code', config('enrollment.active_status_codes'))
                ),
            ])
            ->with([
                'schedules' => fn($query) => $query->where('status', '!=', 'cancelled'),
                'professor',
            ])
            ->get()
            ->map(function ($group) use ($student, $enrollment, $currentGroupId, $validator) {
                $validation = $validator->validate($student, $group, $enrollment)->toArray();

                return [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'capacity' => $group->capacity,
                    'enrolled' => $group->active_enrollments_count,
                    'availableSeats' => max($group->capacity - $group->active_enrollments_count, 0),
                    'modality' => $group->modality,
                    'shift' => $group->shift,
                    'professor' => optional($group->professor)->name,
                    'isCurrent' => $group->id === $currentGroupId,
                    'validation' => $validation,
                    'canSelect' => $validation['allowed'] && $group->id !== $currentGroupId,
                    'schedules' => $group->schedules->map(fn($s) => [
                        'day' => strtolower($s->day),
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ]),
                ];
            });

        return response()->json(['groups' => $groups]);
    }
}


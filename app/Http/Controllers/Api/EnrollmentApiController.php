<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Services\Enrollment\EnrollmentValidationService;
use App\Services\EnrollmentService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EnrollmentApiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'academic_period_id' => ['nullable', 'integer', 'exists:academic_periods,id'],
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $student = $this->studentFromRequest($request, $validated['student_id'] ?? null, false);

        $enrollments = SubjectEnrollment::query()
            ->with([
                'student.user',
                'subject',
                'academicPeriod',
                'status',
                'classGroup.professor',
            ])
            ->when($student, fn($query) => $query->where('student_id', $student->id))
            ->when(
                $request->user()->hasRole('student'),
                fn($query) => $query->where('student_id', $request->user()->student?->id)
            )
            ->when($validated['academic_period_id'] ?? null, fn($query, $periodId) => $query->where('academic_period_id', $periodId))
            ->when($validated['status'] ?? null, fn($query, $status) => $query->whereHas('status', fn($statusQuery) => $statusQuery->where('code', $status)))
            ->latest()
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return EnrollmentResource::collection($enrollments);
    }

    public function availableGroups(
        Request $request,
        Subject $subject,
        EnrollmentValidationService $validator
    ) {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'academic_period_id' => ['nullable', 'integer', 'exists:academic_periods,id'],
        ]);

        $student = $this->studentFromRequest($request, $validated['student_id'] ?? null);
        $period = isset($validated['academic_period_id'])
            ? AcademicPeriod::findOrFail($validated['academic_period_id'])
            : AcademicPeriod::active()->firstOrFail();

        $enrollment = SubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $currentGroupId = $enrollment?->class_group_id;

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
                'professor',
                'schedules' => fn($query) => $query->where('status', '!=', 'cancelled'),
            ])
            ->get()
            ->map(function (ClassGroup $group) use ($student, $enrollment, $currentGroupId, $validator) {
                $validation = $validator->validate($student, $group, $enrollment)->toArray();

                return [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'capacity' => $group->capacity,
                    'enrolled' => $group->active_enrollments_count,
                    'available_seats' => max($group->capacity - $group->active_enrollments_count, 0),
                    'modality' => $group->modality,
                    'shift' => $group->shift,
                    'professor' => $group->professor ? [
                        'id' => $group->professor->id,
                        'name' => $group->professor->name,
                        'email' => $group->professor->email,
                    ] : null,
                    'is_current' => $group->id === $currentGroupId,
                    'can_select' => $validation['allowed'] && $group->id !== $currentGroupId,
                    'validation' => $validation,
                    'schedules' => $group->schedules->map(fn($schedule) => [
                        'id' => $schedule->id,
                        'day' => strtolower($schedule->day),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'data' => $groups,
            'meta' => [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'current_group_id' => $currentGroupId,
            ],
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup, EnrollmentService $service)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $student = $this->studentFromRequest($request, $validated['student_id'] ?? null);
        $this->authorize('enroll', [SubjectEnrollment::class, $classGroup]);

        try {
            $existing = SubjectEnrollment::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $classGroup->subject_id)
                ->where('academic_period_id', $classGroup->academic_period_id)
                ->whereHas('status', fn($query) => $query->whereIn('code', config('enrollment.active_status_codes')))
                ->first();

            $enrollment = $existing
                ? $service->changeGroup($student, $classGroup)
                : $service->enroll($student, $classGroup);

            return (new EnrollmentResource($enrollment->load([
                'student.user',
                'subject',
                'academicPeriod',
                'status',
                'classGroup.professor',
            ])))
                ->additional([
                    'message' => $existing ? 'Group changed successfully.' : 'Enrollment successful.',
                    'type' => $existing ? 'group_changed' : 'enrolled',
                ])
                ->response()
                ->setStatusCode($existing ? 200 : 201);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $this->domainMessage($exception),
                'code' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('API enrollment error', [
                'message' => $exception->getMessage(),
                'class_group_id' => $classGroup->id,
                'student_id' => $student->id,
            ]);

            return response()->json([
                'message' => 'Unable to complete enrollment. Please try again.',
            ], 500);
        }
    }

    public function changeGroup(Request $request, SubjectEnrollment $enrollment, EnrollmentService $service)
    {
        $validated = $request->validate([
            'class_group_id' => [
                'required',
                'integer',
                Rule::exists('class_groups', 'id'),
            ],
        ]);

        $this->authorize('unenroll', $enrollment);

        $group = ClassGroup::findOrFail($validated['class_group_id']);

        if ($group->subject_id !== $enrollment->subject_id) {
            return response()->json([
                'message' => 'The target group must belong to the same subject.',
            ], 422);
        }

        try {
            $updated = $service->changeGroup($enrollment->student, $group);

            return (new EnrollmentResource($updated->load([
                'student.user',
                'subject',
                'academicPeriod',
                'status',
                'classGroup.professor',
            ])))
                ->additional([
                    'message' => 'Group changed successfully.',
                    'type' => 'group_changed',
                ]);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $this->domainMessage($exception),
                'code' => $exception->getMessage(),
            ], 422);
        }
    }

    public function confirmPeriod(Request $request, EnrollmentService $service)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'academic_period_id' => ['nullable', 'integer', 'exists:academic_periods,id'],
        ]);

        $student = $this->studentFromRequest($request, $validated['student_id'] ?? null);
        $period = isset($validated['academic_period_id'])
            ? AcademicPeriod::findOrFail($validated['academic_period_id'])
            : AcademicPeriod::active()->firstOrFail();

        try {
            return response()->json([
                'message' => 'Enrollment load confirmed successfully.',
                'data' => $service->confirmPeriodEnrollment($student, $period),
            ]);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $this->domainMessage($exception),
                'code' => $exception->getMessage(),
            ], 422);
        }
    }

    public function destroy(SubjectEnrollment $enrollment, EnrollmentService $service)
    {
        $this->authorize('unenroll', $enrollment);

        try {
            $service->unenroll($enrollment);

            return (new EnrollmentResource($enrollment->fresh([
                'student.user',
                'subject',
                'academicPeriod',
                'status',
                'classGroup.professor',
            ])))
                ->additional([
                    'message' => 'Enrollment cancelled or withdrawn successfully.',
                ]);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $this->domainMessage($exception),
                'code' => $exception->getMessage(),
            ], 422);
        }
    }

    private function studentFromRequest(Request $request, ?int $studentId, bool $required = true): ?Student
    {
        if ($request->user()->hasRole('student')) {
            $student = $request->user()->student;

            abort_unless($student, 403, 'The authenticated user is not linked to a student profile.');

            if ($studentId && $studentId !== $student->id) {
                abort(403, 'Students can only manage their own enrollments.');
            }

            return $student;
        }

        if ($studentId) {
            return Student::findOrFail($studentId);
        }

        abort_if($required, 422, 'student_id is required for administrative enrollment operations.');

        return null;
    }

    private function domainMessage(DomainException $exception): string
    {
        return match ($exception->getMessage()) {
            'BLOCK_ALREADY_ENROLLED' => 'The student is already enrolled in this subject for the selected academic period.',
            'BLOCK_ALREADY_IN_GROUP' => 'The student is already assigned to this class group.',
            'BLOCK_CAPACITY' => 'This class group has reached maximum capacity.',
            'BLOCK_GROUP_NOT_PUBLISHED' => 'This class group is not published and cannot receive enrollments.',
            'BLOCK_GROUP_WITHOUT_SCHEDULE' => 'This class group does not have a valid schedule.',
            'BLOCK_MAX_CREDITS' => 'The enrollment would exceed the maximum credit load.',
            'BLOCK_MIN_CREDITS' => 'The student must enroll at least the minimum required credits before confirming enrollment.',
            'BLOCK_NO_CURRICULUM' => 'The student does not have an assigned curriculum.',
            'BLOCK_OUT_OF_CURRICULUM' => 'The subject is not part of the student curriculum.',
            'BLOCK_SCHEDULE_CONFLICT' => 'The class group schedule conflicts with another active enrollment.',
            'BLOCK_STATUS_GRADUATED' => 'Graduated students cannot enroll.',
            'BLOCK_STATUS_SUSPENDED' => 'Suspended students cannot enroll.',
            'BLOCK_STATUS_WITHDRAWN' => 'Withdrawn students cannot enroll.',
            'BLOCK_UNENROLL_INVALID_STATUS' => 'This enrollment cannot be cancelled or withdrawn in its current status.',
            default => 'The enrollment operation could not be completed.',
        };
    }
}

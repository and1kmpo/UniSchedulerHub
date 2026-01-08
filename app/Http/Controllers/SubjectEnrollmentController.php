<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Services\DegreeAuditService;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class SubjectEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $student = $request->user()->student;

        // ⛔ No hay curriculum todavía
        if (!$student->curriculum) {
            return Inertia::render('Students/SubjectEnrollment', [
                'subjects' => [],
                'enrollmentDeadline' => null,
                'unenrollmentDeadline' => null,
                'currentSchedules' => [],
                'systemState' => 'no_curriculum',
            ]);
        }


        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Current enrollments for active period
        $enrollments = SubjectEnrollment::with(['status', 'classGroup.schedules'])
            ->where('student_id', $student->id)
            ->where('academic_period_id', optional($activePeriod)->id)
            ->get();

        // Current schedules (for conflict visualization)
        $currentSchedules = $enrollments
            ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? [])
            ->map(fn($schedule) => [
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ])
            ->values();

        // Degree Audit engine
        $audit = new DegreeAuditService($student);

        $currentCredits = $activePeriod
            ? $audit->currentPeriodCredits($activePeriod)
            : 0;


        // Curriculum subjects
        $subjects = $student->curriculum->subjects()
            ->with([
                'prerequisites',
                'classGroups' => fn($q) =>
                $q->where('academic_period_id', optional($activePeriod)->id)
                    ->with(['schedules', 'professor'])
                    ->withCount('subjectEnrollments'),
            ])
            ->get()
            ->map(function ($subject) use ($audit, $enrollments) {

                $evaluation = $audit->evaluateSubject($subject);

                $enrollment = $enrollments->firstWhere('subject_id', $subject->id);
                $currentGroupId = optional($enrollment)->class_group_id;

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'credits' => $subject->pivot->credits,
                    'semester' => $subject->pivot->semester_recommended,

                    // Degree audit (from service)
                    ...$evaluation,

                    // Current enrollment info
                    'status' => $enrollment?->status?->code,
                    'statusColor' => $enrollment?->status?->color,
                    'currentGroupId' => $currentGroupId,
                    'schedules' => $enrollment?->classGroup?->schedules->map(fn($s) => [
                        'day' => $s->day,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ])->values(),

                    // Available groups
                    'groups' => $subject->classGroups->map(function ($group) use ($currentGroupId) {
                        return [
                            'id' => $group->id,
                            'code' => $group->code,
                            'name' => $group->name,
                            'capacity' => $group->capacity,
                            'enrolled' => $group->subject_enrollments_count,
                            'professor' => optional($group->professor)->name,
                            'isCurrent' => $group->id === $currentGroupId,
                            'schedules' => $group->schedules->map(fn($s) => [
                                'day' => $s->day,
                                'start_time' => $s->start_time,
                                'end_time' => $s->end_time,
                            ]),
                        ];
                    }),
                ];
            });

        $progress = $audit->progress($subjects->count());

        return Inertia::render('Students/SubjectEnrollment', [
            'subjects' => $subjects,
            'progress' => $progress,
            'currentCredits' => $currentCredits,
            'maxCredits' => $audit->maxCreditsPerPeriod,
            'enrollmentDeadline' => $activePeriod?->enrollment_deadline,
            'unenrollmentDeadline' => $activePeriod?->unenrollment_deadline,
            'currentSchedules' => $currentSchedules,
        ]);
    }

    public function enroll(Request $request, Subject $subject)
    {
        try {
            $student = $request->user()->student;

            /**
             * 1️⃣ Obtener período académico activo
             */
            $period = AcademicPeriod::where('is_active', true)->first();

            if (!$period) {
                return response()->json([
                    'error' => 'There is no active academic period.'
                ], 500);
            }

            /**
             * 2️⃣ Validar que la asignatura pertenece al currículo del estudiante
             */
            $curriculumSubject = $student->curriculum
                ->subjects()
                ->where('subjects.id', $subject->id)
                ->first();

            if (!$curriculumSubject) {
                return response()->json([
                    'error' => 'This subject does not belong to your curriculum.'
                ], 403);
            }


            /**
             * 🔐 3️⃣ Validación académica (Degree Audit CENTRAL)
             */
            $audit = new DegreeAuditService($student);
            $authorization = $audit->authorizeEnrollment($curriculumSubject, $period);

            if (!$authorization['allowed']) {
                return response()->json([
                    'error' => 'Enrollment not allowed.',
                    'reason' => $authorization['reason'],
                    'status' => $authorization['status'] ?? null,
                    'details' => $authorization['details'] ?? null,
                    'blocked_by' => $authorization['blocked_by'] ?? [],
                ], 422);
            }


            /**
             * 4️⃣ Validar fecha límite de inscripción
             */
            if (
                $period->enrollment_deadline &&
                now()->greaterThan(
                    Carbon::parse($period->enrollment_deadline)->endOfDay()
                )
            ) {
                return response()->json([
                    'error' => 'The enrollment deadline has passed.'
                ], 403);
            }

            /**
             * 5️⃣ Validar grupo enviado
             */
            $groupId = $request->get('class_group_id');

            if (!$groupId) {
                return response()->json([
                    'error' => 'No group selected.'
                ], 422);
            }

            $classGroup = $subject->classGroups()
                ->where('id', $groupId)
                ->where('academic_period_id', $period->id)
                ->with('schedules')
                ->first();

            if (!$classGroup) {
                return response()->json([
                    'error' => 'Selected group is not valid for this subject or period.'
                ], 422);
            }

            /**
             * 6️⃣ Buscar inscripción existente (para cambio de grupo)
             */
            $existing = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_period_id', $period->id)
                ->first();

            /**
             * 7️⃣ Validar traslapes de horario
             */
            $existingSchedules = SubjectEnrollment::where('student_id', $student->id)
                ->where('academic_period_id', $period->id)
                ->with('classGroup.schedules')
                ->get()
                ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? []);

            foreach ($classGroup->schedules as $new) {
                foreach ($existingSchedules as $existingSchedule) {

                    // Ignorar el mismo grupo si es cambio
                    if ($existing && $existing->class_group_id === $classGroup->id) {
                        continue;
                    }

                    if (
                        $new->day === $existingSchedule->day &&
                        $new->start_time < $existingSchedule->end_time &&
                        $new->end_time > $existingSchedule->start_time
                    ) {
                        return response()->json([
                            'error' => 'Schedule conflict detected with another enrolled subject.'
                        ], 422);
                    }
                }
            }

            /**
             * 8️⃣ Si YA está inscrito → solo cambiar grupo (NO créditos)
             */
            if ($existing) {
                $existing->class_group_id = $classGroup->id;
                $existing->save();

                return response()->json([
                    'message' => 'Group changed successfully.',
                    'status' => [
                        'code' => $existing->status->code,
                        'color' => $existing->status->color,
                        'description' => $existing->status->description,
                    ],
                ], 200);
            }

            /**
             * 🔟 Validar capacidad del grupo
             */
            if ($classGroup->subjectEnrollments()->count() >= $classGroup->capacity) {
                return response()->json([
                    'error' => 'This group is already full.'
                ], 422);
            }

            /**
             * 1️⃣1️⃣ Obtener estado "enrolled"
             */
            $status = SubjectEnrollmentStatus::where('code', 'enrolled')->first();

            if (!$status) {
                return response()->json([
                    'error' => 'Enrollment status is misconfigured.'
                ], 500);
            }

            /**
             * 1️⃣2️⃣ Crear inscripción
             */
            SubjectEnrollment::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'class_group_id' => $classGroup->id,
                'status_id' => $status->id,
            ]);

            return response()->json([
                'message' => 'Enrollment successful.',
                'status' => [
                    'code' => $status->code,
                    'color' => $status->color,
                    'description' => $status->description,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Enrollment error', ['exception' => $e]);

            return response()->json([
                'error' => 'An unexpected error occurred.',
            ], 500);
        }
    }


    public function unenroll(Request $request, Subject $subject)
    {
        try {
            $student = $request->user()->student;

            /**
             * 1️⃣ Obtener período académico activo
             */
            $period = AcademicPeriod::where('is_active', true)->first();

            if (!$period) {
                return response()->json([
                    'error' => 'There is no active academic period.'
                ], 500);
            }

            /**
             * 2️⃣ Validar fecha límite de retiro
             */
            if (
                $period->unenrollment_deadline &&
                now()->greaterThan(
                    Carbon::parse($period->unenrollment_deadline)->endOfDay()
                )
            ) {
                return response()->json([
                    'error' => 'The unenrollment deadline has passed.'
                ], 403);
            }

            /**
             * 3️⃣ Buscar inscripción existente
             */
            $enrollment = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_period_id', $period->id)
                ->with('status', 'subject')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'error' => 'You are not enrolled in this subject.'
                ], 404);
            }

            /**
             * 🔐 4️⃣ Validación académica central (Degree Audit)
             */
            $audit = new DegreeAuditService($student);
            $authorization = $audit->authorizeUnenrollment($enrollment, $period);

            if (!$authorization['allowed']) {
                return response()->json([
                    'error' => 'Unenrollment not allowed.',
                    'reason' => $authorization['reason'],
                    'status' => $authorization['status'] ?? null,
                    'details' => $authorization['details'] ?? null,
                ], 422);
            }

            /**
             * 5️⃣ Eliminar inscripción
             */
            $enrollment->delete();

            return response()->json([
                'message' => 'Unenrollment successful.'
            ]);
        } catch (\Exception $e) {
            Log::error('Unenrollment error', ['exception' => $e]);

            return response()->json([
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }


    public function groups(Subject $subject)
    {
        $student = auth()->user()->student;
        $period = AcademicPeriod::where('is_active', true)->first();

        $enrollment = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $currentGroupId = optional($enrollment)->class_group_id;

        $groups = $subject->classGroups()
            ->where('academic_period_id', $period->id)
            ->whereHas('schedules')
            ->withCount('subjectEnrollments')
            ->with('schedules', 'professor')
            ->get()
            ->map(function ($group) use ($currentGroupId) {
                return [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'capacity' => $group->capacity,
                    'enrolled' => $group->subject_enrollments_count,
                    'schedules' => $group->schedules->map(fn($s) => [
                        'day' => strtolower($s->day),
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ]),
                    'professor' => optional($group->professor)->name,
                    'isCurrent' => $group->id === $currentGroupId,
                ];
            });

        return response()->json(['groups' => $groups]);
    }
}

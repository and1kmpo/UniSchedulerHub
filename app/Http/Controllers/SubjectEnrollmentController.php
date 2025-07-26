<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
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
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        $enrollments = SubjectEnrollment::with(['status', 'classGroup.schedules'])
            ->where('student_id', $student->id)
            ->where('academic_period_id', optional($activePeriod)->id)
            ->get();

        $currentSchedules = $enrollments->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? [])
            ->map(fn($schedule) => [
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ])
            ->values();

        $approvedSubjectIds = SubjectEnrollment::where('student_id', $student->id)
            ->whereHas('status', fn($q) => $q->where('code', 'approved'))
            ->pluck('subject_id');

        $subjects = $student->program->subjects()
            ->with([
                'prerequisites',
                'classGroups' => fn($q) =>
                $q->where('academic_period_id', optional($activePeriod)->id)
                    ->with(['schedules', 'professor'])
                    ->withCount('subjectEnrollments')
            ])
            ->get()
            ->map(function ($subject) use ($approvedSubjectIds, $enrollments) {
                $hasAllPrerequisites = $subject->prerequisites->every(
                    fn($pr) => $approvedSubjectIds->contains($pr->id)
                );

                $enrollment = $enrollments->firstWhere('subject_id', $subject->id);
                $currentGroupId = optional($enrollment)->class_group_id;

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'credits' => $subject->credits,
                    'semester' => $subject->pivot->semester,
                    'hasAllPrerequisites' => $hasAllPrerequisites,
                    'alreadyEnrolled' => $enrollment !== null,
                    'status' => $enrollment?->status?->code,
                    'statusColor' => $enrollment?->status?->color,
                    'currentGroupId' => $currentGroupId,
                    'schedules' => $enrollment?->classGroup?->schedules->map(fn($s) => [
                        'day' => $s->day,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ])->values(),
                    'groups' => $subject->classGroups->map(function ($group) use ($currentGroupId) {
                        return [
                            'id' => $group->id,
                            'code' => $group->code,
                            'enrolled' => $group->subject_enrollments_count,
                            'name' => $group->name,
                            'capacity' => $group->capacity,
                            'schedules' => $group->schedules->map(fn($s) => [
                                'day' => $s->day,
                                'start_time' => $s->start_time,
                                'end_time' => $s->end_time,
                            ]),
                            'professor' => optional($group->professor?->user)->name,
                            'isCurrent' => $group->id === $currentGroupId,
                        ];
                    }),
                ];
            });

        return Inertia::render('Students/SubjectEnrollment', [
            'subjects' => $subjects,
            'enrollmentDeadline' => $activePeriod->enrollment_deadline,
            'unenrollmentDeadline' => $activePeriod->unenrollment_deadline,
            'currentSchedules' => $currentSchedules,
        ]);
    }


    public function enroll(Request $request, Subject $subject)
    {
        try {
            $student = $request->user()->student;

            // Validar que la materia pertenece al programa
            if (!$student->program->subjects()->where('subjects.id', $subject->id)->exists()) {
                return response()->json(['error' => 'This subject is not part of your program.'], 403);
            }

            // Validar prerrequisitos
            $missing = $subject->prerequisites->filter(function ($prereq) use ($student) {
                return !SubjectEnrollment::where('student_id', $student->id)
                    ->where('subject_id', $prereq->id)
                    ->whereHas('status', fn($q) => $q->where('code', 'approved'))
                    ->exists();
            });

            if ($missing->isNotEmpty()) {
                return response()->json(['error' => 'You have not passed the required prerequisites.'], 422);
            }

            $period = AcademicPeriod::where('is_active', true)->first();

            if (!$period) {
                return response()->json(['error' => 'There is no active academic period.'], 500);
            }

            // Validar fecha límite
            if ($period->enrollment_deadline && now()->greaterThan(Carbon::parse($period->enrollment_deadline)->endOfDay())) {
                return response()->json(['error' => 'The enrollment deadline has passed.'], 403);
            }

            // Validar group enviado
            $groupId = $request->get('class_group_id');
            if (!$groupId) {
                return response()->json(['error' => 'No group selected.'], 422);
            }

            // Buscar el grupo y asegurarse que pertenece a esta materia y período
            $classGroup = $subject->classGroups()
                ->where('id', $groupId)
                ->where('academic_period_id', $period->id)
                ->with('schedules')
                ->first();

            if (!$classGroup) {
                return response()->json(['error' => 'Selected group is not valid for this subject or period.'], 422);
            }

            // Validar capacidad
            if ($classGroup->subjectEnrollments()->count() >= $classGroup->capacity) {
                return response()->json(['error' => 'This group is already full.'], 422);
            }

            // Buscar si ya existe inscripción previa para esta asignatura
            $existing = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_period_id', $period->id)
                ->first();

            // Si ya la aprobó o revalidó, no puede inscribirse ni cambiar grupo
            if ($existing && in_array($existing->status->code, ['approved', 'revalidation'])) {
                return response()->json(['error' => 'You have already completed this subject.'], 422);
            }

            // Validar traslapes de horario
            $existingSchedules = SubjectEnrollment::where('student_id', $student->id)
                ->where('academic_period_id', $period->id)
                ->with('classGroup.schedules')
                ->get()
                ->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? []);

            foreach ($classGroup->schedules as $new) {
                foreach ($existingSchedules as $existingSchedule) {
                    // Si el horario a comparar es del mismo grupo actual, lo ignoramos
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

            $status = SubjectEnrollmentStatus::where('code', 'enrolled')->first();
            if (!$status) {
                return response()->json(['error' => 'Enrollment status is misconfigured.'], 500);
            }

            // Si ya está inscrito, actualizar el grupo
            if ($existing) {
                $existing->class_group_id = $classGroup->id;
                $existing->save();

                return response()->json([
                    'message' => 'Group changed successfully.',
                    'status' => [
                        'code' => $existing->status->code,
                        'color' => $existing->status->color,
                        'description' => $existing->status->description,
                    ]
                ]);
            }

            // Si no existe, crear nueva inscripción
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
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Enrollment error', ['exception' => $e]);
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }



    public function unenroll(Request $request, Subject $subject)
    {
        $student = $request->user()->student;
        $period = AcademicPeriod::where('is_active', true)->first();

        if (!$period) {
            return response()->json(['error' => 'There is no active academic period.'], 500);
        }

        if (
            $period->unenrollment_deadline &&
            now()->greaterThan(Carbon::parse($period->unenrollment_deadline)->endOfDay())
        ) {
            return response()->json(['error' => 'The unenrollment deadline has passed.'], 403);
        }

        $enrollment = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'You are not enrolled in this subject.'], 404);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Unenrollment successful.']);
    }

    public function groups(Subject $subject)
    {
        $groups = $subject->classGroups()
            ->whereHas('schedules')
            ->withCount('subjectEnrollments')
            ->with('schedules', 'professor.user')
            ->get()
            ->map(function ($group) {
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
                    'professor' => optional($group->professor?->user)->name,
                ];
            });

        return response()->json(['groups' => $groups]);
    }
}

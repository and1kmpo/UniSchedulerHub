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

        // Traer todos los enrollments del estudiante en el período activo con relaciones
        $enrollments = SubjectEnrollment::with(['status', 'classGroup.schedules'])
            ->where('student_id', $student->id)
            ->where('academic_period_id', optional($activePeriod)->id)
            ->get();

        // Horarios actuales para validación de traslapes en el frontend
        $currentSchedules = $enrollments->flatMap(fn($enrollment) => $enrollment->classGroup?->schedules ?? [])
            ->map(fn($schedule) => [
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ])
            ->values();

        // IDs de asignaturas aprobadas (para validar prerrequisitos)
        $approvedSubjectIds = SubjectEnrollment::where('student_id', $student->id)
            ->whereHas('status', fn($q) => $q->where('code', 'approved'))
            ->pluck('subject_id');

        // Obtener asignaturas del programa con prerrequisitos y grupos activos
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

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'credits' => $subject->credits,
                    'semester' => $subject->pivot->semester,
                    'hasAllPrerequisites' => $hasAllPrerequisites,
                    'alreadyEnrolled' => $enrollment !== null,
                    'status' => $enrollment?->status?->label,
                    'statusColor' => $enrollment?->status?->color,
                    'schedules' => $enrollment?->classGroup?->schedules->map(fn($s) => [
                        'day' => $s->day,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ])->values(),
                    'groups' => $subject->classGroups->map(function ($group) {
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
                        ];
                    }),
                ];
            });

        return Inertia::render('Students/SubjectEnrollment', [
            'subjects' => $subjects,
            'enrollmentDeadline' => optional($activePeriod)->enrollment_deadline,
            'unenrollmentDeadline' => optional($activePeriod)->unenrollment_deadline,
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

            // Evitar duplicados
            if (SubjectEnrollment::where('student_id', $student->id)->where('subject_id', $subject->id)->exists()) {
                return response()->json(['error' => 'You have already enrolled in or completed this subject.'], 422);
            }

            $period = AcademicPeriod::where('is_active', true)->first();

            if (!$period) {
                return response()->json(['error' => 'There is no active academic period.'], 500);
            }

            // Validar fecha límite
            if ($period->enrollment_deadline && now()->greaterThan(Carbon::parse($period->enrollment_deadline)->endOfDay())) {
                return response()->json(['error' => 'The enrollment deadline has passed.'], 403);
            }

            $groupId = $request->input('class_group_id');

            $classGroup = $subject->classGroups()
                ->where('id', $groupId)
                ->where('academic_period_id', $period->id)
                ->with('schedules')
                ->first();

            if (!$classGroup) {
                return response()->json(['error' => 'Selected group is not valid for this subject or period.'], 422);
            }

            if ($classGroup->subjectEnrollments()->count() >= $classGroup->capacity) {
                return response()->json(['error' => 'This group is already full.'], 422);
            }



            if (!$classGroup) {
                return response()->json(['error' => 'No group available for this subject in the active period'], 404);
            }

            $newSchedules = $classGroup->schedules;

            // Obtener horarios ya inscritos del estudiante
            $existingSchedules = SubjectEnrollment::where('student_id', $student->id)
                ->where('academic_period_id', $period->id)
                ->with('classGroup.schedules')
                ->get()
                ->flatMap(fn($enrollment) => $enrollment->classGroup->schedules);

            // Validar traslapes
            foreach ($newSchedules as $new) {
                foreach ($existingSchedules as $existing) {
                    if (
                        $new->day === $existing->day &&
                        $new->start_time < $existing->end_time &&
                        $new->end_time > $existing->start_time
                    ) {
                        return response()->json([
                            'error' => 'Schedule conflict detected with another enrolled subject.'
                        ], 422);
                    }
                }
            }

            $statusId = SubjectEnrollmentStatus::where('code', 'enrolled')->value('id');

            SubjectEnrollment::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'class_group_id' => $classGroup->id,
                'status_id' => $statusId,
            ]);

            return response()->json([
                'message' => 'Enrollment successful.',
                'status' => [
                    'label' => $statusId->label,
                    'color' => $statusId->color
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
}

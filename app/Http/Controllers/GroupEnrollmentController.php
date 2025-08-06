<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\SubjectEnrollment;
use Inertia\Inertia;

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
        $group = $classGroup->load(['subject', 'schedules', 'professor']);

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
            ],
            'enrollments' => $enrollments
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id']
        ]);

        $studentId = $request->input('student_id');

        // Verificar si ya está inscrito
        $alreadyEnrolled = SubjectEnrollment::where('class_group_id', $classGroup->id)
            ->where('student_id', $studentId)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Student is already enrolled'], 422);
        }

        // Verificar capacidad
        $count = SubjectEnrollment::where('class_group_id', $classGroup->id)->count();
        if ($count >= $classGroup->capacity) {
            return response()->json(['message' => 'Group is at full capacity'], 422);
        }

        // Crear inscripción
        SubjectEnrollment::create([
            'student_id' => $studentId,
            'subject_id' => $classGroup->subject_id,
            'academic_period_id' => currentAcademicPeriodId(),
            'class_group_id' => $classGroup->id,
            'status_id' => 1,
        ]);

        return response()->json(['message' => 'Student enrolled successfully']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use Inertia\Inertia;

class GroupEnrollmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/GroupEnrollments/Index', [
            'classGroups' => ClassGroup::with('subject', 'professor')->withCount('subjectEnrollments')->latest()->paginate(15),
        ]);
    }

    public function show(ClassGroup $classGroup)
    {
        $group = $classGroup->load(['subject', 'schedules', 'professor.user']);

        $enrollments = $classGroup->subjectEnrollments()
            ->with(['student.user', 'status'])
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'student_name' => $e->student->user->name,
                'code' => $e->student->code,
                'status' => $e->status->code,
                'statusColor' => $e->status->color,
            ]);

        return Inertia::render('Admin/GroupEnrollments/Index', [
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
}

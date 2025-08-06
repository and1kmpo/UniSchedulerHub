<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    public function index()
    {
        return Inertia::render('ClassGroups/Index', [
            'classGroups' => ClassGroup::with('subject', 'professor')
                ->withCount('subjectEnrollments')
                ->latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('ClassGroups/Create', [
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
            'currentPeriodId' => AcademicPeriod::where('is_active', true)->first()?->id
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Request all data', $request->all());

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'professor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'modality' => 'required|string',
            'shift' => 'required|string',
            'academic_period_id' => 'required|exists:academic_periods,id',
            // Validación del schedule
            'schedules'          => 'required|array|min:1',
            'schedules.*.day'        => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time'   => 'required|date_format:H:i|after:schedules.*.start_time',
            // 'code' => 'required|unique:class_groups,code',
            // 'group_code' => 'required|string|unique:class_groups,group_code',
            // 'name' => 'required|string|max:255',
            // 'semester' => 'required|string',
        ]);

        Log::info('Creating ClassGroup with data:', $data);

        DB::transaction(function () use ($data) {
            $group = ClassGroup::create($data);
            foreach ($data['schedules'] as $sch) {
                $group->schedules()->create($sch);
            }
        });


        return redirect()->route('class-groups.index')->with('success', 'Class group created');
    }


    public function show($id)
    {
        $group = ClassGroup::with([
            'subject',
            'professor',                 // cargar al profesor y su user
            'subjectEnrollments.student' // cargar a cada student.user en los enrollments
        ])
            ->withCount('subjectEnrollments')
            ->findOrFail($id);

        // Lista de IDs de estudiante ya inscritos
        $enrolledIds = $group->subjectEnrollments
            ->pluck('student_id')
            ->all();

        // Todos los usuarios con rol 'student' (para el dropdown de inscripción)
        $allStudents = User::role('student')
            ->with('student') // trae su Student
            ->get()
            ->map(fn($u) => [
                'id'   => $u->student->id,
                'document' => $u->student->document,
                'name' => $u->name,               // $u ya es User
            ]);

        return Inertia::render('ClassGroups/Show', [
            'classGroup' => [
                'id'                        => $group->id,
                'code'                      => $group->code,
                'subject'                   => [
                    'id'   => $group->subject->id,
                    'name' => $group->subject->name,
                ],
                'professor'                 => [
                    'id'   => $group->professor->id,
                    'name' => $group->professor->name,
                ],
                'modality'                  => $group->modality,
                'shift'                     => $group->shift,
                'capacity'                  => $group->capacity,
                'subject_enrollments_count' => $group->subject_enrollments_count,
                'students'                  => $group->subjectEnrollments->map(fn($e) => [
                    'id'   => $e->student->id,
                    'code' => $e->student->document,
                    'name' => $e->student->user->name,
                ]),
            ],
            'allStudents' => $allStudents,
            'enrolledIds' => $enrolledIds,
        ]);
    }

    public function edit($id)
    {
        $classGroup = ClassGroup::findOrFail($id);


        return Inertia::render('ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'subjects' => Subject::all(['id', 'name', 'code']),
            'professors' => User::role('professor')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $classGroup = ClassGroup::findOrFail($id);

        $data = $request->validate([
            'subject_id'         => 'required|exists:subjects,id',
            'professor_id'       => 'required|exists:users,id',
            'capacity'           => 'required|integer|min:1',
            'modality'           => 'required|string',
            'shift'              => 'required|string',
            /*  'academic_period_id' => 'required|exists:academic_periods,id', */

            // Validación del schedule
            'schedules'             => 'required|array|min:1',
            'schedules.*.day'        => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time'   => 'required|date_format:H:i|after:schedules.*.start_time',
        ]);

        DB::transaction(function () use ($classGroup, $data) {
            // 1) Actualiza datos del grupo
            $classGroup->update([
                'subject_id'         => $data['subject_id'],
                'professor_id'       => $data['professor_id'],
                'capacity'           => $data['capacity'],
                'modality'           => $data['modality'],
                'shift'              => $data['shift'],
                /* 'academic_period_id' => $data['academic_period_id'], */
            ]);

            // 2) Elimina horarios antiguos
            $classGroup->schedules()->delete();

            // 3) Crea los nuevos horarios
            foreach ($data['schedules'] as $sch) {
                $classGroup->schedules()->create([
                    'day'        => $sch['day'],
                    'start_time' => $sch['start_time'],
                    'end_time'   => $sch['end_time'],
                ]);
            }
        });

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Class group updated with schedule');
    }


    public function destroy($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $classGroup->delete();

        return redirect()->route('class-groups.index')->with('success', 'Class group deleted');
    }
}

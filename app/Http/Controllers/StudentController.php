<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Student;
use App\Http\Requests\StudentRequest;
use App\Models\Grade;
use App\Models\User;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = User::role('Student')->with(['student.program', 'student.subjects'])->paginate(5);

        if (request()->wantsJson()) {
            return response()->json($students);
        }

        return inertia('Students/Index', ['students' => $students]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $programs = Program::all()->pluck('name', 'id');
        $subjects = Subject::all()->pluck('name', 'id');
        return inertia('Students/Create', [
            'programs' => $programs,
            'subjects' => $subjects
        ]);
    }

    /**
     *
     * Store a newly created resource in storage.
     * @param App\Http\Requests\StudentRequest
     * @return \illuminate\Http\Response
     */
    public function store(StudentRequest $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'document' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'semester' => 'nullable|integer',
            'program_id' => 'required|exists:programs,id',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $user->assignRole('Student');

        $user->student()->create([
            'document' => $validatedData['document'],
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'semester' => $validatedData['semester'],
            'program_id' => $validatedData['program_id'],
        ]);

        return redirect()->route('students.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        // Obtén la lista de programas
        $student->load('student.program');
        $programs = Program::all()->pluck('name', 'id');

        return inertia('Students/Edit', [
            'student' => $student,
            'programs' => $programs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, $user_id)
    {
        // Obtén el usuario y carga la relación con student
        $user = User::with('student')->findOrFail($user_id);

        //Validar datos recibidos
        $validatedData = $request->validated();
        // Actualiza el estudiante relacionado
        $user->student->update($validatedData);

        // Actualizar también el email en la tabla users, si aplica
        if (isset($validatedData['email'])) {
            $user->update(['email' => $validatedData['email']]);
        }

        return redirect()->route('students.index');
    }

    /**
     * @param Program $program
     * Remove the specified resource from storage.
     */
    public function destroy(User  $student)
    {
        $student->student()->delete();
        return redirect()->route('students.index');
    }

    public function assignSubjectForm()
    {
        $students = Student::all();
        return inertia('Students/AssignSubject', ['students' => $students]);
    }

    public function getAssignedSubjects($studentId)
    {
        $student = Student::with('subjects')->findOrFail($studentId);

        $assignedSubjects = $student->subjects;

        return response()->json($assignedSubjects);
    }


    public function assignSubjects(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'subject_ids' => 'required|array',
            ]);

            // Extraer datos del request
            $studentId = $request->input('student_id');
            $subjectIds = $request->input('subject_ids');

            // Buscar al estudiante
            $student = Student::findOrFail($studentId);

            // Calcular créditos totales
            $totalCredits = Subject::whereIn('id', $subjectIds)->sum('credits');

            // Validar que cumpla con el mínimo
            if ($totalCredits < 7) {
                return response()->json(['error' => 'The selected subjects do not meet the minimum requirement of 7 credits.'], 422);
            }

            // Obtener y asignar materias
            $subjects = Subject::whereIn('id', $subjectIds)->get(['id']);
            foreach ($subjects as $subject) {
                $professorId = $subject->professors()->pluck('professors.id')->first();

                if ($professorId !== null) {
                    $student->subjects()->syncWithoutDetaching([$subject->id => ['professor_id' => $professorId]]);
                } else {
                    Log::warning('Subject without professor assigned:', ['subject_id' => $subject->id]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Successfully assigned subjects.'], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => 'Error when assigning subject: ' . $exception->getMessage()], 500);
        }
    }


    public function unassignSubject($studentId, $subjectId)
    {
        try {
            $student = Student::findOrFail($studentId);
            $totalCredits = $student->subjects->sum('credits');
            $subject = Subject::findOrFail($subjectId);
            $subjectCredits = $subject->credits;


            if (($totalCredits - $subjectCredits) < 7) {
                Log::warning('Cannot unassign due to minimum credit restriction.', [
                    'remaining_credits' => $totalCredits - $subjectCredits
                ]);
                return response()->json(['error' => 'Cannot unassign this subject. The student must have at least 7 credits assigned.'], 422);
            }

            $student->subjects()->detach($subjectId);

            return response()->json(['success' => true, 'message' => 'Subject successfully unassigned.']);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => 'Error when unassigning subject.'], 500);
        }
    }

    public function mySubjects()
    {
        $student = auth()->user()->student;

        $subjects = $student->subjects()->get()->map(
            function ($subject) {
                $professorId = $subject->pivot->professor_id;
                $professor = \App\Models\Professor::with('user')->find($professorId);

                $subject->professor_name = optional($professor?->user)->name ?? 'Unassigned';
                return $subject;
            }
        );

        return Inertia::render('Students/MySubjects', [
            'subjects' => $subjects
        ]);
    }

    public function viewGrades($subjectId)
    {
        $studentId = auth()->user()->student->id;

        $grade = Grade::with('state')->where('student_id', $studentId)->where('subject_id', $subjectId)->first();

        $subject = Subject::findOrFail($subjectId);

        return Inertia::render('Students/SubjectGrades', ['subject' => $subject, 'grade' => $grade]);
    }

    public function getGradeJson($subjectId)
    {
        $studentId = auth()->user()->student->id;

        $grade = Grade::with('state')
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->first();

        return response()->json(['grade' => $grade]);
    }

    public function gradesSummary()
    {
        $student = auth()->user()->student;

        // Todas las asignaturas asignadas al estudiante
        $subjects = $student->subjects()->with('grades.state')->get();

        // Mapeamos cada asignatura y buscamos si tiene calificación
        $gradesSummary = $subjects->map(function ($subject) use ($student) {
            $grade = Grade::with('state')
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->first();

            return [
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->name
                ],
                'partial_1'   => $grade->partial_1 ?? null,
                'partial_2'   => $grade->partial_2 ?? null,
                'partial_3'   => $grade->partial_3 ?? null,
                'activities'  => $grade->activities ?? null,
                'final_grade' => $grade->final_grade ?? null,
                'state'       => $grade?->state,
            ];
        });

        return response()->json(['grades' => $gradesSummary]);
    }
}

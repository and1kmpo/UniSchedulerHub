<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StudentRequest;
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
        $students = User::role('Student')->with(['student.program'])->paginate(5);

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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
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
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
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
        $student = Student::findOrFail($studentId);
        $assignedSubjects = $student ? $student->subjects : [];

        return response()->json($assignedSubjects);
    }

    public function assignSubjects(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'subject_ids' => 'required|array',
            ]);

            $studentId = $request->input('student_id');
            $subjectIds = $request->input('subject_ids');

            $student = Student::findOrFail($studentId);

            // Obtener el total de créditos de las asignaturas seleccionadas
            $totalCredits = Subject::whereIn('id', $subjectIds)->sum('credits');

            // Validar que el estudiante cumpla con el mínimo de 7 créditos
            if ($totalCredits < 7) {
                return response()->json(['error' => 'The selected subjects do not meet the minimum requirement of 7 credits.'], 422);
            }

            // Obtener las asignaturas con el professor_id
            $subjects = Subject::whereIn('id', $subjectIds)->get(['id']);

            // Iterar sobre las asignaturas y asignarlas al estudiante con el professor_id correspondiente
            foreach ($subjects as $subject) {
                // Obtener el professor_id de la asignatura a través de la relación directa
                $professorId = $subject->professors()->pluck('professors.id')->first();

                if ($professorId !== null) {
                    // Asignar la asignatura al estudiante y establecer el professor_id en la tabla pivote
                    $student->subjects()->syncWithoutDetaching([$subject->id => ['professor_id' => $professorId]]);
                } else {
                    // Manejar el caso en que no se encuentra el professor_id para la asignatura
                    Log::warning("No professor_id found for subject with ID: {$subject->id}");
                }
            }

            return response()->json(['success' => true, 'message' => 'Successfully assigned subjects.'], 200);
        } catch (\Exception $exception) {
            Log::error('Error when assigning subject: ' . $exception->getMessage());

            return response()->json(['error' => 'Error when assigning subject: ' . $exception->getMessage()], 500);
        }
    }


    public function unassignSubject($studentId, $subjectId)
    {
        try {
            Log::info('Unassigning subject...');

            $student = Student::findOrFail($studentId);
            $totalCredits = $student->subjects->sum('credits');
            $subject = Subject::findOrFail($subjectId);
            $subjectCredits = $subject->credits;

            if (($totalCredits - $subjectCredits) < 7) {
                return response()->json(['error' => 'Cannot unassign this subject. The student must have at least 7 credits assigned.'], 422);
            }

            $student->subjects()->detach($subjectId);

            Log::info('Subject unassigned successfully.');

            return response()->json(['success' => true, 'message' => 'Subject successfully unassigned.']);
        } catch (\Exception $exception) {
            Log::error('Error when unassigning subject: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Error when unassigning subject.'], 500);
        }
    }
}

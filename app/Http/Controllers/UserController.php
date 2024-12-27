<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['professor', 'student', 'roles']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $users = $query->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return inertia('Users/Index', [
            'users' => $users,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Inicia la transacción

        try {
            // Validación de datos
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:professor,student,admin',
                'document' => 'required|string|unique:students,document|unique:professors,document',
                'phone' => 'required|string|min:7|max:15',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:50',
                'semester' => 'nullable|required_if:role,student|integer|min:1|max:10',
                'program_id' => 'nullable|required_if:role,student|exists:programs,id',
            ]);

            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt('123'),
            ]);

            // Asignar rol al usuario
            $user->assignRole($validated['role']);

            // Crear el registro correspondiente dependiendo del rol
            if ($validated['role'] === 'professor') {
                $user->professor()->create([
                    'document' => $validated['document'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                ]);
            } elseif ($validated['role'] === 'student') {
                $user->student()->create([
                    'document' => $validated['document'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'semester' => $validated['semester'],
                    'program_id' => $validated['program_id'],
                ]);
            }

            // Si todo está bien, se hace commit de la transacción
            DB::commit();

            return response()->json(['message' => 'User created successfully'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devuelve todos los errores en formato JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
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
    public function edit(string $id)
    {
        $user = User::with(['professor', 'student', 'roles'])->findOrFail($id);

        return response()->json($user);
    }
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:professor,student,admin',
                'document' => [
                    'required',
                    'string',
                    Rule::unique('students', 'document')->ignore($id, 'user_id'),
                    Rule::unique('professors', 'document')->ignore($id, 'user_id'),
                ],
                'phone' => 'required|string|min:7|max:15',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:50',
                'semester' => 'nullable|required_if:role,student|integer|min:1|max:10',
                'program_id' => 'nullable|required_if:role,student|exists:programs,id',
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
            $user->syncRoles($validated['role']);

            if ($validated['role'] === 'professor') {
                $user->professor()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'document' => $validated['document'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'city' => $validated['city'],
                    ]
                );
                $user->student()->delete();
            } elseif ($validated['role'] === 'student') {
                $user->student()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'document' => $validated['document'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'city' => $validated['city'],
                        'semester' => $validated['semester'],
                        'program_id' => $validated['program_id'],
                    ]
                );
                $user->professor()->delete();
            }

            DB::commit();

            // Retornar el usuario actualizado con relaciones necesarias
            return response()->json(
                User::with(['roles:id,name', 'professor', 'student'])->find($user->id),
                200
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction(); // Inicia la transacción

        try {
            // Encontrar al usuario
            $user = User::findOrFail($id);

            User::destroy($id);

            // Si todo está bien, se hace commit de la transacción
            DB::commit();

            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devuelve todos los errores en formato JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function activate(User $user)
    {
        try {
            $user->activate();

            return response()->json([
                'message' => 'User activated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devuelve todos los errores en formato JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deactivate(User $user)
    {
        try {
            $user->deactivate();

            return response()->json([
                'message' => 'User deactivated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devuelve todos los errores en formato JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getUserAssignments(Request $request)
    {
        $user = $request->user();
        $role = $user->roles->first()->name;

        if ($role === 'student') {
            $assignments = DB::table('student_subject_professor')
                ->join('professors', 'professors.id', '=', 'student_subject_professor.professor_id')
                ->join('subjects', 'subjects.id', '=', 'student_subject_professor.subject_id')
                ->join('users', 'users.id', '=', 'professors.user_id')
                ->where('student_subject_professor.student_id', $user->student->id)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.credits',
                    'subjects.knowledge_area',
                    'subjects.elective',
                    'users.name as professor_name'
                )
                ->get();

            $totalCredits = $assignments->sum(fn($assignment) => $assignment->credits);

            return inertia('Assignments/Index', [
                'assignments' => $assignments,
                'totalCredits' => $totalCredits,
                'role' => $role,
            ]);
        } elseif ($role === 'professor') {
            $assignments = DB::table('professor_subject')
                ->join('subjects', 'professor_subject.subject_id', '=', 'subjects.id')
                ->where('professor_subject.professor_id', $user->professor->id)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.credits',
                )
                ->get()
                ->map(function ($assignment) use ($user) {
                    // Añadir estudiantes matriculados a cada asignatura
                    $students = DB::table('student_subject_professor')
                        ->join('students', 'students.id', '=', 'student_subject_professor.student_id')
                        ->join('users', 'users.id', '=', 'students.user_id')
                        ->where('student_subject_professor.professor_id', $user->professor->id)
                        ->where('student_subject_professor.subject_id', $assignment->subject_id)
                        ->select(
                            'students.id as student_id',
                            'users.name as student_name'
                        )
                        ->get();

                    $assignment->students = $students;
                    return $assignment;
                });

            return inertia('Assignments/Index', [
                'assignments' => $assignments,
                'role' => $role,
            ]);
        } else {
            return response()->json(['error' => 'Role not supported'], 403);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use App\Services\AcademicAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

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

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($roles) => $roles->where('name', $request->role));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortable = ['name', 'email', 'status', 'created_at'];

        if (! in_array($sort, $sortable, true)) {
            $sort = 'name';
        }

        $query->orderBy($sort, $direction);

        $users = $query->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'users' => $users
            ]);
        }

        return inertia('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status', 'sort', 'direction']),
            'roles' => Role::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($role) => [
                    'label' => str($role->name)->replace('_', ' ')->title()->toString(),
                    'value' => $role->name,
                ])
                ->values(),
            'identityRoleOptions' => collect($this->identityRoles())
                ->map(fn($role) => [
                    'label' => str($role)->replace('_', ' ')->title()->toString(),
                    'value' => $role,
                ])
                ->values(),
            'statusOptions' => [
                ['label' => 'Active', 'value' => User::STATUS_ACTIVE],
                ['label' => 'Inactive', 'value' => User::STATUS_INACTIVE],
            ],
            'metrics' => [
                'users' => User::count(),
                'active' => User::where('status', User::STATUS_ACTIVE)->count(),
                'inactive' => User::where('status', User::STATUS_INACTIVE)->count(),
                'roles' => Role::count(),
                'academicProfiles' => User::whereHas('student')
                    ->orWhereHas('professor')
                    ->count(),
            ],
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
                'role' => ['required', Rule::in($this->identityRoles())],
            ]);

            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt('123'),
                'status' => User::STATUS_ACTIVE,
            ]);

            // Asignar rol al usuario
            $user->assignRole($validated['role']);

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
            $user = User::with(['professor', 'student', 'roles'])->findOrFail($id);
            $academicRole = $this->academicProfileRole($user);

            if ($academicRole && $request->input('role') !== $academicRole) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Academic roles are managed from Students and Professors. Deactivate the account instead of changing its role.',
                ], 422);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => [
                    'required',
                    Rule::in($academicRole ? [$academicRole] : $this->identityRoles()),
                ],
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! $academicRole) {
                $user->syncRoles($validated['role']);
            }

            DB::commit();

            // Return the updated user with the relationships needed by the UI.
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
            $user = User::findOrFail($id);

            if ($this->hasAcademicHistory($user)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'This user has academic history. Deactivate the account instead of deleting it.',
                ], 422);
            }

            $user->delete();

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

    public function resetTemporaryPassword(User $user, AcademicAuditService $audit)
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot reset your own password from this action. Use your profile security settings instead.',
            ], 422);
        }

        $temporaryPassword = $this->generateTemporaryPassword();

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
        ])->save();

        $audit->record(
            'identity.password_reset',
            $user,
            [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'target_user_roles' => $user->roles()->pluck('name')->values()->all(),
                'temporary_password_issued' => true,
            ],
            "Temporary password issued for {$user->email}."
        );

        return response()->json([
            'message' => 'Temporary password generated successfully. Share it through an institutional channel and ask the user to change it after login.',
            'temporary_password' => $temporaryPassword,
        ]);
    }

    public function getUserAssignments(Request $request)
    {
        $user = $request->user();
        $role = $user->roles->first()->name;
        $activeStatusIds = SubjectEnrollmentStatus::whereIn('code', config('enrollment.active_status_codes'))
            ->pluck('id');

        if ($role === 'student') {
            $assignments = $user->student
                ?->enrollments()
                ->whereIn('status_id', $activeStatusIds)
                ->with(['subject:id,name,credits,knowledge_area,elective', 'classGroup.professor:id,name'])
                ->get()
                ->map(fn($enrollment) => [
                    'subject_id' => $enrollment->subject_id,
                    'subject_name' => $enrollment->subject?->name,
                    'credits' => $enrollment->subject?->credits,
                    'knowledge_area' => $enrollment->subject?->knowledge_area,
                    'elective' => $enrollment->subject?->elective,
                    'professor_name' => $enrollment->classGroup?->professor?->name ?? 'No professor assigned',
                    'group_code' => $enrollment->classGroup?->code,
                ]) ?? collect();

            $totalCredits = $assignments->sum(fn($assignment) => $assignment['credits']);

            return inertia('Assignments/Index', [
                'assignments' => $assignments,
                'totalCredits' => $totalCredits,
                'role' => $role,
            ]);
        }

        if ($role === 'professor') {
            $assignments = $user->professor
                ?->classGroups()
                ->with([
                    'subject:id,name,credits',
                    'subjectEnrollments' => fn($enrollments) => $enrollments
                        ->whereIn('status_id', $activeStatusIds)
                        ->with('student.user:id,name,email'),
                ])
                ->get()
                ->map(fn($group) => [
                    'subject_id' => $group->subject_id,
                    'subject_name' => $group->subject?->name,
                    'credits' => $group->subject?->credits,
                    'group_code' => $group->code,
                    'students' => $group->subjectEnrollments->map(fn($enrollment) => [
                        'student_id' => $enrollment->student_id,
                        'student_name' => $enrollment->student?->user?->name,
                        'student_email' => $enrollment->student?->user?->email,
                    ])->values(),
                ]) ?? collect();

            return inertia('Assignments/Index', [
                'assignments' => $assignments,
                'role' => $role,
            ]);
        }

        return response()->json(['error' => 'Role not supported'], 403);
    }

    private function academicProfileRole(User $user): ?string
    {
        $user->loadMissing(['student', 'professor']);

        if ($user->student) {
            return 'student';
        }

        if ($user->professor) {
            return 'professor';
        }

        return null;
    }

    private function hasAcademicHistory(User $user): bool
    {
        $user->loadMissing(['student', 'professor']);

        if ($user->student) {
            return $user->student->enrollments()->exists()
                || $user->student->enrollmentGrades()->exists();
        }

        if ($user->professor) {
            return $user->professor->classGroups()->exists()
                || $user->professor->grades()->exists();
        }

        return false;
    }

    private function identityRoles(): array
    {
        return ['admin', 'academic_coordinator'];
    }

    private function generateTemporaryPassword(): string
    {
        return implode('', [
            Str::random(4),
            random_int(10, 99),
            '!',
            Str::upper(Str::random(2)),
            Str::lower(Str::random(4)),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Filters\StudentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentApiController extends Controller
{
    public function index(Request $request, StudentFilter $filters)
    {
        $students = $filters
            ->apply(Student::query()->with(['user', 'program', 'curriculum'])->withCount('enrollments'))
            ->paginate(min((int) $request->input('per_page', 15), 100))
            ->withQueryString();

        return StudentResource::collection($students);
    }

    public function store(StudentRequest $request)
    {
        $validated = $request->validated();

        $student = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => User::STATUS_ACTIVE,
            ]);

            $user->assignRole('student');

            return $user->student()->create([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'semester' => $validated['semester'],
                'program_id' => $validated['program_id'],
                'curriculum_id' => $validated['curriculum_id'] ?? null,
                'academic_status' => $validated['academic_status'] ?? Student::STATUS_ACTIVE,
            ]);
        });

        return (new StudentResource($student->load(['user', 'program', 'curriculum'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Student $student)
    {
        return new StudentResource($student->load(['user', 'program', 'curriculum'])->loadCount('enrollments'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($student, $validated) {
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $student->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $student->update([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'semester' => $validated['semester'],
                'program_id' => $validated['program_id'],
                'curriculum_id' => $validated['curriculum_id'] ?? null,
                'academic_status' => $validated['academic_status'] ?? $student->academic_status,
            ]);
        });

        return new StudentResource($student->fresh(['user', 'program', 'curriculum']));
    }

    public function destroy(Student $student)
    {
        $blockers = $this->deletionBlockers($student);

        if (! empty($blockers)) {
            return response()->json([
                'message' => 'This student cannot be deleted because it has academic history.',
                'blockers' => $blockers,
            ], 409);
        }

        try {
            $student->user?->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'This student cannot be deleted because it is associated with other records.',
                ], 409);
            }

            throw $exception;
        }

        return response()->noContent();
    }

    private function deletionBlockers(Student $student): array
    {
        return collect([
            'enrollments' => $student->enrollments()->exists(),
            'grades' => $student->enrollmentGrades()->exists(),
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
    }
}

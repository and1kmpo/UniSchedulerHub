<?php

namespace App\Http\Controllers\Api;

use App\Filters\ProfessorFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessorRequest;
use App\Http\Resources\ProfessorResource;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfessorApiController extends Controller
{
    public function index(Request $request, ProfessorFilter $filters)
    {
        $professors = $filters
            ->apply(Professor::query()->with('user')->withCount(['subjects', 'classGroups']))
            ->paginate(min((int) $request->input('per_page', 15), 100))
            ->withQueryString();

        return ProfessorResource::collection($professors);
    }

    public function store(ProfessorRequest $request)
    {
        $validated = $request->validated();

        $professor = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => User::STATUS_ACTIVE,
            ]);

            $user->assignRole('professor');

            return $user->professor()->create([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
            ]);
        });

        return (new ProfessorResource($professor->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Professor $professor)
    {
        return new ProfessorResource($professor->load('user')->loadCount(['subjects', 'classGroups']));
    }

    public function update(ProfessorRequest $request, Professor $professor)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($professor, $validated) {
            $professor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $professor->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $professor->update([
                'document' => $validated['document'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
            ]);
        });

        return new ProfessorResource($professor->fresh('user'));
    }

    public function destroy(Professor $professor)
    {
        $blockers = $this->deletionBlockers($professor);

        if (! empty($blockers)) {
            return response()->json([
                'message' => 'This professor cannot be deleted because it has academic assignments.',
                'blockers' => $blockers,
            ], 409);
        }

        try {
            $professor->user?->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'This professor cannot be deleted because it is associated with other records.',
                ], 409);
            }

            throw $exception;
        }

        return response()->noContent();
    }

    private function deletionBlockers(Professor $professor): array
    {
        return collect([
            'teaching_capabilities' => $professor->subjects()->exists(),
            'class_groups' => $professor->classGroups()->exists(),
            'grades' => $professor->grades()->exists(),
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
    }
}

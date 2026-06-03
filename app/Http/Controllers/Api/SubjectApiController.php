<?php

namespace App\Http\Controllers\Api;

use App\Filters\SubjectFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SubjectApiController extends Controller
{
    public function index(Request $request, SubjectFilter $filters)
    {
        $subjects = $filters
            ->apply(Subject::query()->withCount(['professors', 'classGroups']))
            ->paginate(min((int) $request->input('per_page', 15), 100))
            ->withQueryString();

        return SubjectResource::collection($subjects);
    }

    public function store(SubjectRequest $request)
    {
        return (new SubjectResource(Subject::create($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subject $subject)
    {
        return new SubjectResource($subject->loadCount(['professors', 'classGroups']));
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());

        return new SubjectResource($subject->fresh());
    }

    public function destroy(Subject $subject)
    {
        $blockers = $this->deletionBlockers($subject);

        if (! empty($blockers)) {
            return response()->json([
                'message' => 'This subject cannot be deleted because it has academic usage.',
                'blockers' => $blockers,
            ], 409);
        }

        try {
            $subject->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'This subject cannot be deleted because it is associated with other records.',
                ], 409);
            }

            throw $exception;
        }

        return response()->noContent();
    }

    private function deletionBlockers(Subject $subject): array
    {
        return collect([
            'professors' => $subject->professors()->exists(),
            'enrollments' => $subject->enrollments()->exists(),
            'class_groups' => $subject->classGroups()->exists(),
            'curricula' => $subject->curricula()->exists(),
            'programs' => $subject->programs()->exists(),
            'grades' => $subject->grades()->exists(),
            'prerequisites' => $subject->prerequisites()->exists(),
            'dependent_subjects' => $subject->isPrerequisiteFor()->exists(),
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
    }
}

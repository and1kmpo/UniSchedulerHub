<?php

namespace App\Http\Controllers;

use App\Filters\BuildingFilter;
use App\Http\Requests\BuildingRequest;
use App\Models\Building;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildingController extends Controller
{
    public function index(
        Request $request,
        BuildingFilter $filters
    ) {
        $buildings = $filters
            ->apply(
                Building::query()
                    ->withCount('classrooms')
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Buildings/Index', [
            'buildings' => $buildings,

            'filters' => $request->only([
                'search',
                'sort',
                'direction',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Buildings/Create');
    }

    public function store(BuildingRequest $request)
    {
        $building = Building::create(
            $request->validated()
        );

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Building created successfully',
                'data' => $building,
            ], 201)
            : redirect()
            ->route('buildings.index')
            ->with('success', 'Building created successfully');
    }

    public function show(Building $building)
    {
        $building->loadCount('classrooms');

        $classrooms = $building
            ->classrooms()
            ->withCount('schedules')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Buildings/Show', [
            'building' => $building,
            'classrooms' => $classrooms,
        ]);
    }

    public function edit(Building $building)
    {
        return Inertia::render('Buildings/Edit', [
            'building' => $building,
        ]);
    }

    public function update(
        BuildingRequest $request,
        Building $building
    ) {
        $building->update(
            $request->validated()
        );

        return request()->wantsJson()
            ? response()->json([
                'message' => 'Building updated successfully',
            ])
            : redirect()
            ->route('buildings.index')
            ->with('success', 'Building updated successfully');
    }

    public function destroy(Building $building)
    {
        if ($building->classrooms()->exists()) {
            return back()->withErrors([
                'message' => 'This building cannot be deleted because it has classrooms assigned.',
            ]);
        }

        try {
            $building->delete();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'message' => 'This building cannot be deleted because it is associated with other records.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('buildings.index')
            ->with('success', 'Building deleted successfully');
    }

    public function restore($id)
    {
        $building = Building::withTrashed()->findOrFail($id);
        $building->restore();

        return redirect()
            ->route('buildings.index')
            ->with('success', 'Building restored successfully');
    }
}

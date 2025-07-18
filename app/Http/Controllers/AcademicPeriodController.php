<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/AcademicPeriods', [
            'periods' => AcademicPeriod::orderByDesc('start_date')->paginate(10)
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
        $data = $request->validate([
            'name'       => 'required|string|unique:academic_periods,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'required|boolean',
        ]);

        if ($data['is_active']) {
            AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        $period = AcademicPeriod::create($data);

        // 🔁 Si la petición es Inertia/AJAX, devolvemos JSON directamente
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return response()->json([
                'message' => 'Period created successfully',
                'period'  => $period,
            ]);
        }

        return redirect()->route('academic-periods.index')->with('success', 'Period created successfully');
    }
    /**
     * Display the specified resource.
     */
    public function show(AcademicPeriod $academicPeriod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicPeriod $academicPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicPeriod $academicPeriod)
    {
        $data = $request->validate([
            'name'       => 'required|string|unique:academic_periods,name,' . $academicPeriod->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'nullable|boolean',
        ]);

        if ($request->boolean('is_active')) {
            AcademicPeriod::query()->update(['is_active' => false]);
        }

        $academicPeriod->update($data);

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return response()->json([
                'message' => 'Period updated successfully',
                'period' => $academicPeriod->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Period updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicPeriod $academicPeriod)
    {
        if ($academicPeriod->is_active) {
            return response()->json([
                'error' => 'You cannot delete an active period. Please deactivate it first.'
            ], 422);
        }

        $academicPeriod->delete();

        return response()->json([
            'message' => 'Period deleted successfully'
        ]);
    }

    public function activate($id)
    {
        AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        AcademicPeriod::where('id', $id)->update(['is_active' => true]);

        // Si es una petición AJAX (fetch o XHR), respondemos con JSON plano
        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Period activated successfully',
                'active_id' => (string) $id
            ]);
        }

        // De lo contrario, hacemos una redirección normal de Inertia
        return redirect()->back()->with('success', 'Period activated successfully.');
    }
}

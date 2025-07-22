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

        // Opcional: desactivar el actual
        AcademicPeriod::where('is_active', true)->update(['is_active' => false]);

        AcademicPeriod::create($request->validate([
            'name'       => 'required|string|unique:academic_periods,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active' => 'required|boolean'
        ]));

        if ($request->is_active && AcademicPeriod::where('is_active', true)->exists()) {
            return back()->withErrors(['is_active' => 'An active period already exists.']);
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
        $request->validate([
            'name'       => 'required|string|unique:academic_periods,name,' . $academicPeriod->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        if ($request->boolean('is_active')) {
            AcademicPeriod::query()->update(['is_active' => false]);
        }

        $academicPeriod->update($request->all());

        return redirect()->back()->with('success', 'Period updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicPeriod $academicPeriod)
    {
        $academicPeriod->delete();

        return redirect()->back()->with('success', 'Period deleted successfully');
    }

    public function activate($id)
    {
        AcademicPeriod::query()->update(['is_active' => false]);
        AcademicPeriod::findOrFail($id)->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Period activated successfully.');
    }
}

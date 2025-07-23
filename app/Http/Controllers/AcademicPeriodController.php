<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        $periods = AcademicPeriod::orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/AcademicPeriods', [
            'periods' => $periods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_periods,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicPeriod::create($validated);

        return back()->with('success', 'Academic period created successfully.');
    }

    public function update(Request $request, $id)
    {
        $period = AcademicPeriod::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_periods,name,' . $period->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)->where('id', '!=', $period->id)->update(['is_active' => false]);
        }

        $period->update($validated);

        return back()->with('success', 'Academic period updated successfully.');
    }

    public function destroy($id)
    {
        $period = AcademicPeriod::findOrFail($id);
        $period->delete();

        return back()->with('success', 'Academic period deleted.');
    }

    public function activate($id)
    {
        $period = AcademicPeriod::findOrFail($id);

        AcademicPeriod::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
        $period->update(['is_active' => true]);

        return back()->with('success', 'Academic period activated.');
    }
}

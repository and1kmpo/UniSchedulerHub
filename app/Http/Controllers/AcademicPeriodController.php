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
        // Validar datos básicos
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_periods,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'enrollment_deadline' => 'nullable|date',
            'unenrollment_deadline' => 'nullable|date',
            'is_active'  => 'boolean',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $enrollmentDeadline = $validated['enrollment_deadline'] ?? null;
        $unenrollmentDeadline = $validated['unenrollment_deadline'] ?? null;

        // ✅ Validar que las fecha de inscripción esté dentro del rango permitido
        if ($enrollmentDeadline && ($enrollmentDeadline < $startDate || $enrollmentDeadline > $endDate)) {
            return response()->json([
                'error' => 'The enrollment deadline must be between the start and end date of the academic period.',
                'form' => $request->all(),
            ], 422);
        }


        // ✅ Validar que la fecha de cancelación esté dentro del rango permitido
        if ($unenrollmentDeadline) {
            if ($unenrollmentDeadline < $startDate || $unenrollmentDeadline > $endDate) {
                return response()->json([
                    'error' => 'The unenrollment deadline must be between the start and end date of the academic period.',
                    'form' => $request->all(),
                ], 422);
            }
        }

        // ✅ Validación de solapamiento robusta
        $overlap = AcademicPeriod::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subquery) use ($startDate, $endDate) {
                    $subquery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })->exists();

        if ($overlap) {
            return response()->json([
                'error' => 'The period dates overlap with an existing period.',
                'form' => $request->all(),
            ], 400);
        }

        // Desactivar periodo activo si se marca uno nuevo como activo
        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        try {
            $academicPeriod = AcademicPeriod::create($validated);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create period: ' . $e->getMessage(),
                'form' => $request->all(),
            ], 500);
        }

        return response()->json([
            'success' => 'Academic period created successfully.',
            'academicPeriod' => $academicPeriod,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $period = AcademicPeriod::findOrFail($id);

        // Validar campos básicos
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_periods,name,' . $period->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'enrollment_deadline' => 'nullable|date',
            'unenrollment_deadline' => 'nullable|date',
            'is_active'  => 'boolean',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $enrollmentDeadline = $validated['enrollment_deadline'] ?? null;
        $unenrollmentDeadline = $validated['unenrollment_deadline'] ?? null;

        // Validar que las fechas estén dentro del rango
        if ($enrollmentDeadline && ($enrollmentDeadline < $startDate || $enrollmentDeadline > $endDate)) {
            return response()->json([
                'error' => 'The enrollment deadline must be between the start and end date of the academic period.',
                'form' => $request->all(),
            ], 422);
        }

        // Validar que la fecha de cancelación esté dentro del rango
        if ($unenrollmentDeadline) {
            if ($unenrollmentDeadline < $startDate || $unenrollmentDeadline > $endDate) {
                return response()->json([
                    'error' => 'The unenrollment deadline must be between the start and end date of the academic period.',
                    'form' => $request->all(),
                ], 422);
            }
        }

        // Validar solapamiento con otros periodos (excluyendo este)
        $overlap = AcademicPeriod::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subquery) use ($startDate, $endDate) {
                    $subquery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })
            ->where('id', '!=', $period->id)
            ->exists();

        if ($overlap) {
            return response()->json([
                'error' => 'The period dates overlap with an existing period.',
                'form' => $request->all(),
            ], 400);
        }

        // Si se activa este periodo, desactivar otros
        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)
                ->where('id', '!=', $period->id)
                ->update(['is_active' => false]);
        }

        // Actualizar el periodo
        $period->update($validated);

        return response()->json([
            'success' => 'Academic period updated successfully.',
            'academicPeriod' => $period,
        ]);
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

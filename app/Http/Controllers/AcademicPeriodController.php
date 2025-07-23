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
        // Validar los datos
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_periods,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        // Validación de solapamiento de fechas
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $overlap = AcademicPeriod::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate]);
        })->exists();

        if ($overlap) {
            // Devolver una respuesta JSON con un error específico
            return response()->json([
                'error' => 'The period dates overlap with an existing period.',
                'form' => $request->all(),  // Mantener los datos del formulario
            ], 400);  // Código HTTP 400: Bad Request
        }

        // Desactivar el periodo activo anterior si corresponde
        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        // Crear el nuevo periodo
        try {
            $academicPeriod = AcademicPeriod::create($validated);
        } catch (\Exception $e) {
            // Manejar cualquier excepción durante la creación
            return response()->json([
                'error' => 'Failed to create period: ' . $e->getMessage(),
                'form' => $request->all(),  // Mantener los datos en el formulario
            ], 500);  // Código HTTP 500: Internal Server Error
        }

        // Si todo salió bien, devolver una respuesta exitosa
        return response()->json([
            'success' => 'Academic period created successfully.',
            'academicPeriod' => $academicPeriod,
        ], 201);  // Código HTTP 201: Created
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

        // Validación de solapamiento de fechas
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $overlap = AcademicPeriod::where(function ($query) use ($startDate, $endDate, $period) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->where('id', '!=', $period->id); // Excluir el periodo que estamos editando
        })->exists();

        if ($overlap) {
            return response()->json([
                'error' => 'The period dates overlap with an existing period.',
                'form' => $request->all(),  // Mantener los datos del formulario
            ], 400);
        }

        if ($validated['is_active']) {
            AcademicPeriod::where('is_active', true)->where('id', '!=', $period->id)->update(['is_active' => false]);
        }

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

<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BuildingController extends Controller
{
    public function index()
    {
        return Inertia::render('Buildings/Index', [
            'buildings' => Building::ordered()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Buildings/Create', [
            'suggestedCode' => $this->generateSuggestedCode()
        ]);
    }

    private function generateSuggestedCode()
    {
        $lastCode = Building::orderByDesc('id')->value('code');
        if (!$lastCode) {
            return 'B001';
        }

        // Extraer número y sumarle 1
        preg_match('/(\d+)/', $lastCode, $matches);
        $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;

        return 'B' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }


    public function store(Request $request)
    {
        // Nota: NO usamos 'unique' en el validador porque comprobamos withTrashed manualmente
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        // Si no viene code, generar uno
        if (empty($data['code'])) {
            $data['code'] = $this->generateSuggestedCode();
        }

        // Buscar existencia, incluyendo soft-deleted
        $existing = Building::withTrashed()->where('code', $data['code'])->first();

        if ($existing) {
            if ($existing->trashed()) {
                // Hay uno borrado con ese code -> indicar al front que existe trashed
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'trashed_exists',
                        'message' => 'A building with this code exists but is deleted.',
                        'building' => [
                            'id' => $existing->id,
                            'code' => $existing->code,
                            'name' => $existing->name,
                        ],
                    ], 409);
                }

                // Si no es una petición XHR, devolver flash (compatibilidad)
                return redirect()->back()->with('trashed_building', $existing->id);
            } else {
                // Ya existe activo => error de validación
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'errors' => ['code' => ['The code has already been taken.']]
                    ], 422);
                }
                return back()->withErrors(['code' => 'The code has already been taken.']);
            }
        }

        // No existe -> crear
        $building = Building::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Building created successfully',
                'building' => $building,
                'suggestedCode' => $this->generateSuggestedCode()
            ], 201);
        }

        // Compatibilidad Inertia / form normal
        return redirect()->route('buildings.create')->with([
            'success' => 'Building created successfully',
            'suggestedCode' => $this->generateSuggestedCode()
        ]);
    }

    public function restore(Request $request, $id)
    {
        $building = Building::withTrashed()->findOrFail($id);

        if (! $building->trashed()) {
            return response()->json(['message' => 'Building is not deleted'], 400);
        }

        // Restaurar y opcionalmente actualizar nombre si se envía
        $building->restore();

        if ($request->filled('name')) {
            $building->update(['name' => $request->input('name')]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Building restored successfully',
            'building' => $building
        ], 200);
    }
    public function edit(Building $building)
    {
        return Inertia::render('Buildings/Edit', [
            'building' => $building
        ]);
    }

    public function update(Request $request, Building $building)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:buildings,code,' . $building->id,
        ]);

        $building->update($request->only('name', 'code'));

        return redirect()->route('buildings.index')->with('success', 'Building updated.');
    }

    public function destroy(Building $building)
    {
        $building->delete();

        return redirect()->route('buildings.index')->with('success', 'Building deleted.');
    }
}

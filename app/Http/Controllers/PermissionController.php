<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(10);

        // Verificamos si la solicitud es JSON
        if (request()->wantsJson()) {
            return response()->json($permissions);
        }
        return response()->json($permissions, 201);
    }
    // Crear un nuevo permiso
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);

        $permission = Permission::create(['name' => $request->name]);

        return response()->json($permission, 201);
    }

    // Eliminar un permiso
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}

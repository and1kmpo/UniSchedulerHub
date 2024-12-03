<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions'])->paginate(10);
        //dd($users);

        // Verificamos si la solicitud es JSON
        if (request()->wantsJson()) {
            return response()->json($roles);
        }
        return response()->json($roles, 201);
    }

    // Crear un nuevo rol
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);

        // Especifica el guard_name
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json($role, 201);
    }

    // Asignar un permiso a un rol
    public function assignPermissionToRole(Request $request, Role $role)
    {
        $permissionName = $request->input('permission');
        $permission = Permission::where('name', $permissionName)->first();

        if (!$permission) {
            return response()->json(['error' => 'Permission not found.'], 404);
        }

        $role->givePermissionTo($permission);

        return response()->json(['message' => 'Permission assigned successfully']);
    }

    // Asignar varios permisos a un rol
    public function updatePermissions(Request $request, Role $role)
    {
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return response()->json(['message' => 'Permissions updated successfully']);
    }

    // Eliminar un rol
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}

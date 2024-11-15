<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    public function assignRoleToUser(Request $request, User $user)
    {
        if (!Auth::$user()->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $role = Role::findByName($request->role);
        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function removeRoleFromUser(Request $request, User $user)
    {
        if (!Auth::$user()->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->removeRole($request->role);

        return response()->json(['message' => 'Role removed successfully']);
    }
}

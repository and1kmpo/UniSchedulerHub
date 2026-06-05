<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'admin',
            'academic_coordinator',
            'professor',
            'student',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = PermissionCatalog::all();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $permissionModels = Permission::query()
            ->whereIn('name', $permissions)
            ->get()
            ->keyBy('name')
            ->toBase();

        foreach (PermissionCatalog::byRole() as $role => $rolePermissions) {
            Role::findByName($role)->syncPermissions(
                $permissionModels->only($rolePermissions)->values()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

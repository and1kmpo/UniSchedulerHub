<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
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

        $permissions = [
            'manage users',
            'manage roles',
            'manage programs',
            'manage subjects',
            'manage professors',
            'manage students',
            'manage academic periods',
            'manage infrastructure',
            'manage class groups',
            'manage enrollments',
            'manage grades',
            'view professor subjects',
            'view student subjects',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionModels = Permission::where('guard_name', 'web')
            ->whereIn('name', $permissions)
            ->get()
            ->keyBy('name');

        Role::findByName('admin', 'web')->syncPermissions($permissionModels->values());

        Role::findByName('academic_coordinator', 'web')->syncPermissions($permissionModels->only([
            'manage programs',
            'manage subjects',
            'manage professors',
            'manage students',
            'manage academic periods',
            'manage infrastructure',
            'manage class groups',
            'manage enrollments',
            'view professor subjects',
            'view student subjects',
            'view reports',
        ])->values());

        Role::findByName('professor', 'web')->syncPermissions($permissionModels->only([
            'manage grades',
            'view professor subjects',
            'view student subjects',
            'view reports',
        ])->values());

        Role::findByName('student', 'web')->syncPermissions($permissionModels->only([
            'view student subjects',
        ])->values());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

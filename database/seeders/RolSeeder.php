<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    public function run(): void
    {
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

        Role::findByName('admin')->syncPermissions($permissions);

        Role::findByName('academic_coordinator')->syncPermissions([
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
        ]);

        Role::findByName('professor')->syncPermissions([
            'manage grades',
            'view professor subjects',
            'view student subjects',
            'view reports',
        ]);

        Role::findByName('student')->syncPermissions([
            'view student subjects',
        ]);
    }
}

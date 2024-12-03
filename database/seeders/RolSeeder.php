<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleStudent = Role::create(['name' => 'student']);
        $roleProfessor = Role::create(['name' => 'professor']);

        // Permisos
        $permissions = [
            // CRUD General
            'manage programs',
            'manage subjects',
            'manage professors',
            'manage students',
            'manage users',

            // Permisos específicos de asignaturas
            'assign subjects to students',
            'assign subjects to professors',
            'view subjects with professors',
            'view professor subjects',
            'view student subjects',

            // Reportes
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar permisos a roles
        $roleAdmin->givePermissionTo(Permission::all());
        $roleProfessor->givePermissionTo([
            'view professor subjects',
            'view student subjects',
            'view reports',
        ]);
        $roleStudent->givePermissionTo([
            'view student subjects',
        ]);
    }
}

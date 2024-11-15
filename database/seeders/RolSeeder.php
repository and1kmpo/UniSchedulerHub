<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $role_admin = Role::create(['name' => 'admin']);
        $role_student = Role::create(['name' => 'student']);
        $role_professor = Role::create(['name' => 'professor']);

        $permission_view_dashboard = Permission::create(['name' => 'view dashboard']);

        $permission_create_role = Permission::create(['name' => 'create roles']);
        $permission_read_role = Permission::create(['name' => 'read roles']);
        $permission_update_role = Permission::create(['name' => 'update roles']);
        $permission_delete_role = Permission::create(['name' => 'delete roles']);

        $permission_create_professor = Permission::create(['name' => 'create professors']);
        $permission_read_professor = Permission::create(['name' => 'read professors']);
        $permission_update_professor = Permission::create(['name' => 'update professors']);
        $permission_delete_professor = Permission::create(['name' => 'delete professors']);

        $permission_create_program = Permission::create(['name' => 'create programs']);
        $permission_read_program = Permission::create(['name' => 'read programs']);
        $permission_update_program = Permission::create(['name' => 'update programs']);
        $permission_delete_program = Permission::create(['name' => 'delete programs']);

        $permission_create_student = Permission::create(['name' => 'create students']);
        $permission_read_student = Permission::create(['name' => 'read students']);
        $permission_update_student = Permission::create(['name' => 'update students']);
        $permission_delete_student = Permission::create(['name' => 'delete students']);

        $permission_create_subject = Permission::create(['name' => 'create subjects']);
        $permission_read_subject = Permission::create(['name' => 'read subjects']);
        $permission_update_subject = Permission::create(['name' => 'update subjects']);
        $permission_delete_subject = Permission::create(['name' => 'delete subjects']);

        $permissions_student = [
            $permission_read_professor,
            $permission_read_program,
            $permission_read_student,
            $permission_read_subject,
        ];

        $permissions_professor = [
            $permission_read_professor,
            $permission_update_professor,
            $permission_read_student,
            $permission_update_student,
            $permission_view_dashboard
        ];

        $role_admin->syncPermissions(Permission::all());
        $role_student->syncPermissions($permissions_student);
        $role_professor->syncPermissions($permissions_professor);
    }
}

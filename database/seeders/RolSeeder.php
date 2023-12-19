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
        $role_editor = Role::create(['name' => 'editor']);

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

        $permissions_admin = [
            $permission_create_role,
            $permission_read_role,
            $permission_update_role,
            $permission_delete_role,
            $permission_create_professor,
            $permission_read_professor,
            $permission_update_professor,
            $permission_delete_professor,
            $permission_create_program,
            $permission_read_program,
            $permission_update_program,
            $permission_delete_program,
            $permission_create_student,
            $permission_read_student,
            $permission_update_student,
            $permission_delete_student,
            $permission_create_subject,
            $permission_read_subject,
            $permission_update_subject,
            $permission_delete_subject
        ];

        $permissions_editor = [
            $permission_create_professor,
            $permission_read_professor,
            $permission_update_professor,
            $permission_delete_professor,
            $permission_create_program,
            $permission_read_program,
            $permission_update_program,
            $permission_delete_program,
            $permission_create_student,
            $permission_read_student,
            $permission_update_student,
            $permission_delete_student,
            $permission_create_subject,
            $permission_read_subject,
            $permission_update_subject,
            $permission_delete_subject
        ];

        $role_admin->syncPermissions($permissions_admin);
        $role_editor->syncPermissions($permissions_editor);
    }
}

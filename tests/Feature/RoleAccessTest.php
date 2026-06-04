<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
    }

    public function test_admin_can_access_security_and_academic_administration(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('students.index'))->assertOk();
        $this->actingAs($admin)->get(route('academic-periods.index'))->assertOk();
        $this->actingAs($admin)->get(route('academic-audit-logs.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.student-assignments.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.student-assignments.export'))->assertOk();
        $this->actingAs($admin)->get(route('reports.professor-load.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.professor-load.export'))->assertOk();
    }

    public function test_academic_coordinator_can_access_academic_operations_but_not_security_administration(): void
    {
        $coordinator = $this->userWithRole('academic_coordinator');

        $this->actingAs($coordinator)->get(route('students.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('class-groups.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('academic-periods.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('academic-audit-logs.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.student-assignments.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.student-assignments.export'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.professor-load.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.professor-load.export'))->assertOk();

        $this->actingAs($coordinator)->get(route('users.index'))->assertForbidden();
        $this->actingAs($coordinator)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($coordinator)->get(route('permissions.index'))->assertForbidden();
    }

    public function test_professor_is_limited_to_teaching_workspace(): void
    {
        $professor = $this->userWithRole('professor');

        $this->actingAs($professor)->get(route('admin.group-enrollments.index'))->assertOk();

        $this->actingAs($professor)->get(route('students.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('class-groups.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('academic-audit-logs.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.student-assignments.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.student-assignments.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.professor-load.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.professor-load.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('users.index'))->assertForbidden();
    }

    public function test_student_cannot_access_administrative_or_professor_crud_routes(): void
    {
        $student = $this->userWithRole('student');

        $this->actingAs($student)->get(route('students.index'))->assertForbidden();
        $this->actingAs($student)->get(route('professors.index'))->assertForbidden();
        $this->actingAs($student)->get(route('subjects.index'))->assertForbidden();
        $this->actingAs($student)->get(route('academic-audit-logs.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.student-assignments.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.student-assignments.export'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.professor-load.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.professor-load.export'))->assertForbidden();
        $this->actingAs($student)->get(route('users.index'))->assertForbidden();
    }

    public function test_student_cannot_jump_to_group_grade_routes_by_typing_url(): void
    {
        $student = $this->userWithRole('student');
        $professor = $this->userWithRole('professor');
        $subject = Subject::create([
            'code' => 'SEC101',
            'name' => 'Security Foundations',
            'description' => 'Security test subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);

        $group = ClassGroup::create([
            'subject_id' => $subject->id,
            'professor_id' => $professor->id,
            'semester' => '2026-I',
            'modality' => 'In-person',
            'shift' => 'Day',
            'capacity' => 30,
            'status' => ClassGroup::STATUS_PUBLISHED,
        ]);

        $this->actingAs($student)
            ->get(route('groups.grades.index', $group))
            ->assertForbidden();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $coordinator = $this->userWithRole('academic_coordinator');
        $student = $this->userWithRole('student');

        $this->actingAs($coordinator)
            ->get('/dashboard')
            ->assertRedirect(route('dashboard'));

        $this->flushSession();

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertRedirect(route('student.subjects'));
    }

    public function test_dashboard_payload_is_role_aware(): void
    {
        $admin = $this->userWithRole('admin');
        $professor = $this->userWithRole('professor');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->where('dashboardType', 'academic')
                ->has('academicDashboard.metrics')
            );

        $this->flushSession();

        $this->actingAs($professor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->where('dashboardType', 'professor')
                ->has('professorDashboard.metrics')
            );
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

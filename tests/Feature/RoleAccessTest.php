<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Support\PermissionCatalog;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
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
        $this->actingAs($admin)->get(route('reports.classroom-occupancy.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.classroom-occupancy.export'))->assertOk();
        $this->actingAs($admin)->get(route('reports.group-capacity-conflicts.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.group-capacity-conflicts.export'))->assertOk();
        $this->actingAs($admin)->get(route('reports.grade-operations.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.grade-operations.export'))->assertOk();
        $this->actingAs($admin)->get(route('reports.academic-events.index'))->assertOk();
        $this->actingAs($admin)->get(route('reports.academic-events.export'))->assertOk();
        $this->actingAs($admin)->get(route('roles.index'))->assertRedirect(route('users.index'));
        $this->actingAs($admin)->get(route('permissions.index'))->assertRedirect(route('users.index'));
    }

    public function test_role_permissions_are_managed_by_code_catalog(): void
    {
        foreach (PermissionCatalog::byRole() as $roleName => $expectedPermissions) {
            $role = Role::findByName($roleName);

            $this->assertSame(
                collect($expectedPermissions)->sort()->values()->all(),
                $role->permissions->pluck('name')->sort()->values()->all()
            );
        }

        $this->assertSame(
            collect(PermissionCatalog::all())->sort()->values()->all(),
            Permission::query()->pluck('name')->sort()->values()->all()
        );

        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->post('/roles', ['name' => 'custom_role'])
            ->assertRedirect(route('users.index'));

        $this->actingAs($admin)
            ->post('/permissions', ['name' => 'custom permission'])
            ->assertRedirect(route('users.index'));
    }

    public function test_admin_can_create_operational_user_without_academic_profile(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->postJson(route('users.store'), [
                'name' => 'Academic Coordinator',
                'email' => 'coordinator@example.test',
                'role' => 'academic_coordinator',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'User created successfully');

        $user = User::where('email', 'coordinator@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('academic_coordinator'));
        $this->assertNull($user->student);
        $this->assertNull($user->professor);
    }

    public function test_admin_cannot_delete_or_reclassify_user_with_academic_history(): void
    {
        $admin = $this->userWithRole('admin');
        $professor = $this->userWithRole('professor');
        $professor->professor()->create([
            'document' => 'P-SEC-001',
            'phone' => '3000000001',
            'address' => 'Professor street',
            'city' => 'Bogota',
        ]);

        $subject = Subject::create([
            'code' => 'HIST101',
            'name' => 'History Protected Subject',
            'description' => 'Subject used to protect destructive user changes',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);

        ClassGroup::create([
            'subject_id' => $subject->id,
            'professor_id' => $professor->id,
            'semester' => '2026-I',
            'modality' => 'In-person',
            'shift' => 'Day',
            'capacity' => 30,
            'status' => ClassGroup::STATUS_PUBLISHED,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('users.destroy', $professor))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This user has academic history. Deactivate the account instead of deleting it.');

        $this->assertDatabaseHas('users', ['id' => $professor->id]);
        $this->assertDatabaseHas('professors', ['user_id' => $professor->id]);

        $this->actingAs($admin)
            ->putJson(route('users.update', $professor), [
                'name' => $professor->name,
                'email' => $professor->email,
                'role' => 'admin',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Academic roles are managed from Students and Professors. Deactivate the account instead of changing its role.');

        $this->assertTrue($professor->fresh()->hasRole('professor'));
        $this->assertDatabaseHas('professors', ['user_id' => $professor->id]);
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
        $this->actingAs($coordinator)->get(route('reports.classroom-occupancy.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.classroom-occupancy.export'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.group-capacity-conflicts.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.group-capacity-conflicts.export'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.grade-operations.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.grade-operations.export'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.academic-events.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('reports.academic-events.export'))->assertOk();

        $this->actingAs($coordinator)->get(route('users.index'))->assertForbidden();
        $this->actingAs($coordinator)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($coordinator)->get(route('permissions.index'))->assertForbidden();

        $target = $this->userWithRole('academic_coordinator');

        $this->actingAs($coordinator)
            ->postJson(route('users.store'), [
                'name' => 'Unauthorized Admin',
                'email' => 'unauthorized.admin@example.test',
                'role' => 'admin',
            ])
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->patchJson(route('users.activate', $target))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->patchJson(route('users.deactivate', $target))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->patchJson(route('users.reset-password', $target))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->deleteJson(route('users.destroy', $target))
            ->assertForbidden();
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
        $this->actingAs($professor)->get(route('reports.classroom-occupancy.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.classroom-occupancy.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.group-capacity-conflicts.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.group-capacity-conflicts.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.grade-operations.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.grade-operations.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.academic-events.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('reports.academic-events.export'))->assertForbidden();
        $this->actingAs($professor)->get(route('users.index'))->assertForbidden();
        $this->actingAs($professor)->get(route('student.subjects'))->assertForbidden();
        $this->actingAs($professor)->get(route('student.schedule'))->assertForbidden();
        $this->actingAs($professor)->get(route('student.subject-enrollment.index'))->assertForbidden();

        $target = $this->userWithRole('academic_coordinator');

        $this->actingAs($professor)
            ->patchJson(route('users.reset-password', $target))
            ->assertForbidden();
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
        $this->actingAs($student)->get(route('reports.classroom-occupancy.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.classroom-occupancy.export'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.group-capacity-conflicts.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.group-capacity-conflicts.export'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.grade-operations.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.grade-operations.export'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.academic-events.index'))->assertForbidden();
        $this->actingAs($student)->get(route('reports.academic-events.export'))->assertForbidden();
        $this->actingAs($student)->get(route('users.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.group-enrollments.index'))->assertForbidden();
        $this->actingAs($student)->get(route('professor.subjects'))->assertForbidden();
        $this->actingAs($student)->get(route('professor.schedule'))->assertForbidden();

        $target = $this->userWithRole('academic_coordinator');

        $this->actingAs($student)
            ->patchJson(route('users.reset-password', $target))
            ->assertForbidden();
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

    public function test_role_navigation_contract_is_role_aware(): void
    {
        $admin = $this->userWithRole('admin');
        $coordinator = $this->userWithRole('academic_coordinator');
        $professor = $this->userWithRole('professor');
        $student = $this->userWithRole('student');

        Student::factory()->create([
            'user_id' => $student->id,
        ]);

        $this->assertNavigationFor($admin, route('dashboard'), [
            'Insights',
            'Dashboard',
            'Reports',
            'Audit Logs',
            'Core',
            'Programs',
            'Subjects',
            'Professors',
            'Students',
            'Class Groups',
            'Sync',
            'Enrollment Management',
            'Academic Periods',
            'Rooms',
            'Buildings',
            'Classrooms',
            'Admin',
            'Identity & Access',
        ]);

        $this->assertNavigationFor($coordinator, route('students.index'), [
            'Insights',
            'Dashboard',
            'Reports',
            'Audit Logs',
            'Core',
            'Programs',
            'Subjects',
            'Professors',
            'Students',
            'Class Groups',
            'Sync',
            'Enrollment Management',
            'Academic Periods',
            'Rooms',
            'Buildings',
            'Classrooms',
        ], [
            'Admin',
            'Identity & Access',
        ]);

        $this->assertNavigationFor($professor, route('dashboard'), [
            'Insights',
            'Dashboard',
            'Teaching',
            'My Subjects',
            'My Schedule',
            'Group Enrollments',
            'Account',
            'Profile',
        ], [
            'Reports',
            'Students',
            'Identity & Access',
            'Academic Periods',
        ]);

        $this->assertNavigationFor($student, route('student.subjects'), [
            'Student Flow',
            'My Subjects',
            'My Schedule',
            'Subject Enrollment',
            'Account',
            'Profile',
        ], [
            'Dashboard',
            'Reports',
            'Students',
            'Group Enrollments',
            'Identity & Access',
        ]);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function assertNavigationFor(User $user, string $url, array $expectedLabels, array $unexpectedLabels = []): void
    {
        $this->flushSession();

        $response = $this->actingAs($user)
            ->get($url)
            ->assertOk();

        $navigation = collect($response->viewData('page')['props']['navigation']['main'] ?? []);
        $labels = $this->navigationLabels($navigation->all());

        $this->assertSame($expectedLabels, $labels);

        foreach ($unexpectedLabels as $label) {
            $this->assertNotContains($label, $labels);
        }

        $this->assertTrue($this->navigationLeavesHaveRoutes($navigation->all()));
    }

    private function navigationLabels(array $items): array
    {
        $labels = [];

        foreach ($items as $item) {
            $labels[] = $item['label'];

            if (! empty($item['children'])) {
                array_push($labels, ...$this->navigationLabels($item['children']));
            }
        }

        return $labels;
    }

    private function navigationLeavesHaveRoutes(array $items): bool
    {
        foreach ($items as $item) {
            if (! empty($item['children'])) {
                if (! $this->navigationLeavesHaveRoutes($item['children'])) {
                    return false;
                }

                continue;
            }

            if (! filled($item['route'] ?? null)) {
                return false;
            }
        }

        return true;
    }
}

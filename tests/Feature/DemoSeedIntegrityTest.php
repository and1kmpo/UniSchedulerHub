<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeedIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_demo_academic_environment(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDemoUser('admin@unischedulerhub.test', 'admin');
        $this->assertDemoUser('coordinator@unischedulerhub.test', 'academic_coordinator');
        $this->assertDemoUser('professor@unischedulerhub.test', 'professor');
        $this->assertDemoUser('professor.math@unischedulerhub.test', 'professor');
        $this->assertDemoUser('student@unischedulerhub.test', 'student');
        $this->assertDemoUser('student.enrolled@unischedulerhub.test', 'student');
        $this->assertDemoUser('student.probation@unischedulerhub.test', 'student');
        $this->assertDemoUser('student.suspended@unischedulerhub.test', 'student');
        $this->assertDemoUser('student.graded@unischedulerhub.test', 'student');

        $this->assertSame(1, AcademicPeriod::where('is_active', true)->count());
        $this->assertGreaterThanOrEqual(5, Subject::count());
        $this->assertGreaterThanOrEqual(5, Student::count());
        $this->assertGreaterThanOrEqual(7, ClassGroup::where('status', ClassGroup::STATUS_PUBLISHED)->count());
        $this->assertGreaterThanOrEqual(7, ClassSchedule::count());
        $this->assertGreaterThanOrEqual(3, SubjectEnrollment::count());
        $this->assertGreaterThanOrEqual(1, Grade::count());

        $this->artisan('check:seed-integrity')->assertSuccessful();
    }

    public function test_seeded_admin_can_access_academic_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@unischedulerhub.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_seeded_professor_can_access_teaching_portal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $professor = User::where('email', 'professor@unischedulerhub.test')->firstOrFail();

        $this->actingAs($professor)
            ->get(route('professor.subjects'))
            ->assertOk();
    }

    public function test_seeded_student_can_access_enrollment_portal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'student@unischedulerhub.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.subject-enrollment.index'))
            ->assertOk();
    }

    public function test_seeded_demo_users_can_exercise_main_academic_api_flows(): void
    {
        $this->seed(DatabaseSeeder::class);

        $coordinator = User::where('email', 'coordinator@unischedulerhub.test')->firstOrFail();
        $student = User::where('email', 'student@unischedulerhub.test')->firstOrFail();
        $subject = Subject::where('code', 'SWE101')->firstOrFail();

        $this->actingAs($coordinator)
            ->getJson('/api/reports/student-assignments')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->actingAs($student)
            ->getJson(route('api.subjects.available-groups', $subject))
            ->assertOk()
            ->assertJsonPath('meta.student_id', $student->student->id)
            ->assertJsonCount(2, 'data');
    }

    private function assertDemoUser(string $email, string $role): void
    {
        $user = User::where('email', $email)->first();

        $this->assertNotNull($user, "Expected demo user {$email} to exist.");
        $this->assertTrue($user->hasRole($role), "Expected {$email} to have role {$role}.");
    }
}

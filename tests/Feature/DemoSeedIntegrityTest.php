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

    public function test_seeded_demo_users_can_open_operational_role_workspaces(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@unischedulerhub.test')->firstOrFail();
        $professor = User::where('email', 'professor@unischedulerhub.test')->firstOrFail();
        $student = User::where('email', 'student.enrolled@unischedulerhub.test')->firstOrFail();

        $adminResponse = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $adminProps = $adminResponse->viewData('page')['props'];

        $this->assertSame('academic', $adminProps['dashboardType']);
        $this->assertGreaterThan(0, $adminProps['academicDashboard']['metrics']['published_groups']);
        $this->assertGreaterThan(0, $adminProps['academicDashboard']['capacity']['total_capacity']);
        $this->assertNotEmpty($adminProps['academicDashboard']['charts']['capacity_by_group']);

        $reportsIndexResponse = $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk();

        $reportsIndexProps = $reportsIndexResponse->viewData('page')['props'];

        $this->assertCount(2, $reportsIndexProps['reports']);

        $reportResponse = $this->actingAs($admin)
            ->get(route('reports.student-assignments.index'))
            ->assertOk();

        $reportProps = $reportResponse->viewData('page')['props'];

        $this->assertGreaterThan(0, $reportProps['summary']['students']);
        $this->assertNotEmpty($reportProps['students']['data']);

        $csvResponse = $this->actingAs($admin)
            ->get(route('reports.student-assignments.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $csvResponse->streamedContent();

        $this->assertStringContainsString('Student document', $csv);
        $this->assertStringContainsString('Subject code', $csv);
        $this->assertStringContainsString('Professor', $csv);

        $professorLoadResponse = $this->actingAs($admin)
            ->get(route('reports.professor-load.index'))
            ->assertOk();

        $professorLoadProps = $professorLoadResponse->viewData('page')['props'];

        $this->assertGreaterThan(0, $professorLoadProps['summary']['professors']);
        $this->assertGreaterThan(0, $professorLoadProps['summary']['groups']);
        $this->assertNotEmpty($professorLoadProps['professors']['data']);

        $professorLoadCsvResponse = $this->actingAs($admin)
            ->get(route('reports.professor-load.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $professorLoadCsv = $professorLoadCsvResponse->streamedContent();

        $this->assertStringContainsString('Professor document', $professorLoadCsv);
        $this->assertStringContainsString('Pending grades', $professorLoadCsv);

        $this->flushSession();

        $professorResponse = $this->actingAs($professor)
            ->get(route('professor.subjects'))
            ->assertOk();

        $professorProps = $professorResponse->viewData('page')['props'];

        $this->assertSame('ready', $professorProps['systemState']);
        $this->assertGreaterThan(0, $professorProps['summary']['groups']);
        $this->assertNotEmpty($professorProps['groups']);

        $professorScheduleResponse = $this->actingAs($professor)
            ->get(route('professor.schedule'))
            ->assertOk();

        $professorScheduleProps = $professorScheduleResponse->viewData('page')['props'];

        $this->assertNotEmpty($professorScheduleProps['currentSchedules']);
        $this->assertGreaterThan(0, $professorScheduleProps['summary']['blocks']);

        $this->flushSession();

        $studentResponse = $this->actingAs($student)
            ->get(route('student.subjects'))
            ->assertOk();

        $studentProps = $studentResponse->viewData('page')['props'];

        $this->assertGreaterThan(0, $studentProps['summary']['active_subjects']);
        $this->assertGreaterThanOrEqual(7, $studentProps['summary']['current_credits']);
        $this->assertNotEmpty($studentProps['subjects']);
        $this->assertNotNull($studentProps['currentPeriod']);

        $scheduleResponse = $this->actingAs($student)
            ->get(route('student.schedule'))
            ->assertOk();

        $scheduleProps = $scheduleResponse->viewData('page')['props'];

        $this->assertNotEmpty($scheduleProps['currentSchedules']);
        $this->assertSame($studentProps['currentPeriod']['id'], $scheduleProps['currentPeriod']['id']);
    }

    private function assertDemoUser(string $email, string $role): void
    {
        $user = User::where('email', $email)->first();

        $this->assertNotNull($user, "Expected demo user {$email} to exist.");
        $this->assertTrue($user->hasRole($role), "Expected {$email} to have role {$role}.");
    }
}

<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use Database\Seeders\AcademicPeriodStatusSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SubjectEnrollmentStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAcademicResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolSeeder::class,
            AcademicPeriodStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class,
        ]);
    }

    public function test_academic_api_requires_authentication(): void
    {
        $this->getJson('/api/students')->assertUnauthorized();
    }

    public function test_admin_can_list_academic_rest_resources(): void
    {
        $admin = $this->userWithRole('admin');

        Student::factory()->create();
        $professorUser = $this->userWithRole('professor');
        $professorUser->professor()->create([
            'document' => 'P-API-001',
            'phone' => '3000000000',
            'address' => 'Professor API Address',
            'city' => 'Bogota',
        ]);
        Subject::factory()->create(['name' => 'API Calculus']);

        $this->actingAs($admin)
            ->getJson('/api/students')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->actingAs($admin)
            ->getJson('/api/professors')
            ->assertOk()
            ->assertJsonPath('data.0.document', 'P-API-001');

        $this->actingAs($admin)
            ->getJson('/api/subjects?search=Calculus')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'API Calculus');
    }

    public function test_student_assignment_report_uses_enrollments_with_class_group_professor(): void
    {
        $admin = $this->userWithRole('admin');
        $professorUser = $this->userWithRole('professor', [
            'name' => 'Preferred Calculus Professor',
            'email' => 'preferred.professor@example.test',
        ]);

        $professorUser->professor()->create([
            'document' => 'P-API-002',
            'phone' => '3000000001',
            'address' => 'Professor Address',
            'city' => 'Bogota',
        ]);

        $program = Program::factory()->create(['name' => 'Software Engineering API']);
        $studentUser = $this->userWithRole('student', [
            'name' => 'Report Student',
            'email' => 'report.student@example.test',
        ]);
        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'program_id' => $program->id,
            'document' => 'S-API-001',
        ]);
        $subject = Subject::factory()->create([
            'name' => 'Differential Calculus API',
            'credits' => 4,
        ]);
        $period = AcademicPeriod::factory()->create(['name' => '2026-I']);
        $group = ClassGroup::factory()->create([
            'subject_id' => $subject->id,
            'professor_id' => $professorUser->id,
            'academic_period_id' => $period->id,
            'code' => 'CAL-API-G1',
        ]);

        SubjectEnrollment::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'class_group_id' => $group->id,
            'status_id' => SubjectEnrollmentStatus::where('code', 'enrolled')->value('id'),
            'enrolled_at' => now(),
            'enrolled_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/reports/student-assignments')
            ->assertOk()
            ->assertJsonPath('data.0.student.name', 'Report Student')
            ->assertJsonPath('data.0.assignments.0.subject.name', 'Differential Calculus API')
            ->assertJsonPath('data.0.assignments.0.professor.name', 'Preferred Calculus Professor')
            ->assertJsonPath('data.0.summary.active_credits', 4);
    }

    private function userWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole($role);

        return $user;
    }
}

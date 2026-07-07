<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ClassGroup;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Professor;
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

    public function test_academic_coordinator_can_access_academic_rest_resources(): void
    {
        $coordinator = $this->userWithRole('academic_coordinator');

        Student::factory()->create();
        Subject::factory()->create(['name' => 'Coordinator API Subject']);

        $this->actingAs($coordinator)
            ->getJson('/api/students')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->actingAs($coordinator)
            ->getJson('/api/subjects?search=Coordinator')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Coordinator API Subject');
    }

    public function test_admin_can_create_student_through_rest_api(): void
    {
        $admin = $this->userWithRole('admin');
        $program = Program::factory()->create(['name' => 'REST Software Engineering']);
        $curriculum = Curriculum::factory()->create([
            'program_id' => $program->id,
            'code' => 'REST-CUR-001',
        ]);

        $this->actingAs($admin)
            ->postJson('/api/students', [
                'document' => 'REST-STU-001',
                'name' => 'REST Student',
                'phone' => '3001112233',
                'email' => 'rest.student@example.test',
                'password' => 'password',
                'address' => 'REST Student Address',
                'city' => 'Bogota',
                'semester' => 3,
                'program_id' => $program->id,
                'curriculum_id' => $curriculum->id,
                'academic_status' => Student::STATUS_ACTIVE,
            ])
            ->assertCreated()
            ->assertJsonPath('data.document', 'REST-STU-001')
            ->assertJsonPath('data.name', 'REST Student')
            ->assertJsonPath('data.program.name', 'REST Software Engineering')
            ->assertJsonPath('data.curriculum.code', 'REST-CUR-001');

        $this->assertDatabaseHas('users', [
            'email' => 'rest.student@example.test',
        ]);
        $this->assertDatabaseHas('students', [
            'document' => 'REST-STU-001',
            'program_id' => $program->id,
            'curriculum_id' => $curriculum->id,
        ]);
        $this->assertTrue(User::where('email', 'rest.student@example.test')->first()->hasRole('student'));
    }

    public function test_admin_can_create_professor_through_rest_api(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->postJson('/api/professors', [
                'document' => 'REST-PROF-001',
                'name' => 'REST Professor',
                'phone' => '3004445566',
                'email' => 'rest.professor@example.test',
                'password' => 'password',
                'address' => 'REST Professor Address',
                'city' => 'Medellin',
            ])
            ->assertCreated()
            ->assertJsonPath('data.document', 'REST-PROF-001')
            ->assertJsonPath('data.name', 'REST Professor')
            ->assertJsonPath('data.email', 'rest.professor@example.test');

        $this->assertDatabaseHas('professors', [
            'document' => 'REST-PROF-001',
            'city' => 'Medellin',
        ]);
        $this->assertTrue(User::where('email', 'rest.professor@example.test')->first()->hasRole('professor'));
    }

    public function test_admin_can_create_subject_through_rest_api(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->postJson('/api/subjects', [
                'name' => 'REST Differential Calculus',
                'description' => 'Calculus for REST API validation.',
                'credits' => 4,
                'knowledge_area' => 'Mathematics',
                'elective' => false,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'REST Differential Calculus')
            ->assertJsonPath('data.credits', 4)
            ->assertJsonPath('data.knowledge_area', 'Mathematics')
            ->assertJsonPath('data.elective', false);

        $subject = Subject::where('name', 'REST Differential Calculus')->first();

        $this->assertNotNull($subject);
        $this->assertNotEmpty($subject->code);
    }

    public function test_professor_and_student_cannot_access_administrative_api_resources(): void
    {
        $professor = $this->userWithRole('professor');
        $student = $this->userWithRole('student');

        foreach ([$professor, $student] as $user) {
            $this->actingAs($user)->getJson('/api/students')->assertForbidden();
            $this->actingAs($user)->getJson('/api/professors')->assertForbidden();
            $this->actingAs($user)->getJson('/api/subjects')->assertForbidden();
            $this->actingAs($user)->getJson('/api/reports/student-assignments')->assertForbidden();
        }
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

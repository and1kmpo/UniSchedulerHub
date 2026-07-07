<?php

namespace Tests\Feature;

use App\Models\AcademicAuditLog;
use App\Models\AcademicRequest;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
    }

    public function test_student_can_submit_academic_request_and_audit_log_is_created(): void
    {
        $student = $this->studentUser();

        $this->actingAs($student)
            ->post(route('academic-requests.store'), [
                'type' => AcademicRequest::TYPE_GRADE_REVIEW,
                'title' => 'Review final grade for Calculus I',
                'description' => 'I need academic coordination to review the final grade because the last activity was not reflected.',
            ])
            ->assertRedirect(route('academic-requests.index'));

        $request = AcademicRequest::firstOrFail();

        $this->assertSame(AcademicRequest::STATUS_SUBMITTED, $request->status);
        $this->assertSame($student->student->id, $request->student_id);

        $this->assertDatabaseHas('academic_audit_logs', [
            'action' => 'academic_request.submitted',
            'auditable_type' => AcademicRequest::class,
            'auditable_id' => $request->id,
        ]);
    }

    public function test_coordinator_can_approve_request_and_student_cannot_review_it(): void
    {
        $student = $this->studentUser();
        $coordinator = $this->userWithRole('academic_coordinator');

        $academicRequest = AcademicRequest::create([
            'student_id' => $student->student->id,
            'created_by' => $student->id,
            'type' => AcademicRequest::TYPE_LATE_WITHDRAWAL,
            'status' => AcademicRequest::STATUS_SUBMITTED,
            'title' => 'Late withdrawal from Databases',
            'description' => 'The student is requesting a late withdrawal for documented academic reasons.',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->patch(route('academic-requests.approve', $academicRequest), [
                'decision_reason' => 'Student should not be able to approve this request.',
            ])
            ->assertForbidden();

        $this->flushSession();

        $this->actingAs($coordinator)
            ->patch(route('academic-requests.approve', $academicRequest), [
                'decision_reason' => 'Approved after academic coordination reviewed the supporting context.',
            ])
            ->assertRedirect(route('academic-requests.index'));

        $academicRequest->refresh();

        $this->assertSame(AcademicRequest::STATUS_APPROVED, $academicRequest->status);
        $this->assertSame($coordinator->id, $academicRequest->reviewed_by);

        $this->assertDatabaseHas('academic_audit_logs', [
            'action' => 'academic_request.approved',
            'auditable_type' => AcademicRequest::class,
            'auditable_id' => $academicRequest->id,
        ]);
    }

    public function test_professor_cannot_access_academic_requests(): void
    {
        $professor = $this->userWithRole('professor');

        $this->actingAs($professor)
            ->get(route('academic-requests.index'))
            ->assertForbidden();
    }

    private function studentUser(): User
    {
        $user = $this->userWithRole('student');
        $program = Program::create([
            'name' => 'Software Engineering',
            'description' => 'Test program',
        ]);

        Student::create([
            'user_id' => $user->id,
            'document' => fake()->unique()->numerify('########'),
            'phone' => '3000000000',
            'address' => 'Main street',
            'city' => 'Bogota',
            'semester' => 1,
            'program_id' => $program->id,
            'academic_status' => Student::STATUS_ACTIVE,
        ]);

        return $user->load('student');
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

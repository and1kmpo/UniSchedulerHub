<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use Database\Seeders\AcademicPeriodStatusSeeder;
use Database\Seeders\GradeStatusSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SubjectEnrollmentStatusSeeder;
use Database\Seeders\SubjectEnrollmentStatusTransitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $professor;
    private AcademicPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolSeeder::class,
            AcademicPeriodStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class,
            SubjectEnrollmentStatusTransitionSeeder::class,
            GradeStatusSeeder::class,
        ]);

        $this->admin = $this->userWithRole('admin');
        $this->professor = $this->userWithRole('professor', [
            'name' => 'API Professor',
            'email' => 'api.professor@example.test',
        ]);

        $this->professor->professor()->create([
            'document' => 'P-ENR-001',
            'phone' => '3000000000',
            'address' => 'Professor address',
            'city' => 'Bogota',
        ]);

        $this->period = AcademicPeriod::create([
            'name' => '2030-I',
            'start_date' => '2030-01-01',
            'end_date' => '2030-06-30',
            'enrollment_deadline' => '2030-01-30',
            'unenrollment_deadline' => '2030-02-15',
            'is_active' => true,
            'academic_period_status_id' => AcademicPeriodStatus::where('code', 'enrollment_open')->value('id'),
        ]);
    }

    public function test_student_can_view_available_groups_with_professor_comparison_data(): void
    {
        [$student, $subject, $group] = $this->academicFixture();

        $this->actingAs($student->user)
            ->getJson(route('api.subjects.available-groups', $subject))
            ->assertOk()
            ->assertJsonPath('data.0.id', $group->id)
            ->assertJsonPath('data.0.professor.name', 'API Professor')
            ->assertJsonPath('data.0.can_select', true)
            ->assertJsonPath('meta.student_id', $student->id);
    }

    public function test_student_can_enroll_in_class_group_through_rest_api(): void
    {
        [$student, $subject, $group] = $this->academicFixture();

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $group))
            ->assertCreated()
            ->assertJsonPath('type', 'enrolled')
            ->assertJsonPath('data.subject.name', $subject->name)
            ->assertJsonPath('data.class_group.id', $group->id);

        $this->assertDatabaseHas('subject_enrollments', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_group_id' => $group->id,
        ]);
    }

    public function test_admin_can_enroll_student_by_student_id(): void
    {
        [$student, $subject, $group] = $this->academicFixture();

        $this->actingAs($this->admin)
            ->postJson(route('api.class-groups.enrollments.store', $group), [
                'student_id' => $student->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.subject.id', $subject->id);
    }

    public function test_student_cannot_enroll_another_student_by_passing_student_id(): void
    {
        [$firstStudent, , $group] = $this->academicFixture();
        [$secondStudent] = $this->academicFixture();

        $this->actingAs($firstStudent->user)
            ->postJson(route('api.class-groups.enrollments.store', $group), [
                'student_id' => $secondStudent->id,
            ])
            ->assertForbidden();
    }

    public function test_enrollment_api_changes_group_when_student_selects_same_subject_group(): void
    {
        [$student, , $currentGroup] = $this->academicFixture();
        $newGroup = $this->classGroup($currentGroup->subject, ['group_code' => 'G2', 'code' => 'API-G2']);
        $this->schedule($newGroup, 'tuesday', '10:00', '11:00');

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $currentGroup))
            ->assertCreated();

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $newGroup))
            ->assertOk()
            ->assertJsonPath('type', 'group_changed')
            ->assertJsonPath('data.class_group.id', $newGroup->id);

        $this->assertSame($newGroup->id, SubjectEnrollment::first()->class_group_id);
    }

    public function test_unenroll_api_changes_status_without_deleting_history(): void
    {
        [$student, , $group] = $this->academicFixture();

        $response = $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $group))
            ->assertCreated();

        $enrollmentId = $response->json('data.id');

        $this->actingAs($student->user)
            ->deleteJson(route('api.enrollments.destroy', $enrollmentId))
            ->assertOk()
            ->assertJsonPath('data.status.code', 'cancelled');

        $this->assertDatabaseHas('subject_enrollments', [
            'id' => $enrollmentId,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_cannot_confirm_period_enrollment_below_minimum_credits(): void
    {
        [$student, , $group] = $this->academicFixture();

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $group))
            ->assertCreated();

        $this->actingAs($student->user)
            ->postJson(route('api.enrollments.confirm-period'))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'BLOCK_MIN_CREDITS');

        $this->assertDatabaseHas('subject_enrollments', [
            'student_id' => $student->id,
            'class_group_id' => $group->id,
            'status_id' => SubjectEnrollmentStatus::where('code', 'pre_enrolled')->value('id'),
        ]);
    }

    public function test_student_can_confirm_period_enrollment_when_minimum_credits_are_met(): void
    {
        [$student, , $firstGroup] = $this->academicFixture();
        [, $secondGroup] = $this->addSubjectGroupForStudent($student, 4, 'tuesday');

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $firstGroup))
            ->assertCreated();

        $this->actingAs($student->user)
            ->postJson(route('api.class-groups.enrollments.store', $secondGroup))
            ->assertCreated();

        $this->actingAs($student->user)
            ->postJson(route('api.enrollments.confirm-period'))
            ->assertOk()
            ->assertJsonPath('data.credits', 7)
            ->assertJsonPath('data.meets_minimum', true)
            ->assertJsonPath('data.confirmed_enrollments', 2);

        $this->assertDatabaseCount('subject_enrollments', 2);
        $this->assertDatabaseMissing('subject_enrollments', [
            'student_id' => $student->id,
            'status_id' => SubjectEnrollmentStatus::where('code', 'pre_enrolled')->value('id'),
        ]);
    }

    private function academicFixture(): array
    {
        $subject = Subject::create([
            'code' => fake()->unique()->bothify('API###'),
            'name' => fake()->words(3, true),
            'description' => 'API enrollment subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);

        $student = $this->studentForSubject($subject);
        $group = $this->classGroup($subject);
        $this->schedule($group);

        return [$student, $subject, $group];
    }

    private function studentForSubject(Subject $subject): Student
    {
        $program = Program::create([
            'name' => fake()->unique()->words(3, true),
            'description' => 'API test program',
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'code' => fake()->unique()->bothify('API-CUR-###'),
            'name' => 'API Curriculum',
            'valid_from' => now()->toDateString(),
            'is_active' => true,
        ]);

        $curriculum->subjects()->attach($subject->id, [
            'semester_recommended' => 1,
            'credits' => $subject->credits,
            'type' => 'required',
        ]);

        $user = $this->userWithRole('student');

        return Student::create([
            'user_id' => $user->id,
            'document' => fake()->unique()->numerify('########'),
            'phone' => '3000000000',
            'address' => 'Main street',
            'city' => 'Bogota',
            'semester' => 1,
            'program_id' => $program->id,
            'curriculum_id' => $curriculum->id,
            'academic_status' => Student::STATUS_ACTIVE,
        ]);
    }

    private function addSubjectGroupForStudent(Student $student, int $credits, string $day): array
    {
        $subject = Subject::create([
            'code' => fake()->unique()->bothify('API###'),
            'name' => fake()->words(3, true),
            'description' => 'Additional API enrollment subject',
            'credits' => $credits,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);

        $student->curriculum->subjects()->attach($subject->id, [
            'semester_recommended' => 1,
            'credits' => $subject->credits,
            'type' => 'required',
        ]);

        $group = $this->classGroup($subject);
        $this->schedule($group, $day);

        return [$subject, $group];
    }

    private function classGroup(Subject $subject, array $overrides = []): ClassGroup
    {
        return ClassGroup::create([
            'code' => fake()->unique()->bothify('API-GRP-###'),
            'name' => "{$subject->name} Group",
            'subject_id' => $subject->id,
            'professor_id' => $this->professor->id,
            'academic_period_id' => $this->period->id,
            'semester' => '2030-I',
            'group_code' => fake()->unique()->bothify('G#?'),
            'modality' => 'In-person',
            'shift' => 'Day',
            'capacity' => 30,
            'status' => ClassGroup::STATUS_PUBLISHED,
            ...$overrides,
        ]);
    }

    private function schedule(
        ClassGroup $group,
        string $day = 'monday',
        string $start = '09:00',
        string $end = '10:00'
    ): ClassSchedule {
        return ClassSchedule::create([
            'class_group_id' => $group->id,
            'day' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'status' => ClassSchedule::STATUS_PUBLISHED,
        ]);
    }

    private function userWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole($role);

        return $user;
    }
}

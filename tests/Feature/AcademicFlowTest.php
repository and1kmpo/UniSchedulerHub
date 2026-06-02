<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Grade;
use App\Models\Program;
use App\Models\Professor;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\GradeService;
use Database\Seeders\AcademicPeriodStatusSeeder;
use Database\Seeders\GradeStatusSeeder;
use Database\Seeders\SubjectEnrollmentStatusSeeder;
use Database\Seeders\SubjectEnrollmentStatusTransitionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $professor;
    private AcademicPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('professor');
        Role::findOrCreate('student');

        $this->seed([
            AcademicPeriodStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class,
            SubjectEnrollmentStatusTransitionSeeder::class,
            GradeStatusSeeder::class,
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->professor = User::factory()->create();
        $this->professor->assignRole('professor');
        Professor::create([
            'user_id' => $this->professor->id,
            'document' => fake()->unique()->numerify('P########'),
            'phone' => '3000000001',
            'address' => 'Professor street',
            'city' => 'Bogota',
        ]);

        $this->period = AcademicPeriod::create([
            'name' => '2026-I',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(4),
            'enrollment_deadline' => now()->addMonth(),
            'unenrollment_deadline' => now()->addMonths(2),
            'is_active' => true,
            'academic_period_status_id' => AcademicPeriodStatus::where('code', 'enrollment_open')->value('id'),
        ]);

        $this->actingAs($this->admin);
    }

    public function test_it_blocks_duplicate_enrollment_for_same_subject_and_period(): void
    {
        [$student, $subject, $group] = $this->academicFixture();

        app(EnrollmentService::class)->enroll($student, $group);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('BLOCK_ALREADY_ENROLLED');

        app(EnrollmentService::class)->enroll($student, $group);
    }

    public function test_it_blocks_enrollment_when_group_capacity_is_full(): void
    {
        [$firstStudent, $subject, $group] = $this->academicFixture([
            'capacity' => 1,
        ]);
        $secondStudent = $this->studentForSubject($subject);

        app(EnrollmentService::class)->enroll($firstStudent, $group);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('BLOCK_CAPACITY');

        app(EnrollmentService::class)->enroll($secondStudent, $group);
    }

    public function test_it_blocks_enrollment_when_schedule_conflicts(): void
    {
        [$student, $firstSubject, $firstGroup] = $this->academicFixture();
        $secondSubject = $this->subject();
        $student->curriculum->subjects()->attach($secondSubject->id, [
            'semester_recommended' => 1,
            'credits' => $secondSubject->credits,
            'type' => 'required',
        ]);

        $secondGroup = $this->classGroup($secondSubject);
        $this->schedule($secondGroup, 'monday', '09:30', '11:00');

        app(EnrollmentService::class)->enroll($student, $firstGroup);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('BLOCK_SCHEDULE_CONFLICT');

        app(EnrollmentService::class)->enroll($student, $secondGroup);
    }

    public function test_it_blocks_enrollment_when_group_is_not_published(): void
    {
        [$student, $subject, $group] = $this->academicFixture([
            'status' => ClassGroup::STATUS_DRAFT,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('BLOCK_GROUP_NOT_PUBLISHED');

        app(EnrollmentService::class)->enroll($student, $group);
    }

    public function test_unenroll_changes_status_without_deleting_history(): void
    {
        [$student, $subject, $group] = $this->academicFixture();

        $enrollment = app(EnrollmentService::class)->enroll($student, $group);

        app(EnrollmentService::class)->unenroll($enrollment);

        $enrollment->refresh()->load('status');

        $this->assertDatabaseHas('subject_enrollments', [
            'id' => $enrollment->id,
        ]);
        $this->assertSame('cancelled', $enrollment->status->code);
        $this->assertNotNull($enrollment->cancelled_at);
    }

    public function test_grades_can_be_registered_when_period_is_in_progress(): void
    {
        [$student, $subject, $group] = $this->academicFixture();
        $enrollment = app(EnrollmentService::class)->enroll($student, $group);
        $enrollment->update([
            'status_id' => SubjectEnrollmentStatus::where('code', 'enrolled')->value('id'),
        ]);
        $this->period->update([
            'academic_period_status_id' => AcademicPeriodStatus::where('code', 'in_progress')->value('id'),
        ]);

        $grade = app(GradeService::class)->update($enrollment->fresh(['academicPeriod', 'status', 'classGroup']), [
            'first_exam' => 4.0,
            'second_exam' => 4.0,
            'third_exam' => 3.5,
            'activities' => 4.5,
            'attendance' => 90,
        ], $this->professor->professor?->id);

        $this->assertInstanceOf(Grade::class, $grade);
        $this->assertSame(3.95, (float) $grade->final_grade);
        $this->assertSame('passed', $grade->state->code);
        $this->assertSame($this->admin->id, $grade->created_by);
        $this->assertSame($this->admin->id, $grade->updated_by);
    }

    public function test_grades_are_blocked_when_period_is_not_in_progress(): void
    {
        [$student, $subject, $group] = $this->academicFixture();
        $enrollment = app(EnrollmentService::class)->enroll($student, $group);
        $enrollment->update([
            'status_id' => SubjectEnrollmentStatus::where('code', 'enrolled')->value('id'),
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('BLOCK_PERIOD_DOES_NOT_ALLOW_GRADES');

        app(GradeService::class)->update($enrollment->fresh(['academicPeriod', 'status', 'classGroup']), [
            'first_exam' => 4.0,
            'second_exam' => 4.0,
            'third_exam' => 4.0,
            'activities' => 4.0,
            'attendance' => 90,
        ], $this->professor->professor?->id);
    }

    private function academicFixture(array $groupOverrides = []): array
    {
        $subject = $this->subject();
        $student = $this->studentForSubject($subject);
        $group = $this->classGroup($subject, $groupOverrides);
        $this->schedule($group);

        return [$student, $subject, $group];
    }

    private function studentForSubject(Subject $subject): Student
    {
        $program = Program::create([
            'name' => 'Software Engineering',
            'description' => 'Software Engineering Program',
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'code' => fake()->unique()->bothify('SE-2026-###'),
            'name' => 'Software Engineering 2026',
            'valid_from' => now()->toDateString(),
            'is_active' => true,
        ]);

        $curriculum->subjects()->attach($subject->id, [
            'semester_recommended' => 1,
            'credits' => $subject->credits,
            'type' => 'required',
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

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

    private function subject(): Subject
    {
        return Subject::create([
            'code' => fake()->unique()->bothify('SUB###'),
            'name' => fake()->words(3, true),
            'description' => 'Test subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);
    }

    private function classGroup(Subject $subject, array $overrides = []): ClassGroup
    {
        return ClassGroup::create([
            'code' => fake()->unique()->bothify('GRP###'),
            'name' => "{$subject->name} Group",
            'subject_id' => $subject->id,
            'professor_id' => $this->professor->id,
            'academic_period_id' => $this->period->id,
            'semester' => '2026-I',
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
}

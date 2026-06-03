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
use App\Models\User;
use App\Services\EnrollmentService;
use Database\Seeders\AcademicPeriodStatusSeeder;
use Database\Seeders\GradeStatusSeeder;
use Database\Seeders\SubjectEnrollmentStatusSeeder;
use Database\Seeders\SubjectEnrollmentStatusTransitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentSubjectEnrollmentGroupsTest extends TestCase
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

        $this->period = AcademicPeriod::create([
            'name' => '2026-I',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(4),
            'enrollment_deadline' => now()->addMonth(),
            'unenrollment_deadline' => now()->addMonths(2),
            'is_active' => true,
            'academic_period_status_id' => AcademicPeriodStatus::where('code', 'enrollment_open')->value('id'),
        ]);
    }

    public function test_groups_endpoint_marks_full_group_as_blocked(): void
    {
        $subject = $this->subject();
        $targetStudent = $this->studentForSubjects([$subject]);
        $enrolledStudent = $this->studentForSubjects([$subject]);
        $group = $this->classGroup($subject, ['capacity' => 1]);
        $this->schedule($group);

        $this->actingAs($this->admin);
        app(EnrollmentService::class)->enroll($enrolledStudent, $group);

        $this->actingAs($targetStudent->user)
            ->getJson(route('student.subject-enrollment.groups', $subject))
            ->assertOk()
            ->assertJsonPath('groups.0.id', $group->id)
            ->assertJsonPath('groups.0.canSelect', false)
            ->assertJsonPath('groups.0.validation.allowed', false)
            ->assertJsonPath('groups.0.validation.errors.0', 'This class group has reached maximum capacity.');
    }

    public function test_groups_endpoint_marks_schedule_conflict_as_blocked(): void
    {
        $firstSubject = $this->subject();
        $secondSubject = $this->subject();
        $student = $this->studentForSubjects([$firstSubject, $secondSubject]);

        $enrolledGroup = $this->classGroup($firstSubject);
        $this->schedule($enrolledGroup, 'monday', '09:00', '10:00');

        $conflictingGroup = $this->classGroup($secondSubject);
        $this->schedule($conflictingGroup, 'monday', '09:30', '11:00');

        $this->actingAs($this->admin);
        app(EnrollmentService::class)->enroll($student, $enrolledGroup);

        $this->actingAs($student->user)
            ->getJson(route('student.subject-enrollment.groups', $secondSubject))
            ->assertOk()
            ->assertJsonPath('groups.0.id', $conflictingGroup->id)
            ->assertJsonPath('groups.0.canSelect', false)
            ->assertJsonPath('groups.0.validation.allowed', false)
            ->assertJsonPath('groups.0.validation.conflicts.0.type', 'schedule_overlap');
    }

    public function test_groups_endpoint_allows_switching_to_another_group_for_same_subject(): void
    {
        $subject = $this->subject();
        $student = $this->studentForSubjects([$subject]);

        $currentGroup = $this->classGroup($subject, ['group_code' => 'G1']);
        $this->schedule($currentGroup, 'monday', '09:00', '10:00');

        $alternativeGroup = $this->classGroup($subject, ['group_code' => 'G2']);
        $this->schedule($alternativeGroup, 'tuesday', '09:00', '10:00');

        $this->actingAs($this->admin);
        app(EnrollmentService::class)->enroll($student, $currentGroup);

        $response = $this->actingAs($student->user)
            ->getJson(route('student.subject-enrollment.groups', $subject))
            ->assertOk();

        $groups = collect($response->json('groups'));
        $current = $groups->firstWhere('id', $currentGroup->id);
        $alternative = $groups->firstWhere('id', $alternativeGroup->id);

        $this->assertFalse($current['canSelect']);
        $this->assertTrue($current['isCurrent']);
        $this->assertTrue($alternative['canSelect']);
        $this->assertTrue($alternative['validation']['allowed']);
        $this->assertEmpty($alternative['validation']['errors']);
    }

    public function test_groups_endpoint_warns_when_projected_load_is_below_minimum_credits(): void
    {
        config(['enrollment.min_credits' => 7]);

        $subject = $this->subject([
            'credits' => 3,
        ]);
        $student = $this->studentForSubjects([$subject]);
        $group = $this->classGroup($subject);
        $this->schedule($group);

        $response = $this->actingAs($student->user)
            ->getJson(route('student.subject-enrollment.groups', $subject))
            ->assertOk()
            ->assertJsonPath('groups.0.validation.allowed', true)
            ->assertJsonPath('groups.0.validation.load.credits', 3)
            ->assertJsonPath('groups.0.validation.load.min_credits', 7)
            ->assertJsonPath('groups.0.validation.load.meets_minimum', false);

        $this->assertContains(
            'Academic load is below the minimum expected 7 credits.',
            $response->json('groups.0.validation.warnings')
        );
    }

    private function studentForSubjects(array $subjects): Student
    {
        $program = Program::create([
            'name' => fake()->unique()->words(3, true),
            'description' => 'Test program',
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'code' => fake()->unique()->bothify('CUR-###'),
            'name' => fake()->unique()->words(3, true),
            'valid_from' => now()->toDateString(),
            'is_active' => true,
        ]);

        foreach ($subjects as $subject) {
            $curriculum->subjects()->attach($subject->id, [
                'semester_recommended' => 1,
                'credits' => $subject->credits,
                'type' => 'required',
            ]);
        }

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

    private function subject(array $overrides = []): Subject
    {
        return Subject::create([
            'code' => fake()->unique()->bothify('SUB###'),
            'name' => fake()->unique()->words(3, true),
            'description' => 'Test subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
            ...$overrides,
        ]);
    }

    private function classGroup(Subject $subject, array $overrides = []): ClassGroup
    {
        return ClassGroup::create([
            'name' => "{$subject->name} Group",
            'subject_id' => $subject->id,
            'professor_id' => $this->professor->id,
            'academic_period_id' => $this->period->id,
            'semester' => '2026-I',
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

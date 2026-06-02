<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Professor;
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

class ProfessorPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $firstProfessor;
    private User $secondProfessor;
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

        $this->firstProfessor = $this->professorUser('P10000001');
        $this->secondProfessor = $this->professorUser('P10000002');

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

    public function test_professor_my_subjects_only_contains_assigned_groups(): void
    {
        [$ownGroup] = $this->groupWithEnrollment($this->firstProfessor, 'Algorithms');
        [$otherGroup] = $this->groupWithEnrollment($this->secondProfessor, 'Databases');

        $response = $this->actingAs($this->firstProfessor)
            ->get(route('professor.subjects'))
            ->assertOk();

        $groups = collect($response->viewData('page')['props']['groups']);

        $this->assertTrue($groups->contains('id', $ownGroup->id));
        $this->assertFalse($groups->contains('id', $otherGroup->id));
    }

    public function test_professor_group_enrollments_index_only_contains_assigned_groups(): void
    {
        [$ownGroup] = $this->groupWithEnrollment($this->firstProfessor, 'Calculus');
        [$otherGroup] = $this->groupWithEnrollment($this->secondProfessor, 'Physics');

        $response = $this->actingAs($this->firstProfessor)
            ->get(route('admin.group-enrollments.index'))
            ->assertOk();

        $groups = collect($response->viewData('page')['props']['classGroups']);

        $this->assertTrue($groups->contains('id', $ownGroup->id));
        $this->assertFalse($groups->contains('id', $otherGroup->id));
    }

    public function test_professor_cannot_open_other_professors_group_enrollments_by_url(): void
    {
        [$otherGroup] = $this->groupWithEnrollment($this->secondProfessor, 'Operating Systems');

        $this->actingAs($this->firstProfessor)
            ->get(route('admin.class-groups.enrollments', $otherGroup))
            ->assertForbidden();
    }

    public function test_professor_cannot_open_or_store_grades_for_other_professors_group_by_url(): void
    {
        [$otherGroup, $enrollment] = $this->groupWithEnrollment($this->secondProfessor, 'Software Testing');

        $this->period->update([
            'academic_period_status_id' => AcademicPeriodStatus::where('code', 'in_progress')->value('id'),
        ]);

        $this->actingAs($this->firstProfessor)
            ->get(route('groups.grades.index', $otherGroup))
            ->assertForbidden();

        $this->actingAs($this->firstProfessor)
            ->postJson(route('groups.grades.store', $otherGroup), [
                'grades' => [
                    $enrollment->id => [
                        'first_exam' => 4.0,
                        'second_exam' => 4.0,
                        'third_exam' => 4.0,
                        'activities' => 4.0,
                        'attendance' => 90,
                    ],
                ],
            ])
            ->assertForbidden();
    }

    private function professorUser(string $document): User
    {
        $user = User::factory()->create();
        $user->assignRole('professor');

        Professor::create([
            'user_id' => $user->id,
            'document' => $document,
            'phone' => '3000000001',
            'address' => 'Professor street',
            'city' => 'Bogota',
        ]);

        return $user;
    }

    private function groupWithEnrollment(User $professor, string $subjectName): array
    {
        $subject = Subject::create([
            'code' => fake()->unique()->bothify('SUB###'),
            'name' => $subjectName,
            'description' => 'Test subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);

        $group = ClassGroup::create([
            'name' => "{$subject->name} Group",
            'subject_id' => $subject->id,
            'professor_id' => $professor->id,
            'academic_period_id' => $this->period->id,
            'semester' => '2026-I',
            'modality' => 'In-person',
            'shift' => 'Day',
            'capacity' => 30,
            'status' => ClassGroup::STATUS_PUBLISHED,
        ]);

        ClassSchedule::create([
            'class_group_id' => $group->id,
            'day' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => ClassSchedule::STATUS_PUBLISHED,
        ]);

        $student = $this->studentForSubject($subject);

        $this->actingAs($this->admin);
        $enrollment = app(EnrollmentService::class)->enroll($student, $group);

        return [$group, $enrollment];
    }

    private function studentForSubject(Subject $subject): Student
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
}

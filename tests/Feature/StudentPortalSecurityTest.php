<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Grade;
use App\Models\GradeStatus;
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

class StudentPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $professorUser;
    private Professor $professor;
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

        $this->professorUser = User::factory()->create();
        $this->professorUser->assignRole('professor');
        $this->professor = Professor::create([
            'user_id' => $this->professorUser->id,
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
    }

    public function test_student_subjects_page_only_contains_authenticated_student_enrollments(): void
    {
        [$firstStudent, $firstSubject] = $this->studentWithEnrollment('Algorithms');
        [$secondStudent, $secondSubject] = $this->studentWithEnrollment('Databases');

        $response = $this->actingAs($firstStudent->user)
            ->get(route('student.subjects'))
            ->assertOk();

        $subjects = collect($response->viewData('page')['props']['subjects']);

        $this->assertTrue($subjects->contains('id', $firstSubject->id));
        $this->assertFalse($subjects->contains('id', $secondSubject->id));
        $this->assertNotSame($firstStudent->id, $secondStudent->id);
    }

    public function test_student_cannot_read_another_students_grade_json_by_subject_url(): void
    {
        [$firstStudent] = $this->studentWithEnrollment('Calculus');
        [$secondStudent, $secondSubject, $secondEnrollment] = $this->studentWithEnrollment('Physics');
        $this->grade($secondEnrollment, 4.7);

        $this->actingAs($firstStudent->user)
            ->getJson(route('student.subject.grades.json', $secondSubject))
            ->assertOk()
            ->assertJsonPath('grade', null);

        $this->assertNotSame($firstStudent->id, $secondStudent->id);
    }

    public function test_student_schedule_only_contains_authenticated_student_active_schedules(): void
    {
        [$firstStudent, $firstSubject] = $this->studentWithEnrollment('Computer Networks');
        [$secondStudent, $secondSubject] = $this->studentWithEnrollment('Artificial Intelligence');

        $response = $this->actingAs($firstStudent->user)
            ->get(route('student.schedule'))
            ->assertOk();

        $schedules = collect($response->viewData('page')['props']['currentSchedules']);

        $this->assertTrue($schedules->contains(fn($schedule) => $schedule['subject']['id'] === $firstSubject->id));
        $this->assertFalse($schedules->contains(fn($schedule) => $schedule['subject']['id'] === $secondSubject->id));
        $this->assertNotSame($firstStudent->id, $secondStudent->id);
    }

    public function test_student_grade_summary_only_contains_authenticated_student_grades(): void
    {
        [$firstStudent, $firstSubject, $firstEnrollment] = $this->studentWithEnrollment('Software Testing');
        [$secondStudent, $secondSubject, $secondEnrollment] = $this->studentWithEnrollment('Operating Systems');
        $this->grade($firstEnrollment, 4.2);
        $this->grade($secondEnrollment, 2.8);

        $response = $this->actingAs($firstStudent->user)
            ->getJson(route('student.grades.summary'))
            ->assertOk();

        $grades = collect($response->json('grades'));

        $this->assertTrue($grades->contains(fn($grade) => $grade['subject']['id'] === $firstSubject->id));
        $this->assertFalse($grades->contains(fn($grade) => $grade['subject']['id'] === $secondSubject->id));
        $this->assertNotSame($firstStudent->id, $secondStudent->id);
    }

    private function studentWithEnrollment(string $subjectName): array
    {
        $subject = $this->subject($subjectName);
        $student = $this->studentForSubjects([$subject]);
        $group = $this->classGroup($subject);
        $this->schedule($group);

        $this->actingAs($this->admin);
        $enrollment = app(EnrollmentService::class)->enroll($student, $group);

        return [$student, $subject, $enrollment];
    }

    private function grade($enrollment, float $finalGrade): Grade
    {
        return Grade::create([
            'subject_enrollment_id' => $enrollment->id,
            'professor_id' => $this->professor->id,
            'partial_1' => $finalGrade,
            'partial_2' => $finalGrade,
            'partial_3' => $finalGrade,
            'activities' => $finalGrade,
            'attendance' => 90,
            'final_grade' => $finalGrade,
            'grade_status_id' => GradeStatus::where('code', $finalGrade >= 3 ? 'passed' : 'failed')->value('id'),
        ]);
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

    private function subject(string $name): Subject
    {
        return Subject::create([
            'code' => fake()->unique()->bothify('SUB###'),
            'name' => $name,
            'description' => 'Test subject',
            'credits' => 3,
            'knowledge_area' => 'Engineering',
            'elective' => false,
        ]);
    }

    private function classGroup(Subject $subject): ClassGroup
    {
        return ClassGroup::create([
            'name' => "{$subject->name} Group",
            'subject_id' => $subject->id,
            'professor_id' => $this->professorUser->id,
            'academic_period_id' => $this->period->id,
            'semester' => '2026-I',
            'modality' => 'In-person',
            'shift' => 'Day',
            'capacity' => 30,
            'status' => ClassGroup::STATUS_PUBLISHED,
        ]);
    }

    private function schedule(ClassGroup $group): ClassSchedule
    {
        return ClassSchedule::create([
            'class_group_id' => $group->id,
            'day' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => ClassSchedule::STATUS_PUBLISHED,
        ]);
    }
}

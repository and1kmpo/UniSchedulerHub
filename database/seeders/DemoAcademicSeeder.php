<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Models\Building;
use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Curriculum;
use App\Models\Grade;
use App\Models\GradeStatus;
use App\Models\Professor;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectArea;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAcademicSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $statuses = $this->statuses();
            $periods = $this->periods($statuses);
            $campus = $this->campus();
            $subjects = $this->subjects();
            $program = $this->programWithCurriculum($subjects);
            $people = $this->people($program['program'], $program['curriculum']);

            $this->teachingAssignments($people['professors'], $subjects);
            $groups = $this->classGroups($periods, $campus, $people['professors'], $subjects);
            $this->enrollmentsAndGrades($people['students'], $people['professors'], $subjects, $groups, $periods);
        });
    }

    private function statuses(): array
    {
        return [
            'period' => AcademicPeriodStatus::query()->pluck('id', 'code'),
            'enrollment' => SubjectEnrollmentStatus::query()->pluck('id', 'code'),
            'grade' => GradeStatus::query()->pluck('id', 'code'),
        ];
    }

    private function periods(array $statuses): array
    {
        AcademicPeriod::query()->update(['is_active' => false]);

        $enrollment = AcademicPeriod::updateOrCreate(
            ['name' => '2026-II Enrollment Demo'],
            [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addMonths(5)->endOfMonth()->toDateString(),
                'enrollment_deadline' => now()->addWeeks(3)->toDateString(),
                'unenrollment_deadline' => now()->addWeeks(6)->toDateString(),
                'academic_period_status_id' => $statuses['period']['enrollment_open'],
                'is_active' => true,
            ]
        );

        $grading = AcademicPeriod::updateOrCreate(
            ['name' => '2026-I Grading Demo'],
            [
                'start_date' => now()->subMonths(5)->startOfMonth()->toDateString(),
                'end_date' => now()->addMonth()->endOfMonth()->toDateString(),
                'enrollment_deadline' => now()->subMonths(4)->toDateString(),
                'unenrollment_deadline' => now()->subMonths(3)->toDateString(),
                'academic_period_status_id' => $statuses['period']['in_progress'],
                'is_active' => false,
            ]
        );

        return compact('enrollment', 'grading');
    }

    private function campus(): array
    {
        $engineering = Building::updateOrCreate(
            ['code' => 'ENG'],
            ['name' => 'Engineering Building', 'description' => 'Main academic building for engineering programs.']
        );

        $science = Building::updateOrCreate(
            ['code' => 'SCI'],
            ['name' => 'Science Building', 'description' => 'Laboratories and classrooms for core sciences.']
        );

        $rooms = [
            'lab' => Classroom::updateOrCreate(
                ['name' => 'ENG-201 Lab'],
                [
                    'building_id' => $engineering->id,
                    'floor' => 2,
                    'capacity' => 30,
                    'description' => 'Computer lab with projector and workstations.',
                    'status' => 'active',
                ]
            ),
            'seminar' => Classroom::updateOrCreate(
                ['name' => 'ENG-105 Seminar'],
                [
                    'building_id' => $engineering->id,
                    'floor' => 1,
                    'capacity' => 1,
                    'description' => 'Small room used to demonstrate capacity validation.',
                    'status' => 'active',
                ]
            ),
            'math' => Classroom::updateOrCreate(
                ['name' => 'SCI-301'],
                [
                    'building_id' => $science->id,
                    'floor' => 3,
                    'capacity' => 40,
                    'description' => 'General purpose science classroom.',
                    'status' => 'active',
                ]
            ),
        ];

        return compact('engineering', 'science', 'rooms');
    }

    private function subjects(): array
    {
        $areas = [
            'software' => SubjectArea::updateOrCreate(['code' => 'SWE'], ['name' => 'Software Engineering']),
            'math' => SubjectArea::updateOrCreate(['code' => 'MTH'], ['name' => 'Mathematics']),
            'general' => SubjectArea::updateOrCreate(['code' => 'GEN'], ['name' => 'General Education']),
        ];

        $subjects = [
            'programming' => Subject::updateOrCreate(
                ['code' => 'SWE101'],
                [
                    'name' => 'Programming Fundamentals',
                    'description' => 'Introduction to programming logic and problem solving.',
                    'credits' => 3,
                    'knowledge_area' => 'Software Engineering',
                    'elective' => false,
                ]
            ),
            'calculus' => Subject::updateOrCreate(
                ['code' => 'MTH101'],
                [
                    'name' => 'Calculus I',
                    'description' => 'Differential calculus for engineering students.',
                    'credits' => 4,
                    'knowledge_area' => 'Mathematics',
                    'elective' => false,
                ]
            ),
            'dataStructures' => Subject::updateOrCreate(
                ['code' => 'SWE201'],
                [
                    'name' => 'Data Structures',
                    'description' => 'Linear and non-linear data structures.',
                    'credits' => 3,
                    'knowledge_area' => 'Software Engineering',
                    'elective' => false,
                ]
            ),
            'databases' => Subject::updateOrCreate(
                ['code' => 'SWE220'],
                [
                    'name' => 'Databases',
                    'description' => 'Relational database design and querying.',
                    'credits' => 3,
                    'knowledge_area' => 'Software Engineering',
                    'elective' => false,
                ]
            ),
            'ethics' => Subject::updateOrCreate(
                ['code' => 'GEN110'],
                [
                    'name' => 'Professional Ethics',
                    'description' => 'Ethical decision making in professional contexts.',
                    'credits' => 2,
                    'knowledge_area' => 'General Education',
                    'elective' => true,
                ]
            ),
        ];

        $subjects['dataStructures']->prerequisites()->syncWithoutDetaching([
            $subjects['programming']->id => ['logic' => 'ALL', 'min_grade' => 3.0],
        ]);

        return compact('areas', 'subjects');
    }

    private function programWithCurriculum(array $subjectData): array
    {
        $program = Program::updateOrCreate(
            ['name' => 'Software Engineering'],
            ['description' => 'Demo program for testing enrollment, scheduling and grading workflows.']
        );

        $curriculum = Curriculum::updateOrCreate(
            ['code' => 'SWE-2026'],
            [
                'program_id' => $program->id,
                'name' => 'Software Engineering 2026 Curriculum',
                'valid_from' => now()->startOfYear()->toDateString(),
                'valid_to' => null,
                'is_active' => true,
            ]
        );

        $subjects = $subjectData['subjects'];
        $areas = $subjectData['areas'];

        $curriculum->subjects()->sync([
            $subjects['programming']->id => [
                'semester_recommended' => 1,
                'credits' => 3,
                'type' => 'required',
                'area_id' => $areas['software']->id,
            ],
            $subjects['calculus']->id => [
                'semester_recommended' => 1,
                'credits' => 4,
                'type' => 'required',
                'area_id' => $areas['math']->id,
            ],
            $subjects['dataStructures']->id => [
                'semester_recommended' => 2,
                'credits' => 3,
                'type' => 'required',
                'area_id' => $areas['software']->id,
            ],
            $subjects['databases']->id => [
                'semester_recommended' => 3,
                'credits' => 3,
                'type' => 'required',
                'area_id' => $areas['software']->id,
            ],
            $subjects['ethics']->id => [
                'semester_recommended' => 2,
                'credits' => 2,
                'type' => 'elective',
                'area_id' => $areas['general']->id,
            ],
        ]);

        foreach ($subjects as $subject) {
            $program->subjects()->syncWithoutDetaching([
                $subject->id => ['semester' => $subject->pivot?->semester_recommended ?? 1],
            ]);
        }

        return compact('program', 'curriculum');
    }

    private function people(Program $program, Curriculum $curriculum): array
    {
        $professors = [
            'primary' => $this->professor('professor@unischedulerhub.test', 'Ada Lovelace', 'P1001'),
            'secondary' => $this->professor('professor.math@unischedulerhub.test', 'Katherine Johnson', 'P1002'),
        ];

        $students = [
            'open' => $this->student('student@unischedulerhub.test', 'Grace Hopper Student', 'S1001', $program, $curriculum, Student::STATUS_ACTIVE, 1),
            'enrolled' => $this->student('student.enrolled@unischedulerhub.test', 'Alan Turing Student', 'S1002', $program, $curriculum, Student::STATUS_ACTIVE, 1),
            'probation' => $this->student('student.probation@unischedulerhub.test', 'Barbara Liskov Student', 'S1003', $program, $curriculum, Student::STATUS_PROBATION, 2),
            'suspended' => $this->student('student.suspended@unischedulerhub.test', 'Suspended Demo Student', 'S1004', $program, $curriculum, Student::STATUS_SUSPENDED, 1),
            'graded' => $this->student('student.graded@unischedulerhub.test', 'Margaret Hamilton Student', 'S1005', $program, $curriculum, Student::STATUS_ACTIVE, 3),
        ];

        return compact('professors', 'students');
    }

    private function professor(string $email, string $name, string $document): Professor
    {
        $user = $this->user($email, $name, 'professor');

        return Professor::updateOrCreate(
            ['document' => $document],
            [
                'user_id' => $user->id,
                'phone' => '300000' . substr($document, -4),
                'address' => 'Demo campus office',
                'city' => 'Bogota',
            ]
        );
    }

    private function student(
        string $email,
        string $name,
        string $document,
        Program $program,
        Curriculum $curriculum,
        string $status,
        int $semester
    ): Student {
        $user = $this->user($email, $name, 'student');

        return Student::updateOrCreate(
            ['document' => $document],
            [
                'user_id' => $user->id,
                'phone' => '310000' . substr($document, -4),
                'address' => 'Demo student address',
                'city' => 'Bogota',
                'semester' => $semester,
                'program_id' => $program->id,
                'curriculum_id' => $curriculum->id,
                'academic_status' => $status,
            ]
        );
    }

    private function user(string $email, string $name, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($role);

        return $user;
    }

    private function teachingAssignments(array $professors, array $subjectData): void
    {
        $subjects = $subjectData['subjects'];

        $professors['primary']->subjects()->syncWithoutDetaching([
            $subjects['programming']->id,
            $subjects['dataStructures']->id,
            $subjects['databases']->id,
        ]);

        $professors['secondary']->subjects()->syncWithoutDetaching([
            $subjects['calculus']->id,
            $subjects['ethics']->id,
        ]);
    }

    private function classGroups(array $periods, array $campus, array $professors, array $subjectData): array
    {
        $subjects = $subjectData['subjects'];
        $rooms = $campus['rooms'];

        $groups = [
            'programmingA' => $this->group($subjects['programming'], $professors['primary'], $periods['enrollment'], 'A', 2),
            'programmingB' => $this->group($subjects['programming'], $professors['primary'], $periods['enrollment'], 'B', 30),
            'calculusA' => $this->group($subjects['calculus'], $professors['secondary'], $periods['enrollment'], 'A', 35),
            'dataStructuresA' => $this->group($subjects['dataStructures'], $professors['primary'], $periods['enrollment'], 'A', 25),
            'databasesA' => $this->group($subjects['databases'], $professors['primary'], $periods['enrollment'], 'A', 1),
            'ethicsA' => $this->group($subjects['ethics'], $professors['secondary'], $periods['enrollment'], 'A', 40),
            'gradingProgramming' => $this->group($subjects['programming'], $professors['primary'], $periods['grading'], 'G', 30),
        ];

        $this->schedule($groups['programmingA'], 'monday', '08:00', '10:00', $rooms['lab']);
        $this->schedule($groups['programmingB'], 'tuesday', '10:00', '12:00', $rooms['lab']);
        $this->schedule($groups['calculusA'], 'monday', '08:00', '10:00', $rooms['math']);
        $this->schedule($groups['dataStructuresA'], 'wednesday', '14:00', '16:00', $rooms['lab']);
        $this->schedule($groups['databasesA'], 'friday', '08:00', '10:00', $rooms['seminar']);
        $this->schedule($groups['ethicsA'], 'thursday', '16:00', '18:00', $rooms['math']);
        $this->schedule($groups['gradingProgramming'], 'monday', '10:00', '12:00', $rooms['lab']);

        return $groups;
    }

    private function group(Subject $subject, Professor $professor, AcademicPeriod $period, string $groupCode, int $capacity): ClassGroup
    {
        return ClassGroup::updateOrCreate(
            [
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'group_code' => $groupCode,
            ],
            [
                'code' => "{$subject->code}-{$period->name}-{$groupCode}",
                'name' => "{$subject->name} - Group {$groupCode}",
                'professor_id' => $professor->user_id,
                'semester' => $period->name,
                'modality' => 'In-person',
                'shift' => 'Day',
                'capacity' => $capacity,
                'status' => ClassGroup::STATUS_PUBLISHED,
            ]
        );
    }

    private function schedule(ClassGroup $group, string $day, string $start, string $end, Classroom $classroom): void
    {
        ClassSchedule::updateOrCreate(
            [
                'class_group_id' => $group->id,
                'day' => $day,
                'start_time' => $start,
            ],
            [
                'end_time' => $end,
                'classroom_id' => $classroom->id,
                'status' => ClassSchedule::STATUS_PUBLISHED,
            ]
        );
    }

    private function enrollmentsAndGrades(
        array $students,
        array $professors,
        array $subjectData,
        array $groups,
        array $periods
    ): void {
        $subjects = $subjectData['subjects'];
        $statuses = SubjectEnrollmentStatus::query()->pluck('id', 'code');

        $this->enrollment($students['enrolled'], $subjects['programming'], $groups['programmingA'], $periods['enrollment'], $statuses['pre_enrolled']);
        $this->enrollment($students['enrolled'], $subjects['dataStructures'], $groups['dataStructuresA'], $periods['enrollment'], $statuses['pre_enrolled']);
        $this->enrollment($students['enrolled'], $subjects['ethics'], $groups['ethicsA'], $periods['enrollment'], $statuses['pre_enrolled']);
        $this->enrollment($students['probation'], $subjects['databases'], $groups['databasesA'], $periods['enrollment'], $statuses['enrolled']);

        $gradedEnrollment = $this->enrollment($students['graded'], $subjects['programming'], $groups['gradingProgramming'], $periods['grading'], $statuses['enrolled']);

        Grade::updateOrCreate(
            ['subject_enrollment_id' => $gradedEnrollment->id],
            [
                'professor_id' => $professors['primary']->id,
                'partial_1' => 4.2,
                'partial_2' => 4.0,
                'partial_3' => 3.8,
                'activities' => 4.5,
                'attendance' => 92,
                'final_grade' => 4.08,
                'grade_status_id' => GradeStatus::where('code', 'passed')->value('id'),
                'created_by' => User::where('email', 'admin@unischedulerhub.test')->value('id'),
                'updated_by' => User::where('email', 'admin@unischedulerhub.test')->value('id'),
            ]
        );
    }

    private function enrollment(Student $student, Subject $subject, ClassGroup $group, AcademicPeriod $period, int $statusId): SubjectEnrollment
    {
        return SubjectEnrollment::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
            ],
            [
                'class_group_id' => $group->id,
                'status_id' => $statusId,
                'enrolled_at' => now(),
                'enrolled_by' => User::where('email', 'admin@unischedulerhub.test')->value('id'),
            ]
        );
    }
}

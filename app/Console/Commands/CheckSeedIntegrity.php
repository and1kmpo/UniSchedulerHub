<?php

namespace App\Console\Commands;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Professor;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\User;
use Illuminate\Console\Command;

class CheckSeedIntegrity extends Command
{
    protected $signature = 'check:seed-integrity';

    protected $description = 'Check academic demo seed integrity';

    public function handle(): int
    {
        $this->info('Checking academic demo seed integrity...');

        $usersWithRoles = User::whereHas('roles')->count();
        $usersWithoutRoles = User::doesntHave('roles')->count();
        $this->line('Users with roles: ' . $usersWithRoles . ' / ' . User::count());

        if ($usersWithoutRoles > 0) {
            $this->warn("{$usersWithoutRoles} users do not have a role.");
        }

        $this->checkDemoUser('admin');
        $this->checkDemoUser('academic_coordinator');

        $students = Student::count();
        $studentsWithUser = Student::has('user')->count();
        $studentsWithProgram = Student::whereNotNull('program_id')->count();
        $studentsWithCurriculum = Student::whereNotNull('curriculum_id')->count();
        $this->line("Students with user: {$studentsWithUser} / {$students}");
        $this->line("Students with program: {$studentsWithProgram} / {$students}");
        $this->line("Students with curriculum: {$studentsWithCurriculum} / {$students}");

        if ($studentsWithUser < $students) {
            $this->warn('There are students without a user.');
        }

        if ($studentsWithProgram < $students) {
            $this->warn('There are students without a program.');
        }

        if ($studentsWithCurriculum < $students) {
            $this->warn('There are students without a curriculum.');
        }

        $professors = Professor::count();
        $professorsWithUser = Professor::has('user')->count();
        $this->line("Professors with user: {$professorsWithUser} / {$professors}");

        if ($professorsWithUser < $professors) {
            $this->warn('There are professors without a user.');
        }

        $subjects = Subject::count();
        $subjectsWithPrograms = Subject::has('programs')->count();
        $subjectsWithProfessors = Subject::has('professors')->count();
        $subjectsWithClassGroups = Subject::has('classGroups')->count();
        $subjectsWithEnrollments = Subject::has('enrollments')->count();
        $this->line("Subjects with programs: {$subjectsWithPrograms} / {$subjects}");
        $this->line("Subjects with professors: {$subjectsWithProfessors} / {$subjects}");
        $this->line("Subjects with class groups: {$subjectsWithClassGroups} / {$subjects}");
        $this->line("Subjects with enrollments: {$subjectsWithEnrollments} / {$subjects}");

        if ($subjectsWithPrograms < $subjects) {
            $this->warn('There are subjects without programs.');
        }

        if ($subjectsWithProfessors < $subjects) {
            $this->warn('There are subjects without professors.');
        }

        if ($subjectsWithClassGroups < $subjects) {
            $this->warn('There are subjects without class groups.');
        }

        $programs = Program::count();
        $programsWithSubjects = Program::has('subjects')->count();
        $this->line("Programs with subjects: {$programsWithSubjects} / {$programs}");

        if ($programsWithSubjects < $programs) {
            $this->warn('There are programs without subjects.');
        }

        $groups = ClassGroup::count();
        $groupsWithProfessor = ClassGroup::whereNotNull('professor_id')->count();
        $groupsWithSubject = ClassGroup::whereNotNull('subject_id')->count();
        $groupsWithSchedules = ClassGroup::has('schedules')->count();
        $this->line("Class groups with professor: {$groupsWithProfessor} / {$groups}");
        $this->line("Class groups with subject: {$groupsWithSubject} / {$groups}");
        $this->line("Class groups with schedules: {$groupsWithSchedules} / {$groups}");

        if ($groupsWithProfessor < $groups) {
            $this->warn('There are class groups without a professor.');
        }

        if ($groupsWithSubject < $groups) {
            $this->warn('There are class groups without a subject.');
        }

        if ($groupsWithSchedules < $groups) {
            $this->warn('There are class groups without schedules.');
        }

        $schedules = ClassSchedule::count();
        $schedulesWithGroup = ClassSchedule::whereNotNull('class_group_id')->count();
        $this->line("Schedules with class group: {$schedulesWithGroup} / {$schedules}");

        if ($schedulesWithGroup < $schedules) {
            $this->warn('There are schedules without a class group.');
        }

        $enrollments = SubjectEnrollment::count();
        $validEnrollments = SubjectEnrollment::whereNotNull('student_id')
            ->whereNotNull('subject_id')
            ->whereNotNull('class_group_id')
            ->whereNotNull('academic_period_id')
            ->whereNotNull('status_id')
            ->count();

        $this->line("Valid enrollments: {$validEnrollments} / {$enrollments}");

        if ($validEnrollments < $enrollments) {
            $this->warn('There are enrollments with missing relations.');
        }

        $this->info('Seed integrity check complete.');

        return self::SUCCESS;
    }

    private function checkDemoUser(string $role): void
    {
        $user = User::role($role)->first();

        if (! $user) {
            $this->warn("Missing user with role \"{$role}\".");

            return;
        }

        $this->info(str($role)->replace('_', ' ')->title() . ": {$user->email}");
    }
}

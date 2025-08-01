<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Professor;
use App\Models\Subject;
use App\Models\Program;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\SubjectEnrollment;
use Spatie\Permission\Models\Role;

class CheckSeedIntegrity extends Command
{
    protected $signature = 'check:seed-integrity';
    protected $description = 'Verifica integridad entre entidades creadas por seeders';

    public function handle()
    {
        $this->info('🔍 Verificando integridad de relaciones...');

        // 🧑 Usuarios
        $usersWithRoles = User::whereHas('roles')->count();
        $usersWithoutRoles = User::doesntHave('roles')->count();
        $this->line("👤 Usuarios con rol: $usersWithRoles / " . User::count());
        if ($usersWithoutRoles > 0) {
            $this->warn("⚠️ $usersWithoutRoles usuarios no tienen rol asignado.");
        }

        // ✅ Admin
        $admin = User::role('admin')->first();
        if (!$admin) {
            $this->error('❌ No hay usuario con rol "admin".');
        } else {
            $this->info("✅ Admin: {$admin->email}");
        }

        // 👨‍🎓 Estudiantes
        $students = Student::count();
        $studentsWithUser = Student::has('user')->count();
        $studentsWithProgram = Student::whereNotNull('program_id')->count();
        $this->line("📘 Estudiantes con usuario: $studentsWithUser / $students");
        $this->line("🎓 Estudiantes con programa: $studentsWithProgram / $students");
        if ($studentsWithUser < $students) $this->warn('⚠️ Hay estudiantes sin usuario.');
        if ($studentsWithProgram < $students) $this->warn('⚠️ Hay estudiantes sin programa.');

        // 👨‍🏫 Profesores
        $professors = Professor::count();
        $profWithUser = Professor::has('user')->count();
        $this->line("🧑‍🏫 Profesores con usuario: $profWithUser / $professors");
        if ($profWithUser < $professors) $this->warn('⚠️ Hay profesores sin usuario.');

        // 📚 Materias
        $subjects = Subject::count();
        $subjectsWithPrograms = Subject::has('programs')->count();
        $subjectsWithProfs = Subject::has('professors')->count();
        $subjectsWithStudents = Subject::has('students')->count();
        $this->line("📖 Materias con programas: $subjectsWithPrograms / $subjects");
        $this->line("📖 Materias con profesores: $subjectsWithProfs / $subjects");
        $this->line("📖 Materias con estudiantes: $subjectsWithStudents / $subjects");
        if ($subjectsWithPrograms < $subjects) $this->warn('⚠️ Materias sin programas.');
        if ($subjectsWithProfs < $subjects) $this->warn('⚠️ Materias sin profesores.');
        if ($subjectsWithStudents < $subjects) $this->warn('⚠️ Materias sin estudiantes.');

        // 🏫 Programas
        $programs = Program::count();
        $programsWithSubjects = Program::has('subjects')->count();
        $this->line("🏫 Programas con materias: $programsWithSubjects / $programs");
        if ($programsWithSubjects < $programs) $this->warn('⚠️ Programas sin materias.');

        // 🧪 Grupos
        $groups = ClassGroup::count();
        $groupsWithProf = ClassGroup::whereNotNull('professor_id')->count();
        $groupsWithSubject = ClassGroup::whereNotNull('subject_id')->count();
        $groupsWithSchedules = ClassGroup::has('schedules')->count();
        $this->line("📘 Grupos con profesor: $groupsWithProf / $groups");
        $this->line("📘 Grupos con materia: $groupsWithSubject / $groups");
        $this->line("📘 Grupos con horarios: $groupsWithSchedules / $groups");
        if ($groupsWithProf < $groups) $this->warn('⚠️ Grupos sin profesor.');
        if ($groupsWithSubject < $groups) $this->warn('⚠️ Grupos sin materia.');
        if ($groupsWithSchedules < $groups) $this->warn('⚠️ Grupos sin horarios.');

        // 🕓 Horarios
        $schedules = ClassSchedule::count();
        $schedulesWithGroup = ClassSchedule::whereNotNull('class_group_id')->count();
        $this->line("⏰ Horarios con grupo asignado: $schedulesWithGroup / $schedules");
        if ($schedulesWithGroup < $schedules) $this->warn('⚠️ Horarios sin grupo asignado.');

        // ✅ Enrollments
        $enrollments = SubjectEnrollment::count();
        $validEnrollments = SubjectEnrollment::whereNotNull('student_id')
            ->whereNotNull('subject_id')
            ->whereNotNull('professor_id')
            ->whereNotNull('academic_period_id')
            ->count();
        $this->line("📑 Enrollments válidos: $validEnrollments / $enrollments");
        if ($validEnrollments < $enrollments) $this->warn("⚠️ Hay $enrollments enrollments con relaciones faltantes.");

        $this->info('✅ Verificación completa.');
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassScheduleController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\CurriculumSubjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GroupEnrollmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectEnrollmentController;
use App\Http\Controllers\UserController;

Route::get('/', fn() => redirect()->route('login'));
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    /**
     * ────────────── ADMIN & PROFESSOR ──────────────
     */
    Route::middleware(['auth', 'role:admin|professor'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/group-enrollments', [GroupEnrollmentController::class, 'index'])->name('admin.group-enrollments.index');
        Route::get('class-groups/{classGroup}/enrollments', [GroupEnrollmentController::class, 'show'])->name('admin.class-groups.enrollments');
    });

    /**
     * ────────────── ADMIN ──────────────
     */
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('programs', ProgramController::class);
        Route::resource('/users', UserController::class);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

        Route::resource('/roles', RoleController::class);
        Route::resource('/permissions', PermissionController::class);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions']);

        Route::get('/professors-assign-subject', [ProfessorController::class, 'assignSubjectForm'])->name('professors.assignSubjectForm');
        Route::post('/professors-assign-subject', [ProfessorController::class, 'assignSubjects'])->name('professors.assignSubjects');
        Route::delete('/unassign-subject-professor/{professorId}/{subjectId}', [ProfessorController::class, 'unassignSubject']);
        Route::post('/unassign-selected-subjects', [ProfessorController::class, 'unassignSelectedSubjects']);

        Route::get('/students-assign-subject', [StudentController::class, 'assignSubjectForm'])->name('students.assignSubjectForm');
        Route::post('/students-assign-subject', [StudentController::class, 'assignSubjects'])->name('students.assignSubjects');
        Route::delete('/unassign-subject-student/{studentId}/{subjectId}', [StudentController::class, 'unassignSubject']);

        Route::resource('/class-groups', ClassGroupController::class)->names('class-groups');
        Route::get('/class-groups/{classGroup}/can-enroll/{student}', [ClassGroupController::class, 'canEnroll'])->name('class-groups.can-enroll');
        Route::resource('class-groups.schedules', ClassScheduleController::class)->names('class-schedules');
        Route::get('/class-groups/{class_group}/calendar', [ClassScheduleController::class, 'calendar'])->name('class-schedules.calendar');
        Route::get('/class-groups/{classGroup}/schedules-json', [ClassScheduleController::class, 'schedulesJson'])->name('class-schedules.json');
        Route::post('/class-groups/{classGroup}/enroll', [GroupEnrollmentController::class, 'store'])->name('class-groups.enroll');
        Route::delete('/class-groups/{classGroup}/unenroll/{stduent}', [GroupEnrollmentController::class, 'destroy'])->name('class-groups.unenroll');
        Route::get('/class-groups/{id}', [ClassGroupController::class, 'show'])->name('class-groups.show');

        Route::patch('academic-periods/{id}/activate', [AcademicPeriodController::class, 'activate'])->name('academic-periods.activate');
        Route::resource('academic-periods', AcademicPeriodController::class)->except(['create', 'show', 'edit']);

        Route::resource('buildings', BuildingController::class);
        Route::post('/buildings/{id}/restore', [BuildingController::class, 'restore'])->name('buildings.restore');

        Route::get('/classrooms/preview', [ClassroomController::class, 'preview'])->name('classrooms.preview');
        Route::resource('classrooms', ClassroomController::class);

        Route::get('/classrooms/{classroom}/schedule', [ClassroomController::class, 'schedule'])->name('classrooms.schedule');
        Route::post('/curricula/{curriculum}/subjects', [CurriculumSubjectController::class, 'store']);
    });

    /**
     * ────────────── PROFESSOR ──────────────
     */
    Route::middleware(['role:professor'])->group(function () {
        Route::get('/professor/subjects', [ProfessorController::class, 'mySubjects'])->name('professor.subjects');
        Route::get('/subjects/{subject}/students', [ProfessorController::class, 'viewAllStudents'])->name('subjects.students.view');
        Route::get('/subjects/{subject}/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
    });

    /**
     * ────────────── STUDENT ──────────────
     */
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/subjects', [StudentController::class, 'mySubjects'])->name('student.subjects');
        Route::get('/student/{subject}/grades', [StudentController::class, 'viewGrades'])->name('student.subject.grades');
        Route::get('/student/{subject}/grades-json', [StudentController::class, 'getGradeJson'])->name('student.subject.grades.json');
        Route::get('/student/grades-summary', [StudentController::class, 'gradesSummary'])->name('student.grades.summary');

        Route::get('/student/subject-enrollment', [SubjectEnrollmentController::class, 'index'])->name('student.subject-enrollment.index');
        Route::post('/student/subject-enrollment/{subject}', [SubjectEnrollmentController::class, 'enroll'])->name('student.subject-enrollment.enroll');
        Route::post('/student/subject-unenrollment/{subject}', [SubjectEnrollmentController::class, 'unenroll'])->name('student.subject-enrollment.unenroll');
        Route::get('student/subject-enrollment/{subject}/groups', [SubjectEnrollmentController::class, 'groups'])->name('student.subject-enrollment.groups');
        Route::resource('curricula', CurriculumController::class);
    });

    /**
     * ────────────── GENERAL ──────────────
     */
    Route::resource('/subjects', SubjectController::class);
    Route::resource('/professors', ProfessorController::class);
    Route::resource('/students', StudentController::class);

    Route::get('/user-assignments', [UserController::class, 'getUserAssignments'])->name('user.assignments');
    Route::get('/subjects-with-professors', [SubjectController::class, 'getSubjectsWithProfessors']);
    Route::get('/professor-assigned-subjects/{professorId}', [ProfessorController::class, 'getAssignedSubjects']);
    Route::get('/student-assigned-subjects/{studentId}', [StudentController::class, 'getAssignedSubjects']);

    // ──────── Reports ────────
    Route::get('/assignments-report', [DashboardController::class, 'showAssignmentsReport'])->name('assignments.report');
    Route::get('/students-program-report', [DashboardController::class, 'totalStudentsPerProgram'])->name('studentsPrograms.report');
    Route::get('/elective-subjects-report', [DashboardController::class, 'percentageElectiveSubjects'])->name('electiveSubjects.report');
    Route::get('/students-semester-report', [DashboardController::class, 'studentsPerSemester'])->name('studentsSemester.report');
});

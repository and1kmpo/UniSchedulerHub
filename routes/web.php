<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
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
use App\Http\Controllers\Scheduling\SmartSchedulerController as SchedulingSmartSchedulerController;
use App\Http\Controllers\SmartSchedulerController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectEnrollmentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));
Route::get('/favicon.ico', fn() => redirect('/favicon.svg'));
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'academic_coordinator', 'professor'])) {
            return redirect()->route('dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('student.subjects');
        }

        return redirect('/');
    });

    /*
     * Shared staff workspace.
     * Admin and academic coordinators see institutional operations.
     * Professors see their teaching workspace and assigned groups.
     */
    Route::middleware(['role:admin|academic_coordinator|professor'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/group-enrollments', [GroupEnrollmentController::class, 'index'])->name('admin.group-enrollments.index');
        Route::get('class-groups/{classGroup}/enrollments', [GroupEnrollmentController::class, 'show'])->name('admin.class-groups.enrollments');
    });

    Route::middleware(['role:admin|academic_coordinator|professor'])->group(function () {
        Route::get('/groups/{group}/grades', [GradeController::class, 'index'])
            ->name('groups.grades.index')
            ->can('manageGrades', 'group');

        Route::post('/groups/{group}/grades', [GradeController::class, 'store'])
            ->name('groups.grades.store')
            ->can('manageGrades', 'group');

        Route::get('/class-groups/{classGroup}/grades', [GradeController::class, 'indexByGroup'])
            ->name('class-groups.grades')
            ->can('manageGrades', 'classGroup');

        Route::post('/class-groups/{classGroup}/grades', [GradeController::class, 'storeByGroup'])
            ->name('class-groups.grades.store')
            ->can('manageGrades', 'classGroup');
    });

    /*
     * Admin-only security administration.
     */
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('/users', UserController::class);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

        Route::resource('/roles', RoleController::class);
        Route::resource('/permissions', PermissionController::class);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
    });

    /*
     * Academic operations administration.
     */
    Route::middleware(['role:admin|academic_coordinator'])->group(function () {
        Route::resource('programs', ProgramController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('/professors', ProfessorController::class);
        Route::resource('/students', StudentController::class);
        Route::resource('curricula', CurriculumController::class);

        Route::resource('/class-groups', ClassGroupController::class)->names('class-groups');
        Route::get('/class-groups/{classGroup}/can-enroll/{student}', [ClassGroupController::class, 'canEnroll'])->name('class-groups.can-enroll');
        Route::resource('class-groups.schedules', ClassScheduleController::class)->names('class-schedules');
        Route::post('/class-groups/{classGroup}/enroll', [GroupEnrollmentController::class, 'store'])->name('class-groups.enroll');
        Route::post('/class-groups/{classGroup}/validate-enrollment', [GroupEnrollmentController::class, 'validateEnrollment'])->name('class-groups.validate-enrollment');
        Route::delete('/class-groups/{classGroup}/unenroll/{student}', [GroupEnrollmentController::class, 'destroy'])->name('class-groups.unenroll');

        Route::patch('academic-periods/{academicPeriod}/activate', [AcademicPeriodController::class, 'activate'])->name('academic-periods.activate');
        Route::post('/academic-periods/{academicPeriod}/open-enrollment', [AcademicPeriodController::class, 'openEnrollment'])->name('academic-periods.open-enrollment');
        Route::post('/academic-periods/{academicPeriod}/close-enrollment', [AcademicPeriodController::class, 'closeEnrollment'])->name('academic-periods.close-enrollment');
        Route::post('/academic-periods/{academicPeriod}/start', [AcademicPeriodController::class, 'start'])->name('academic-periods.start');
        Route::post('/academic-periods/{academicPeriod}/close', [AcademicPeriodController::class, 'close'])->name('academic-periods.close');
        Route::post('/academic-periods/{academicPeriod}/archive', [AcademicPeriodController::class, 'archive'])->name('academic-periods.archive');
        Route::resource('academic-periods', AcademicPeriodController::class)->except(['create', 'show', 'edit']);

        Route::resource('buildings', BuildingController::class);
        Route::post('/buildings/{id}/restore', [BuildingController::class, 'restore'])->name('buildings.restore');

        Route::get('/classrooms/preview', [ClassroomController::class, 'preview'])->name('classrooms.preview');
        Route::post('/classrooms/{id}/restore', [ClassroomController::class, 'restore'])->name('classrooms.restore');
        Route::resource('classrooms', ClassroomController::class);
        Route::get('/classrooms/{classroom}/schedule', [ClassroomController::class, 'schedule'])->name('classrooms.schedule');

        Route::post('/curricula/{curriculum}/subjects', [CurriculumSubjectController::class, 'store']);

        Route::prefix('smart-scheduler')->name('smart-scheduler.')->group(function () {
            Route::post('/generate', [SmartSchedulerController::class, 'generate'])->name('generate');
            Route::post('/optimize', [SchedulingSmartSchedulerController::class, 'optimize'])->name('optimize');
        });

        Route::get('/user-assignments', [UserController::class, 'getUserAssignments'])->name('user.assignments');
        Route::get('/subjects-with-professors', [SubjectController::class, 'getSubjectsWithProfessors']);

        Route::get('/assignments-report', [DashboardController::class, 'showAssignmentsReport'])->name('assignments.report');
        Route::get('/students-program-report', [DashboardController::class, 'totalStudentsPerProgram'])->name('studentsPrograms.report');
        Route::get('/elective-subjects-report', [DashboardController::class, 'percentageElectiveSubjects'])->name('electiveSubjects.report');
        Route::get('/students-semester-report', [DashboardController::class, 'studentsPerSemester'])->name('studentsSemester.report');
    });

    /*
     * Professor portal.
     */
    Route::middleware(['role:professor'])->group(function () {
        Route::get('/professor/subjects', [ProfessorController::class, 'mySubjects'])->name('professor.subjects');
        Route::get('/subjects/{subject}/students', [ProfessorController::class, 'viewAllStudents'])->name('subjects.students.view');
    });

    /*
     * Student portal.
     */
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/subjects', [StudentController::class, 'mySubjects'])->name('student.subjects');
        Route::get('/student/{subject}/grades', [StudentController::class, 'viewGrades'])->name('student.subject.grades');
        Route::get('/student/{subject}/grades-json', [StudentController::class, 'getGradeJson'])->name('student.subject.grades.json');
        Route::get('/student/grades-summary', [StudentController::class, 'gradesSummary'])->name('student.grades.summary');

        Route::prefix('student/subject-enrollment')->group(function () {
            Route::get('/', [SubjectEnrollmentController::class, 'index'])->name('student.subject-enrollment.index');
            Route::post('/groups/{classGroup}', [SubjectEnrollmentController::class, 'enroll'])->name('student.subject-enrollment.enroll');
            Route::delete('/{enrollment}', [SubjectEnrollmentController::class, 'unenroll'])->name('student.subject-enrollment.unenroll');
            Route::get('/subjects/{subject}/groups', [SubjectEnrollmentController::class, 'groups'])->name('student.subject-enrollment.groups');
        });
    });
});

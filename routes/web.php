<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\AcademicAuditLogController;
use App\Http\Controllers\AcademicReportController;
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
use App\Http\Controllers\Scheduling\SmartSchedulerController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectEnrollmentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));
Route::get('/favicon.ico', fn() => redirect('/favicon.svg'));
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
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
        Route::get('/academic-audit-logs', [AcademicAuditLogController::class, 'index'])->name('academic-audit-logs.index');
        Route::get('/reports', [AcademicReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/student-assignments', [AcademicReportController::class, 'studentAssignments'])->name('reports.student-assignments.index');
        Route::get('/reports/student-assignments/export', [AcademicReportController::class, 'exportStudentAssignments'])->name('reports.student-assignments.export');
        Route::get('/reports/professor-load', [AcademicReportController::class, 'professorLoad'])->name('reports.professor-load.index');
        Route::get('/reports/professor-load/export', [AcademicReportController::class, 'exportProfessorLoad'])->name('reports.professor-load.export');
        Route::get('/reports/classroom-occupancy', [AcademicReportController::class, 'classroomOccupancy'])->name('reports.classroom-occupancy.index');
        Route::get('/reports/classroom-occupancy/export', [AcademicReportController::class, 'exportClassroomOccupancy'])->name('reports.classroom-occupancy.export');
        Route::get('/reports/group-capacity-conflicts', [AcademicReportController::class, 'groupCapacityConflicts'])->name('reports.group-capacity-conflicts.index');
        Route::get('/reports/group-capacity-conflicts/export', [AcademicReportController::class, 'exportGroupCapacityConflicts'])->name('reports.group-capacity-conflicts.export');

        Route::resource('buildings', BuildingController::class);
        Route::post('/buildings/{id}/restore', [BuildingController::class, 'restore'])->name('buildings.restore');

        Route::get('/classrooms/preview', [ClassroomController::class, 'preview'])->name('classrooms.preview');
        Route::post('/classrooms/{id}/restore', [ClassroomController::class, 'restore'])->name('classrooms.restore');
        Route::resource('classrooms', ClassroomController::class);
        Route::get('/classrooms/{classroom}/schedule', [ClassroomController::class, 'schedule'])->name('classrooms.schedule');

        Route::post('/curricula/{curriculum}/subjects', [CurriculumSubjectController::class, 'store']);

        Route::prefix('smart-scheduler')->name('smart-scheduler.')->group(function () {
            Route::post('/generate', [SmartSchedulerController::class, 'generate'])->name('generate');
            Route::post('/optimize', [SmartSchedulerController::class, 'optimize'])->name('optimize');
        });

        Route::get('/user-assignments', [UserController::class, 'getUserAssignments'])->name('user.assignments');
    });

    /*
     * Professor portal.
     */
    Route::middleware(['role:professor'])->group(function () {
        Route::get('/professor/subjects', [ProfessorController::class, 'mySubjects'])->name('professor.subjects');
        Route::get('/professor/schedule', [ProfessorController::class, 'schedule'])->name('professor.schedule');
        Route::get('/subjects/{subject}/students', [ProfessorController::class, 'viewAllStudents'])->name('subjects.students.view');
    });

    /*
     * Student portal.
     */
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/subjects', [StudentController::class, 'mySubjects'])->name('student.subjects');
        Route::get('/student/schedule', [StudentController::class, 'schedule'])->name('student.schedule');
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

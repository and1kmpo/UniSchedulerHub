<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleAndPermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    /**
     * ────────────── ADMIN & PROFESSOR ──────────────
     */
    Route::middleware(['role:admin|professor'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

    // Programs (Implementación con permisos y roles)
    Route::middleware(['permission:manage programs'])->group(function () {
        Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');
    });

    /**
     * ────────────── ADMIN ──────────────
     */
    Route::middleware(['role:admin'])->group(function () {
        // CRUD Programs
        Route::get('/programs/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
        Route::get('/programs/{program}/edit', [ProgramController::class, 'edit'])->name('programs.edit');
        Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

        //Users, roles, permissions
        Route::resource('/users', UserController::class);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

        Route::resource('/roles', RoleController::class);
        Route::resource('/permissions', PermissionController::class);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions']);

        //Assign subjects to Professors
        Route::get('/professors-assign-subject', [ProfessorController::class, 'assignSubjectForm'])->name('professors.assignSubjectForm');
        Route::post('/professors-assign-subject', [ProfessorController::class, 'assignSubjects'])->name('professors.assignSubjects');
        Route::delete('/unassign-subject-professor/{professorId}/{subjectId}', [ProfessorController::class, 'unassignSubject']);
        Route::post('/unassign-selected-subjects', [ProfessorController::class, 'unassignSelectedSubjects']);

        //Assign subject to Students
        Route::get('/students-assign-subject', [StudentController::class, 'assignSubjectForm'])->name('students.assignSubjectForm');
        Route::post('/students-assign-subject', [StudentController::class, 'assignSubjects'])->name('students.assignSubjects');
        Route::delete('/unassign-subject-student/{studentId}/{subjectId}', [StudentController::class, 'unassignSubject']);
    });

    /**
     * ────────────── PROFESSOR ──────────────
     */
    Route::middleware(['role:professor'])->group(function () {
        Route::get('/professor/subjects', [ProfessorController::class, 'mySubjects'])->name('professor.subjects');
        Route::get('/subjects/{subject}/students', [ProfessorController::class, 'viewAllStudents'])->name('subjects.students.view');
        //Grades
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
    });



    // Resto de las rutas
    Route::resource('/subjects', SubjectController::class);
    Route::resource('/professors', ProfessorController::class);
    Route::resource('/students', StudentController::class);
    Route::get('/user-assignments', [UserController::class, 'getUserAssignments'])->middleware(['auth:sanctum'])->name('user.assignments');


    Route::get('/subjects-with-professors', [SubjectController::class, 'getSubjectsWithProfessors']);
    Route::get('/professor-assigned-subjects/{professorId}', [ProfessorController::class, 'getAssignedSubjects']);

    Route::get('/student-assigned-subjects/{studentId}', [StudentController::class, 'getAssignedSubjects']);

    // Reports
    Route::get('/assignments-report', [DashboardController::class, 'showAssignmentsReport'])->name('assignments.report');
    Route::get('/students-program-report', [DashboardController::class, 'totalStudentsPerProgram'])->name('studentsPrograms.report');
    Route::get('/elective-subjects-report', [DashboardController::class, 'percentageElectiveSubjects'])->name('electiveSubjects.report');
    Route::get('/students-semester-report', [DashboardController::class, 'studentsPerSemester'])->name('studentsSemester.report');
});

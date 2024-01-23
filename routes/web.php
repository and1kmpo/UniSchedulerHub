<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProfessorSubjectController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;

Route::get('/', [DashboardController::class, 'index']);

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Auth required routes
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::resource('/programs', ProgramController::class);
    Route::resource('/subjects', SubjectController::class);
    Route::resource('/professors', ProfessorController::class);
    Route::resource('/students', StudentController::class);
    Route::resource('/roles', RoleController::class);

    Route::get('/subjects-with-professors', [SubjectController::class, 'getSubjectsWithProfessors']);

    Route::get('/professor-assigned-subjects/{professorId}', [ProfessorController::class, 'getAssignedSubjects']);
    Route::get('/professors-assign-subject', [ProfessorController::class, 'assignSubjectForm'])->name('professors.assignSubjectForm');
    Route::post('/professors-assign-subject', [ProfessorController::class, 'assignSubjects'])->name('professors.assignSubjects');
    Route::delete('/unassign-subject-professor/{professorId}/{subjectId}', [ProfessorController::class, 'unassignSubject']);

    Route::get('/student-assigned-subjects/{studentId}', [StudentController::class, 'getAssignedSubjects']);
    Route::get('/students-assign-subject', [StudentController::class, 'assignSubjectForm'])->name('students.assignSubjectForm');
    Route::post('/students-assign-subject', [StudentController::class, 'assignSubjects'])->name('students.assignSubjects');
    Route::delete('/unassign-subject-student/{studentId}/{subjectId}', [StudentController::class, 'unassignSubject']);

    Route::get('/assignments-report', [DashboardController::class, 'showAssignmentsReport'])->name('assignments.report');
});

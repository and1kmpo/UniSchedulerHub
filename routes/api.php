<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnrollmentApiController;
use App\Http\Controllers\Api\ProfessorApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\StudentAssignmentReportController;
use App\Http\Controllers\Api\SubjectApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:admin|academic_coordinator'])->group(function () {
    Route::apiResource('students', StudentApiController::class);
    Route::apiResource('professors', ProfessorApiController::class);
    Route::apiResource('subjects', SubjectApiController::class);

    Route::get('reports/student-assignments', [StudentAssignmentReportController::class, 'index'])
        ->name('api.reports.student-assignments.index');
});

Route::middleware(['auth:sanctum', 'role:admin|academic_coordinator|student'])->group(function () {
    Route::get('enrollments', [EnrollmentApiController::class, 'index'])
        ->name('api.enrollments.index');
    Route::get('subjects/{subject}/available-groups', [EnrollmentApiController::class, 'availableGroups'])
        ->name('api.subjects.available-groups');
    Route::post('class-groups/{classGroup}/enrollments', [EnrollmentApiController::class, 'store'])
        ->name('api.class-groups.enrollments.store');
    Route::patch('enrollments/{enrollment}/change-group', [EnrollmentApiController::class, 'changeGroup'])
        ->name('api.enrollments.change-group');
    Route::post('enrollments/confirm-period', [EnrollmentApiController::class, 'confirmPeriod'])
        ->name('api.enrollments.confirm-period');
    Route::delete('enrollments/{enrollment}', [EnrollmentApiController::class, 'destroy'])
        ->name('api.enrollments.destroy');
});

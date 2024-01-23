<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function showAssignmentsReport()
    {
        $assignments = Student::leftJoin('student_subject_professor', 'students.id', '=', 'student_subject_professor.student_id')
            ->leftJoin('subjects', 'student_subject_professor.subject_id', '=', 'subjects.id')
            ->leftJoin('professors', 'student_subject_professor.professor_id', '=', 'professors.id')
            ->select('students.id as student_id', 'students.first_name', 'students.last_name', 'professors.first_name as professor_first_name', 'professors.last_name as professor_last_name', 'subjects.name as subject_name')
            ->get();

        // Agrupar los resultados por el ID del estudiante
        $groupedAssignments = $assignments->groupBy('student_id')->map(function ($assignments, $studentId) {
            $studentInfo = $assignments->first(); // Tomar la información del estudiante de la primera asignatura

            // Crear un nuevo objeto con la información del estudiante y las asignaturas
            return [
                'student_id' => $studentId,
                'first_name' => $studentInfo->first_name,
                'last_name' => $studentInfo->last_name,
                'subjects' => $assignments->map(function ($assignment) {
                    return [
                        'subject_name' => $assignment->subject_name,
                        'professor_first_name' => $assignment->professor_first_name,
                        'professor_last_name' => $assignment->professor_last_name,
                    ];
                })->all(),
            ];
        })->values()->all();

        return response()->json($groupedAssignments);
    }

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }
}

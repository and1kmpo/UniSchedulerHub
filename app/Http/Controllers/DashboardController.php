<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
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

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }

    public function showAssignmentsReport()
    {
        $assignments = Student::leftJoin('student_subject_professor', 'students.id', '=', 'student_subject_professor.student_id')
            ->leftJoin('subjects', 'student_subject_professor.subject_id', '=', 'subjects.id')
            ->leftJoin('professors', 'student_subject_professor.professor_id', '=', 'professors.id')
            ->select('students.id as student_id', 'students.first_name', 'students.last_name', 'students.document', 'professors.first_name as professor_first_name', 'professors.last_name as professor_last_name', 'subjects.name as subject_name')
            ->get();

        // Agrupar los resultados por el ID del estudiante
        $groupedAssignments = $assignments->groupBy('student_id')->map(function ($assignments, $studentId) {
            $studentInfo = $assignments->first(); // Tomar la información del estudiante de la primera asignatura

            // Crear un nuevo objeto con la información del estudiante y las asignaturas
            return [
                'student_id' => $studentId,
                'first_name' => $studentInfo->first_name,
                'last_name' => $studentInfo->last_name,
                'document' => $studentInfo->document,
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

    public function totalStudentsPerProgram()
    {
        $studentsPerProgram = Program::select('programs.id', 'programs.name', DB::raw('COUNT(students.id) as student_count'))
            ->leftJoin('students', 'programs.id', '=', 'students.program_id')
            ->groupBy('programs.id', 'programs.name')
            ->get();

        return response()->json($studentsPerProgram);
    }

    public function percentageElectiveSubjects()
    {
        $result = DB::table('students as st')
            ->join('student_subject_professor as sp', 'st.id', '=', 'sp.student_id')
            ->join('subjects as s', 'sp.subject_id', '=', 's.id')
            ->whereIn('st.id', function ($query) {
                $query->select('student_id')
                    ->from('student_subject_professor')
                    ->distinct();
            })
            ->selectRaw('
            ROUND((SUM(IF(s.elective = 1, 1, 0)) / NULLIF(COUNT(*), 0)) * 100, 4) AS percentageElective,
            ROUND((SUM(IF(s.elective = 0, 1, 0)) / NULLIF(COUNT(*), 0)) * 100, 4) AS percentageMandatory
        ')
            ->get();

        // Obtén los valores del primer resultado del conjunto de resultados
        $percentageElective = $result[0]->percentageElective;
        $percentageMandatory = $result[0]->percentageMandatory;

        // Devuelve los valores como un array asociativo
        return response()->json([
            'percentageElective' => (float)$percentageElective,
            'percentageMandatory' => (float)$percentageMandatory,
        ]);
    }

    public function studentsPerSemester()
    {
        $studentsPerSemester = Student::select('semester', DB::raw('COUNT(*) as student_count'))
            ->groupBy('semester')
            ->orderBy('semester', 'asc')
            ->get();
        return response()->json($studentsPerSemester);
    }
}

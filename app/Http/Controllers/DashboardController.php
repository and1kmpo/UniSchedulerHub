<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

    public function showAssignmentsReport(Request $request)
    {
        $query = Student::leftJoin('student_subject_professor', 'students.id', '=', 'student_subject_professor.student_id')
            ->leftJoin('subjects', 'student_subject_professor.subject_id', '=', 'subjects.id')
            ->leftJoin('professors', 'student_subject_professor.professor_id', '=', 'professors.id')
            ->leftJoin('users as student_users', 'students.user_id', '=', 'student_users.id')
            ->leftJoin('users as professor_users', 'professors.user_id', '=', 'professor_users.id')
            ->select(
                'students.id as student_id',
                'student_users.name as student_name',
                'students.document',
                'professor_users.name as professor_name',
                'subjects.name as subject_name'
            );

        // Filtrar por búsqueda si se proporciona
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('student_users.name', 'LIKE', "%$search%")
                    ->orWhere('students.document', 'LIKE', "%$search%")
                    ->orWhere('subjects.name', 'LIKE', "%$search%")
                    ->orWhere('professor_users.name', 'LIKE', "%$search%");
            });
        }

        // Obtener los resultados agrupados
        $assignments = $query->get()->groupBy('student_id')->map(function ($assignments, $studentId) {
            $studentInfo = $assignments->first();

            return [
                'student_id' => $studentId,
                'nameStudent' => $studentInfo->student_name,
                'document' => $studentInfo->document,
                'subjects' => $assignments->map(function ($assignment) {
                    return [
                        'subjectName' => $assignment->subject_name,
                        'professorName' => $assignment->professor_name ?? 'No professor assigned',
                    ];
                })->all(),
            ];
        })->values();

        // Paginar resultados manualmente
        $perPage = 4;
        $currentPage = $request->input('page', 1);
        $total = $assignments->count();
        $paginatedData = $assignments->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paginatedData,
            'current_page' => $currentPage,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
            'prev_page_url' => $currentPage > 1 ? url()->current() . '?page=' . ($currentPage - 1) : null,
            'next_page_url' => $currentPage < ceil($total / $perPage) ? url()->current() . '?page=' . ($currentPage + 1) : null,
        ]);
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

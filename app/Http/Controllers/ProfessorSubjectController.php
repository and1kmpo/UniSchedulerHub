<?php

namespace App\Http\Controllers;

use App\Models\Professor;

class ProfessorSubjectController extends Controller
{
    /**
     * Obtener las asignaturas asignadas a un profesor.
     *
     * @param int $professorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function professorAssignedSubjects($professorId)
    {
        $professor = Professor::find($professorId);
        $assignedSubjects = $professor ? $professor->subjects : [];

        return response()->json($assignedSubjects);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAssignmentReportResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentAssignmentReportController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query()
            ->with([
                'user',
                'program',
                'enrollments' => fn($query) => $query
                    ->with([
                        'status',
                        'academicPeriod',
                        'subject',
                        'classGroup.professor',
                    ])
                    ->when($request->filled('academic_period_id'), fn($query) => $query
                        ->where('academic_period_id', $request->integer('academic_period_id')))
                    ->latest(),
            ])
            ->whereHas('enrollments', fn($query) => $query
                ->when($request->filled('academic_period_id'), fn($query) => $query
                    ->where('academic_period_id', $request->integer('academic_period_id'))))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('document', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($user) => $user
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->paginate(min((int) $request->input('per_page', 15), 100))
            ->withQueryString();

        return StudentAssignmentReportResource::collection($students);
    }
}

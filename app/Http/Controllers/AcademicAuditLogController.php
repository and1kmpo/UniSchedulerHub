<?php

namespace App\Http\Controllers;

use App\Filters\AcademicAuditLogFilter;
use App\Models\AcademicAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AcademicAuditLogController extends Controller
{
    public function index(Request $request, AcademicAuditLogFilter $filters)
    {
        $query = AcademicAuditLog::query()
            ->with('user:id,name,email');

        $actions = (clone $query)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn($action) => [
                'value' => $action,
                'label' => Str::headline(str_replace('.', ' ', $action)),
            ])
            ->values();

        $auditableTypes = (clone $query)
            ->whereNotNull('auditable_type')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->values();

        $users = AcademicAuditLog::query()
            ->with('user:id,name,email')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->sortBy('name')
            ->map(fn($user) => [
                'value' => $user->id,
                'label' => "{$user->name} ({$user->email})",
            ])
            ->values();

        $stats = [
            'total' => AcademicAuditLog::count(),
            'today' => AcademicAuditLog::whereDate('created_at', now()->toDateString())->count(),
            'grade_events' => AcademicAuditLog::where('action', 'like', 'grade.%')->count(),
            'enrollment_events' => AcademicAuditLog::where('action', 'like', 'enrollment.%')->count(),
        ];

        $logs = $filters
            ->apply($query)
            ->paginate(15)
            ->withQueryString()
            ->through(fn(AcademicAuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'action_label' => Str::headline(str_replace('.', ' ', $log->action)),
                'summary' => $log->summary,
                'entity' => $log->auditable_type ? class_basename($log->auditable_type) : 'System',
                'entity_id' => $log->auditable_id,
                'auditable_type' => $log->auditable_type,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'metadata' => $log->metadata ?? [],
                'created_at' => $log->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/AcademicAuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only([
                'search',
                'action',
                'user_id',
                'auditable_type',
                'date_from',
                'date_to',
                'sort',
                'direction',
            ]),
            'filterOptions' => [
                'actions' => $actions,
                'users' => $users,
                'auditableTypes' => $auditableTypes,
            ],
            'stats' => $stats,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AcademicRequest;
use App\Models\Student;
use App\Services\AcademicAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AcademicRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isReviewer = $user->hasAnyRole(['admin', 'academic_coordinator']);

        $query = AcademicRequest::query()
            ->with([
                'student.user:id,name,email',
                'student.program:id,name',
                'creator:id,name,email',
                'reviewer:id,name,email',
                'enrollment.subject:id,code,name',
                'classGroup.subject:id,code,name',
            ])
            ->latest();

        if (! $isReviewer) {
            $query->where('student_id', $user->student?->id);
        }

        $requests = $query
            ->paginate(12)
            ->withQueryString()
            ->through(fn(AcademicRequest $academicRequest) => $this->payload($academicRequest));

        return Inertia::render('AcademicRequests/Index', [
            'requests' => $requests,
            'canReview' => $isReviewer,
            'typeOptions' => $this->options(AcademicRequest::TYPES),
            'statusOptions' => $this->options(AcademicRequest::STATUSES),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasRole('student'), 403);

        return Inertia::render('AcademicRequests/Create', [
            'typeOptions' => $this->options(AcademicRequest::TYPES),
        ]);
    }

    public function store(Request $request, AcademicAuditService $audit)
    {
        abort_unless($request->user()->hasRole('student'), 403);

        $student = $request->user()->student;
        abort_unless($student, 403);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(AcademicRequest::TYPES))],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $academicRequest = AcademicRequest::create([
            ...$validated,
            'student_id' => $student->id,
            'created_by' => $request->user()->id,
            'status' => AcademicRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $audit->record(
            'academic_request.submitted',
            $academicRequest,
            [
                'student_id' => $student->id,
                'type' => $academicRequest->type,
                'status' => $academicRequest->status,
            ],
            'Academic request submitted'
        );

        return redirect()
            ->route('academic-requests.index')
            ->with('success', __('ui.academic_requests.submitted_success'));
    }

    public function approve(Request $request, AcademicRequest $academicRequest, AcademicAuditService $audit)
    {
        return $this->review($request, $academicRequest, $audit, AcademicRequest::STATUS_APPROVED);
    }

    public function reject(Request $request, AcademicRequest $academicRequest, AcademicAuditService $audit)
    {
        return $this->review($request, $academicRequest, $audit, AcademicRequest::STATUS_REJECTED);
    }

    private function review(
        Request $request,
        AcademicRequest $academicRequest,
        AcademicAuditService $audit,
        string $status
    ) {
        abort_unless($request->user()->hasAnyRole(['admin', 'academic_coordinator']), 403);

        abort_if(
            in_array($academicRequest->status, [AcademicRequest::STATUS_APPROVED, AcademicRequest::STATUS_REJECTED], true),
            422,
            'This academic request has already been resolved.'
        );

        $validated = $request->validate([
            'decision_reason' => ['required', 'string', 'min:10', 'max:1200'],
        ]);

        $previousStatus = $academicRequest->status;

        $academicRequest->update([
            'status' => $status,
            'decision_reason' => $validated['decision_reason'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $audit->record(
            "academic_request.{$status}",
            $academicRequest,
            [
                'student_id' => $academicRequest->student_id,
                'type' => $academicRequest->type,
                'before' => ['status' => $previousStatus],
                'after' => ['status' => $status],
            ],
            'Academic request reviewed'
        );

        return redirect()
            ->route('academic-requests.index')
            ->with('success', __('ui.academic_requests.decision_success'));
    }

    private function payload(AcademicRequest $academicRequest): array
    {
        return [
            'id' => $academicRequest->id,
            'type' => $academicRequest->type,
            'type_label' => __("ui.academic_requests.types.{$academicRequest->type}"),
            'status' => $academicRequest->status,
            'status_label' => __("ui.academic_requests.statuses.{$academicRequest->status}"),
            'title' => $academicRequest->title,
            'description' => $academicRequest->description,
            'decision_reason' => $academicRequest->decision_reason,
            'submitted_at' => $academicRequest->submitted_at?->toISOString(),
            'reviewed_at' => $academicRequest->reviewed_at?->toISOString(),
            'student' => [
                'id' => $academicRequest->student?->id,
                'name' => $academicRequest->student?->user?->name,
                'document' => $academicRequest->student?->document,
                'program' => $academicRequest->student?->program?->name,
            ],
            'reviewer' => $academicRequest->reviewer ? [
                'id' => $academicRequest->reviewer->id,
                'name' => $academicRequest->reviewer->name,
            ] : null,
        ];
    }

    private function options(array $items): array
    {
        return collect($items)
            ->map(fn($label, $value) => [
                'value' => $value,
                'label' => __("ui.academic_requests.types.{$value}"),
            ])
            ->values()
            ->all();
    }
}

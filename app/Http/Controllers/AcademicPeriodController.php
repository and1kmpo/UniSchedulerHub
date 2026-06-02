<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\AcademicPeriodStatus;
use App\Services\AcademicPeriodService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        $periods = AcademicPeriod::query()
            ->with('status')
            ->withCount(['classGroups', 'subjectEnrollments'])
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/AcademicPeriods', [
            'periods' => $periods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $draftStatusId = AcademicPeriodStatus::where('code', 'draft')->value('id');

        if (! $draftStatusId) {
            return response()->json([
                'error' => 'The draft academic period status is not configured.',
            ], 422);
        }

        if ($validated['is_active'] ?? false) {
            AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        $period = AcademicPeriod::create([
            ...$validated,
            'academic_period_status_id' => $draftStatusId,
            'status_changed_at' => now(),
        ]);

        return response()->json([
            'success' => 'Academic period created successfully.',
            'academicPeriod' => $period->load('status'),
        ], 201);
    }

    public function update(Request $request, AcademicPeriod $academicPeriod)
    {
        if ($academicPeriod->isFinal()) {
            return response()->json([
                'error' => 'Final academic periods cannot be edited.',
            ], 422);
        }

        $validated = $this->validatedData($request, $academicPeriod);

        if ($validated['is_active'] ?? false) {
            AcademicPeriod::where('is_active', true)
                ->where('id', '!=', $academicPeriod->id)
                ->update(['is_active' => false]);
        }

        $academicPeriod->update($validated);

        return response()->json([
            'success' => 'Academic period updated successfully.',
            'academicPeriod' => $academicPeriod->fresh('status'),
        ]);
    }

    public function destroy(AcademicPeriod $academicPeriod)
    {
        if ($academicPeriod->classGroups()->exists() || $academicPeriod->subjectEnrollments()->exists()) {
            return back()->withErrors('Academic periods with groups or enrollments cannot be deleted.');
        }

        $academicPeriod->delete();

        return back()->with('success', 'Academic period deleted.');
    }

    public function activate(AcademicPeriod $academicPeriod)
    {
        if ($academicPeriod->isFinal()) {
            return back()->withErrors('Final academic periods cannot be activated.');
        }

        AcademicPeriod::where('is_active', true)
            ->where('id', '!=', $academicPeriod->id)
            ->update(['is_active' => false]);

        $academicPeriod->update(['is_active' => true]);

        return back()->with('success', 'Academic period activated.');
    }

    public function openEnrollment(AcademicPeriod $academicPeriod, AcademicPeriodService $service)
    {
        $this->authorize('openEnrollment', $academicPeriod);

        return $this->runTransition(
            fn() => $service->openEnrollment($academicPeriod),
            'Enrollment opened successfully.'
        );
    }

    public function closeEnrollment(AcademicPeriod $academicPeriod, AcademicPeriodService $service)
    {
        $this->authorize('closeEnrollment', $academicPeriod);

        return $this->runTransition(
            fn() => $service->closeEnrollment($academicPeriod),
            'Enrollment closed successfully.'
        );
    }

    public function start(AcademicPeriod $academicPeriod, AcademicPeriodService $service)
    {
        $this->authorize('startPeriod', $academicPeriod);

        return $this->runTransition(
            fn() => $service->startPeriod($academicPeriod),
            'Academic period started successfully.'
        );
    }

    public function close(AcademicPeriod $academicPeriod, AcademicPeriodService $service)
    {
        $this->authorize('closeAcademically', $academicPeriod);

        return $this->runTransition(
            fn() => $service->closeAcademicPeriod($academicPeriod),
            'Academic period closed successfully.'
        );
    }

    public function archive(AcademicPeriod $academicPeriod, AcademicPeriodService $service)
    {
        $this->authorize('archive', $academicPeriod);

        return $this->runTransition(
            fn() => $service->archive($academicPeriod),
            'Academic period archived successfully.'
        );
    }

    private function validatedData(Request $request, ?AcademicPeriod $period = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_periods', 'name')->ignore($period?->id),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'enrollment_deadline' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'unenrollment_deadline' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'is_active' => 'boolean',
        ]);

        $overlap = AcademicPeriod::where(function ($query) use ($validated) {
            $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                ->orWhere(function ($subquery) use ($validated) {
                    $subquery->where('start_date', '<=', $validated['start_date'])
                        ->where('end_date', '>=', $validated['end_date']);
                });
        })
            ->when($period, fn($query) => $query->where('id', '!=', $period->id))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'The period dates overlap with an existing period.',
            ]);
        }

        return $validated;
    }

    private function runTransition(callable $callback, string $message)
    {
        try {
            $callback();
        } catch (DomainException $exception) {
            return back()->withErrors($this->domainMessage($exception->getMessage()));
        }

        return back()->with('success', $message);
    }

    private function domainMessage(string $code): string
    {
        if (str_starts_with($code, 'INVALID_TRANSITION')) {
            return 'This status transition is not allowed from the current academic period state.';
        }

        return [
            'BLOCK_PERIOD_ALREADY_FINAL' => 'This academic period is already final.',
            'BLOCK_PERIOD_HAS_NO_STATUS' => 'This academic period has no lifecycle status assigned.',
        ][$code] ?? 'The academic period transition is not allowed.';
    }
}

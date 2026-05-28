<?php

namespace App\Services;

use App\Domain\Academic\AcademicPeriodGuard;
use App\Events\EnrollmentCreated;
use App\Events\EnrollmentWithdrawn;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    /* =========================================================
     | PUBLIC API
     |=========================================================*/

    public function enroll(Student $student, ClassGroup $group): SubjectEnrollment
    {
        return DB::transaction(function () use ($student, $group) {

            $group = $this->lockGroup($group);

            $this->validateEnrollment($student, $group);

            $statusId = $this->getDefaultStatusId();

            $enrollment = SubjectEnrollment::create([
                'student_id'         => $student->id,
                'subject_id'         => $group->subject_id,
                'class_group_id'     => $group->id,
                'academic_period_id' => $group->academic_period_id,
                'status_id'          => $statusId,
                'enrolled_at'        => now(),
            ]);

            /* event(new EnrollmentCreated($enrollment)); */

            return $enrollment->fresh([
                'status',
                'classGroup.schedules',
            ]);
        });
    }

    /**
     * =========================================================
     * CHANGE GROUP
     * =========================================================
     */
    public function changeGroup(Student $student, ClassGroup $group): SubjectEnrollment
    {
        return DB::transaction(function () use ($student, $group) {

            $group = $this->lockGroup($group);

            $existing = SubjectEnrollment::where('student_id', $student->id)
                ->where('subject_id', $group->subject_id)
                ->where('academic_period_id', $group->academic_period_id)
                ->lockForUpdate()
                ->first();

            if (! $existing) {
                throw new DomainException('BLOCK_NOT_ENROLLED');
            }

            if ($existing->class_group_id === $group->id) {
                throw new DomainException('BLOCK_ALREADY_IN_GROUP');
            }

            // 🔒 mismas reglas académicas
            $this->validateEnrollment(
                $student,
                $group,
                ignoreExisting: true,
                ignoreGroupId: $existing->class_group_id
            );

            $existing->update([
                'class_group_id' => $group->id,
            ]);

            return $existing->fresh([
                'status',
                'classGroup.schedules',
            ]);
        });
    }

    /**
     * =========================================================
     * UNENROLL
     * =========================================================
     */
    public function unenroll(SubjectEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {

            $enrollment->loadMissing([
                'academicPeriod',
                'status',
            ]);

            $period = $enrollment->academicPeriod;

            AcademicPeriodGuard::ensurePeriodNotFrozen($period);
            AcademicPeriodGuard::ensureUnenrollmentAllowed($period);

            if (! $enrollment->canUnenroll()) {
                throw new DomainException('BLOCK_UNENROLL_INVALID_STATUS');
            }

            // 🔒 opcional futuro:
            /*
            if ($enrollment->grades()->exists()) {
                throw new DomainException('BLOCK_HAS_GRADES');
            }
            */

            $enrollment->delete();

            /* event(new EnrollmentWithdrawn($enrollment)); */
        });
    }

    /**
     * =========================================================
     * FRONTEND PREVIEW
     * =========================================================
     */
    public function canEnroll(Student $student, ClassGroup $group): array
    {
        try {

            $group->loadMissing([
                'schedules',
                'subject',
                'academicPeriod',
            ]);

            $this->validateEnrollment($student, $group);

            return [
                'can_enroll' => true,
                'reason' => null,
            ];
        } catch (DomainException $e) {

            return [
                'can_enroll' => false,
                'reason' => $e->getMessage(),
            ];
        }
    }

    /* =========================================================
     | CORE PIPELINE
     |=========================================================*/

    private function validateEnrollment(
        Student $student,
        ClassGroup $group,
        bool $ignoreExisting = false,
        ?int $ignoreGroupId = null
    ): void {

        $period = $group->academicPeriod;

        AcademicPeriodGuard::ensurePeriodNotFrozen($period);
        AcademicPeriodGuard::ensureEnrollmentAllowed($period);

        $this->ensureCurriculumAllows($student, $group);

        $this->ensureStudentStatusAllows($student);

        $this->ensureGroupHasSchedules($group);

        if (! $ignoreExisting) {
            $this->ensureNotAlreadyEnrolled($student, $group);
        }

        $this->ensureCapacityAvailable($group);

        $this->ensureNoScheduleConflict(
            $student,
            $group,
            $ignoreGroupId
        );

        $this->ensureCreditLimit($student, $group);

        $this->ensureProbationCreditLimit($student, $group);
    }

    private function lockGroup(ClassGroup $group): ClassGroup
    {
        return ClassGroup::where('id', $group->id)
            ->lockForUpdate()
            ->with([
                'schedules',
                'subject',
                'academicPeriod',
            ])
            ->firstOrFail();
    }

    private function getDefaultStatusId(): int
    {
        return SubjectEnrollmentStatus::where('code', 'pre_enrolled')
            ->value('id')
            ?? throw new DomainException('BLOCK_STATUS_MISCONFIGURED');
    }

    /* =========================================================
     | VALIDATIONS
     |=========================================================*/

    private function ensureCurriculumAllows(Student $student, ClassGroup $group): void
    {
        $curriculum = $student->curriculum;

        if (! $curriculum) {
            throw new DomainException('BLOCK_NO_CURRICULUM');
        }

        $exists = $curriculum->subjects()
            ->where('subjects.id', $group->subject_id)
            ->exists();

        if (! $exists) {
            throw new DomainException('BLOCK_OUT_OF_CURRICULUM');
        }
    }

    private function ensureStudentStatusAllows(Student $student): void
    {
        match ($student->academic_status) {

            'suspended' =>
            throw new DomainException('BLOCK_STATUS_SUSPENDED'),

            'graduated' =>
            throw new DomainException('BLOCK_STATUS_GRADUATED'),

            'withdrawn' =>
            throw new DomainException('BLOCK_STATUS_WITHDRAWN'),

            'active',
            'probation' => null,

            default =>
            throw new DomainException('BLOCK_INVALID_STATUS'),
        };
    }

    private function ensureGroupHasSchedules(ClassGroup $group): void
    {
        if ($group->schedules->isEmpty()) {
            throw new DomainException('BLOCK_GROUP_WITHOUT_SCHEDULE');
        }
    }

    private function ensureNotAlreadyEnrolled(
        Student $student,
        ClassGroup $group
    ): void {

        $exists = SubjectEnrollment::where('student_id', $student->id)
            ->where('subject_id', $group->subject_id)
            ->where('academic_period_id', $group->academic_period_id)
            ->exists();

        if ($exists) {
            throw new DomainException('BLOCK_ALREADY_ENROLLED');
        }
    }

    private function ensureCapacityAvailable(ClassGroup $group): void
    {
        if ($group->capacity === null) {
            return;
        }

        $count = $group->subjectEnrollments()
            ->whereHas(
                'status',
                fn($q) =>
                $q->whereIn(
                    'code',
                    config('enrollment.active_status_codes')
                )
            )
            ->lockForUpdate()
            ->count();

        if ($count >= $group->capacity) {
            throw new DomainException('BLOCK_CAPACITY');
        }
    }

    private function ensureNoScheduleConflict(
        Student $student,
        ClassGroup $group,
        ?int $ignoreGroupId = null
    ): void {

        $enrollments = SubjectEnrollment::with(
            'classGroup.schedules'
        )
            ->where('student_id', $student->id)
            ->where('academic_period_id', $group->academic_period_id)
            ->when(
                $ignoreGroupId,
                fn($q) =>
                $q->where('class_group_id', '!=', $ignoreGroupId)
            )
            ->get();

        foreach ($enrollments as $enrollment) {

            foreach ($group->schedules as $incoming) {

                $conflict = $enrollment->classGroup->schedules
                    ->contains(
                        fn($existing) =>

                        strtolower($existing->day) === strtolower($incoming->day)
                            &&
                            $this->schedulesOverlap($existing, $incoming)
                    );

                if ($conflict) {
                    throw new DomainException('BLOCK_SCHEDULE_CONFLICT');
                }
            }
        }
    }

    /**
     * 🔒 límite global normal
     */
    private function ensureCreditLimit(
        Student $student,
        ClassGroup $group
    ): void {

        $maxCredits = config('enrollment.max_credits', 21);

        $currentCredits = SubjectEnrollment::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_period_id', $group->academic_period_id)
            ->get()
            ->sum(fn($e) => $e->subject->credits ?? 0);

        $newCredits = $group->subject->credits ?? 0;

        if (($currentCredits + $newCredits) > $maxCredits) {
            throw new DomainException('BLOCK_MAX_CREDITS');
        }
    }

    /**
     * 🔒 probation override
     */
    private function ensureProbationCreditLimit(
        Student $student,
        ClassGroup $group
    ): void {

        if ($student->academic_status !== 'probation') {
            return;
        }

        $maxCredits = config('enrollment.probation_max_credits');

        $currentCredits = SubjectEnrollment::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_period_id', $group->academic_period_id)
            ->get()
            ->sum(fn($e) => $e->subject->credits ?? 0);

        $newCredits = $group->subject->credits ?? 0;

        if (($currentCredits + $newCredits) > $maxCredits) {
            throw new DomainException('BLOCK_PROBATION_CREDITS');
        }
    }

    private function schedulesOverlap($a, $b): bool
    {
        return $a->start_time < $b->end_time
            && $a->end_time > $b->start_time;
    }
}

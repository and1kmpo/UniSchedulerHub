<?php

namespace App\Services;

use App\Models\SubjectEnrollment;
use App\Models\SubjectEnrollmentStatus;
use App\Models\SubjectEnrollmentStatusTransition;
use DomainException;

class EnrollmentStatusService
{
    /**
     * Change enrollment status by code
     */
    public function transition(
        SubjectEnrollment $enrollment,
        string $toStatusCode
    ): void {
        $enrollment->loadMissing('status');

        $fromStatus = $enrollment->status;

        if (! $fromStatus) {
            throw new DomainException('BLOCK_ENROLLMENT_NO_STATUS');
        }

        $toStatus = SubjectEnrollmentStatus::where('code', $toStatusCode)->first();

        if (! $toStatus) {
            throw new DomainException('BLOCK_INVALID_TARGET_STATUS');
        }

        if ($this->isFinalStatus($fromStatus->code)) {
            throw new DomainException('BLOCK_FINAL_STATUS');
        }

        if (! $this->transitionAllowed($fromStatus->id, $toStatus->id)) {
            throw new DomainException('BLOCK_INVALID_STATUS_TRANSITION');
        }


        $enrollment->update([
            'status_id' => $toStatus->id,
        ]);
    }

    /**
     * Validate allowed transition
     */
    private function transitionAllowed(int $fromId, int $toId): bool
    {
        return SubjectEnrollmentStatusTransition::where(
            'from_status_id',
            $fromId
        )
            ->where('to_status_id', $toId)
            ->exists();
    }

    /**
     * Final states cannot change
     */
    private function isFinalStatus(string $code): bool
    {
        return in_array($code, [
            'approved',
            'failed',
            'revalidation',
        ]);
    }
}

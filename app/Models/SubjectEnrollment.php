<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DomainException;
use App\Models\SubjectEnrollmentStatus;
use App\Models\SubjectEnrollmentStatusTransition;

class SubjectEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'class_group_id',
        'status_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function status()
    {
        return $this->belongsTo(SubjectEnrollmentStatus::class, 'status_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function transitionTo(string $toCode): void
    {
        $fromStatus = $this->status;

        $toStatus = SubjectEnrollmentStatus::where('code', $toCode)->first();

        if (!$toStatus) {
            throw new DomainException("Target status '{$toCode}' does not exist.");
        }

        $allowed = SubjectEnrollmentStatusTransition::where(
            'from_status_id',
            $fromStatus->id
        )
            ->where(
                'to_status_id',
                $toStatus->id
            )
            ->exists();

        if (!$allowed) {
            throw new DomainException(
                "Transition '{$fromStatus->code}' → '{$toCode}' is not allowed."
            );
        }

        $this->update([
            'status_id' => $toStatus->id
        ]);
    }

    public function grade()
    {
        return $this->hasOne(Grade::class);
    }

    public function isActive(): bool
    {
        return in_array(
            $this->status?->code,
            config('enrollment.active_status_codes')
        );
    }

    public function canEditGrades(): bool
    {
        return in_array($this->status?->code, ['enrolled']);
    }

    public function canUnenroll(): bool
    {
        return in_array($this->status?->code, ['pre_enrolled', 'enrolled']);
    }

    public function isFinal(): bool
    {
        return in_array($this->status?->code, [
            'approved',
            'failed',
            'withdrawn',
            'cancelled',
            'revalidation',
        ]);
    }
}

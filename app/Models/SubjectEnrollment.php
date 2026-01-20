<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DomainException;

class SubjectEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'class_group_id',
        'status_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'enrollment_deadline' => 'datetime',
        'unenrollment_deadline' => 'datetime',
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

    /**
     * Cambia el estado respetando la tabla de transiciones
     */
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
}

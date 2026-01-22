<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'enrollment_deadline',
        'unenrollment_deadline',
        'is_active',
        'academic_period_status_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'enrollment_deadline' => 'datetime',
        'unenrollment_deadline' => 'datetime',
    ];

    public function enrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function status()
    {
        return $this->belongsTo(AcademicPeriodStatus::class, 'academic_period_status_id');
    }

    public function isDraft(): bool
    {
        return $this->status?->code === 'draft';
    }

    public function isEnrollmentOpen(): bool
    {
        return $this->status?->code === 'enrollment_open';
    }

    public function isEnrollmentClosed(): bool
    {
        return in_array($this->status?->code, [
            'enrollment_closed',
            'in_progress',
            'academically_closed',
            'archived',
        ]);
    }

    public function isInProgress(): bool
    {
        return $this->status?->code === 'in_progress';
    }

    public function isAcademicallyClosed(): bool
    {
        return $this->status?->code === 'academically_closed';
    }

    // =========================
    // Domain rules
    // =========================

    public function canEnroll(): bool
    {
        return $this->isEnrollmentOpen();
    }

    // =========================
    // Backward compatibility
    // =========================

    public function isActive(): bool
    {
        return $this->is_active || $this->isEnrollmentOpen();
    }

    // AcademicPeriod.php

    public function allowsGradeEdition(): bool
    {
        return $this->isInProgress();
    }
}

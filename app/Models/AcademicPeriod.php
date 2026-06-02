<?php

namespace App\Models;

use App\Enums\AcademicPeriodState;
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
        'academic_period_status_id',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_deadline' => 'date',
        'unenrollment_deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public const STATUS_DRAFT = 1;
    public const STATUS_ENROLLMENT_OPEN = 2;
    public const STATUS_ENROLLMENT_CLOSED = 3;
    public const STATUS_IN_PROGRESS = 4;
    public const STATUS_ACADEMICALLY_CLOSED = 5;
    public const STATUS_ARCHIVED = 6;


    /* =========================
     | Relationships
     |=========================*/
    public function status()
    {
        return $this->belongsTo(
            AcademicPeriodStatus::class,
            'academic_period_status_id'
        );
    }

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class);
    }

    public function subjectEnrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    /* =========================
     | State
     |=========================*/
    public function state(): ?AcademicPeriodState
    {
        return $this->status
            ? AcademicPeriodState::from($this->status->code)
            : null;
    }

    public function isFinal(): bool
    {
        return $this->state()?->isFinal() ?? false;
    }

    public function isAcademicallyClosed(): bool
    {
        return $this->state()?->isFinal() ?? false;
    }

    public function isInProgress(): bool
    {
        return $this->state()?->code === 'in_progress';
    }

    /* =========================
     | Enrollment rules
     |=========================*/
    public function canEnroll(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->state()?->allowsEnrollment()) {
            return false;
        }

        return ! $this->enrollment_deadline
            || now()->lte($this->enrollment_deadline->endOfDay());
    }

    public function canUnenroll(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isFinal()) {
            return false;
        }

        return ! $this->unenrollment_deadline
            || now()->lte($this->unenrollment_deadline->endOfDay());
    }

    public function canEditGrades(): bool
    {
        return $this->state()?->allowsGradeEdition() ?? false;
    }

    /* =========================
     | Backward compatibility
     |=========================*/
    public function allowsEnrollment(): bool
    {
        return $this->canEnroll();
    }

    public function allowsUnenrollment(): bool
    {
        return $this->canUnenroll();
    }

    /* =========================
     | Scopes & helpers
     |=========================*/
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function currentOrFail(): self
    {
        return self::active()->firstOrFail();
    }
}

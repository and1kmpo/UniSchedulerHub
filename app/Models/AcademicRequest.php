<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicRequest extends Model
{
    use HasFactory;

    public const TYPE_CHANGE_GROUP = 'change_group';
    public const TYPE_LATE_WITHDRAWAL = 'late_withdrawal';
    public const TYPE_GRADE_REVIEW = 'grade_review';
    public const TYPE_ENROLLMENT_EXCEPTION = 'enrollment_exception';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPES = [
        self::TYPE_CHANGE_GROUP => 'Change group',
        self::TYPE_LATE_WITHDRAWAL => 'Late withdrawal',
        self::TYPE_GRADE_REVIEW => 'Grade review',
        self::TYPE_ENROLLMENT_EXCEPTION => 'Enrollment exception',
    ];

    public const STATUSES = [
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_UNDER_REVIEW => 'Under review',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'student_id',
        'created_by',
        'reviewed_by',
        'subject_enrollment_id',
        'class_group_id',
        'type',
        'status',
        'title',
        'description',
        'decision_reason',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function enrollment()
    {
        return $this->belongsTo(SubjectEnrollment::class, 'subject_enrollment_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }
}

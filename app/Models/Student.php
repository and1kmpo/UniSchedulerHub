<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'semester',
        'program_id',
        'curriculum_id',
        'academic_status'
    ];

    // Academic statuses
    public const STATUS_ACTIVE     = 'active';
    public const STATUS_PROBATION  = 'probation';
    public const STATUS_SUSPENDED  = 'suspended';
    public const STATUS_GRADUATED  = 'graduated';
    public const STATUS_WITHDRAWN  = 'withdrawn';

    /**
     * Statuses that allow enrollment
     */
    public const ENROLLABLE_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PROBATION,
    ];

    /**
     * Statuses that block enrollment
     */
    public const BLOCKED_STATUSES = [
        self::STATUS_SUSPENDED,
        self::STATUS_GRADUATED,
        self::STATUS_WITHDRAWN,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class)->withTrashed();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function enrollmentGrades()
    {
        return $this->hasManyThrough(
            Grade::class,
            SubjectEnrollment::class,
            'student_id',
            'subject_enrollment_id',
            'id',
            'id'
        );
    }

    public function enrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function subjectEnrollments()
    {
        return $this->enrollments();
    }

    public function academicRequests()
    {
        return $this->hasMany(AcademicRequest::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class)->withTrashed();
    }
}

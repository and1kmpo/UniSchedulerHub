<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
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
}

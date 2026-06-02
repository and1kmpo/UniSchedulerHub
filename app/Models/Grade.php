<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_enrollment_id',
        'professor_id',
        'partial_1',
        'partial_2',
        'partial_3',
        'activities',
        'attendance',
        'final_grade',
        'grade_status_id',
        'created_by',
        'updated_by',
    ];

    // Relaciones
    public function enrollment()
    {
        return $this->belongsTo(SubjectEnrollment::class, 'subject_enrollment_id');
    }

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
        return $this->enrollment->academicPeriod();
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function state()
    {
        return $this->belongsTo(GradeStatus::class, 'grade_status_id')->select(['id', 'code', 'label']);
    }
}

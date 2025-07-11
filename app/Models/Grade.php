<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'professor_id',
        'partial_1',
        'partial_2',
        'partial_3',
        'activities',
        'attendance',
        'final_grade',
        'grade_state_id',
    ];

    // Relaciones
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    public function state()
    {
        return $this->belongsTo(GradeState::class, 'grade_state_id')->select(['id', 'code', 'label']);
    }
}

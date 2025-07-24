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
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'enrollment_deadline',
        'unenrollment_deadline',
    ];

    public function enrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

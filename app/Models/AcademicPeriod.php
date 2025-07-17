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
        'is_active',
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

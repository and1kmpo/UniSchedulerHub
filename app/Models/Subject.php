<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'credits',
        'knowledge_area',
        'elective',
    ];

    protected $casts = [
        'credits' => 'integer',
        'elective' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($subject) {
            if (empty($subject->code)) {
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subject->name), 0, 3));
                $nextNumber = self::count() + 1;
                $subject->code = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            }
        });
    }


    public function professors()
    {
        return $this->belongsToMany(Professor::class, 'professor_subject');
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class)->withTrashed()->withPivot('semester')->withTimestamps();
    }

    public function grades()
    {
        return $this->hasManyThrough(
            Grade::class,
            SubjectEnrollment::class,
            'subject_id',
            'subject_enrollment_id',
            'id',
            'id'
        );
    }

    public function enrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    // Materias que son prerrequisitos de esta materia
    public function prerequisites()
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_prerequisites',
            'subject_id',
            'prerequisite_subject_id'
        )
            ->withPivot(['logic', 'min_grade'])
            ->withTimestamps();
    }


    // Materias que dependen de esta como prerrequisito
    public function isPrerequisiteFor()
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_prerequisites',
            'prerequisite_subject_id',
            'subject_id'
        )
            ->withPivot(['logic', 'min_grade'])
            ->withTimestamps();
    }

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class);
    }

    public function curricula()
    {
        return $this->belongsToMany(
            Curriculum::class,
            'curriculum_subjects'
        )
            ->withTrashed()
            ->withPivot([
                'semester_recommended',
                'credits',
                'type',
                'area_id',
            ])
            ->withTimestamps();
    }
}

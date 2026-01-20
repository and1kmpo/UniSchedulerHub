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

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject_professor')
            ->withPivot('professor_id') // Incluimos el ID del profesor desde la pivot
            ->withTimestamps();
    }

    // Relación con el profesor desde la tabla pivot
    public function pivotProfessor()
    {
        return $this->hasOneThrough(
            Professor::class,
            Subject::class,
            'id', // Foreign key en `subjects`
            'id', // Foreign key en `professors`
            'subject_id', // Foreign key en `student_subject_professor`
            'professor_id' // Foreign key en `student_subject_professor`
        )->with('user'); // Incluimos el usuario relacionado
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function enrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }
}

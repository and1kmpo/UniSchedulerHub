<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'credits',
        'knowledge_area',
        'elective',
    ];

    protected static function booted()
    {
        static::creating(function ($subject) {
            if (empty($subject->code)) {
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subject->name), 0, 3));
                $nextNumber = self::count() + 1;
                $subject->code = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT); // Ej: MAT01
            }
        });
    }


    public function professors()
    {
        return $this->belongsToMany(Professor::class, 'professor_subject');
    }

    public function pivotProfessor()
    {
        return $this->belongsTo(Professor::class, 'professor_id', 'id')->with('user');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject_professor')
            ->withPivot('professor_id')
            ->withTimestamps();
    }
    public function programs()
    {
        return $this->belongsToMany(Program::class)->withPivot('semester')->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}

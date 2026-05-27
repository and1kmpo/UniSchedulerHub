<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject');
    }

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class, 'professor_id', 'user_id');
    }

    // Relación inversa con la tabla pivot `student_subject_professor`
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject_professor')
            ->withPivot('subject_id')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}

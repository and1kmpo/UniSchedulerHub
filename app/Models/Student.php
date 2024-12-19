<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'document',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'semester',
        'program_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject_professor')->withTimestamps();
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

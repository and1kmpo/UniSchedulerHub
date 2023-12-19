<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'document',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'picture',
        'semester',
        'program_id',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject_professor')->withTimestamps();
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

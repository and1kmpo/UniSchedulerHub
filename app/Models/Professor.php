<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory;

    protected $fillable = ['document', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'];



    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject');
    }
}

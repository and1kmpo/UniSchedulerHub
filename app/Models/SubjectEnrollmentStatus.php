<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectEnrollmentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'label', 'color'];
}

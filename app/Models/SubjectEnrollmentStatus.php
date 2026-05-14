<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectEnrollmentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'color'];

    public function outgoingTransitions()
    {
        return $this->hasMany(
            SubjectEnrollmentStatusTransition::class,
            'from_status_id'
        );
    }
}

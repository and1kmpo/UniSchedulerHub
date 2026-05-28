<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectEnrollmentStatusTransition extends Model
{
    protected $fillable = [
        'from_status_id',
        'to_status_id',
    ];

    public function fromStatus()
    {
        return $this->belongsTo(SubjectEnrollmentStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(SubjectEnrollmentStatus::class, 'to_status_id');
    }
}
